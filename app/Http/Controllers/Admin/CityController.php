<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\City;
use App\Models\Media;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class CityController extends Controller
{

    /**
     * Check if current admin is super admin and abort if not
     */
    private function ensureSuperAdmin()
    {
        $admin = Auth::guard('admin')->user();
        
        // Check if admin has any city assignments
        if ($admin && $admin->cities()->count() > 0) {
            // Admin has city assignments, so they are not a super admin
            abort(403, 'Access denied. This section is only available to super administrators.');
        }
    }

    /**
     * Check if current admin is super admin
     */
    private function isSuperAdmin()
    {
        $admin = Auth::guard('admin')->user();
        return $admin && $admin->cities()->count() === 0;
    }

    // Show all cities
    public function index(Request $request)
    {
        // Ensure only super admins can access
        $this->ensureSuperAdmin();

        $query = City::with(['image', 'admins', 'services', 'categories'])
                    ->withCount(['services', 'categories', 'admins', 'users', 'news', 'ads', 'contactMessages']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where('name', 'LIKE', "%{$search}%");
        }

        // Filter by admin assignment status
        if ($request->filled('admin_status')) {
            switch ($request->get('admin_status')) {
                case 'assigned':
                    $query->has('admins');
                    break;
                case 'unassigned':
                    $query->doesntHave('admins');
                    break;
                // 'all' or default - no additional filter
            }
        }

        // Filter by activity (has services/categories)
        if ($request->filled('activity_status')) {
            switch ($request->get('activity_status')) {
                case 'active':
                    $query->where(function($q) {
                        $q->has('services')->orHas('categories');
                    });
                    break;
                case 'inactive':
                    $query->doesntHave('services')->doesntHave('categories');
                    break;
                // 'all' or default - no additional filter
            }
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        $allowedSorts = ['name', 'created_at', 'services_count', 'categories_count', 'admins_count', 'users_count'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->latest();
        }

        $cities = $query->paginate(25)->withQueryString();

        // Get statistics for dashboard
        $stats = [
            'total_cities' => City::count(),
            'cities_with_admins' => City::has('admins')->count(),
            'cities_without_admins' => City::doesntHave('admins')->count(),
            'active_cities' => City::where(function($q) {
                $q->has('services')->orHas('categories');
            })->count(),
        ];

        return view('city.index', compact('cities', 'stats'));
    }

    // Show the form to create new city
    public function create()
    {
        $this->ensureSuperAdmin();

        $city = new City();
        return view('city.edit', compact('city'));
    }

    // Show the form for editing the specified city
    public function edit(City $city)
    {
        $this->ensureSuperAdmin();

        return view('city.edit', compact('city'));
    }

    // Save a newly created city
    public function store(Request $request)
    {
        $this->ensureSuperAdmin();

        $city = new City();
        return $this->update($request, $city);
    }

    // Update the specified city
    public function update(Request $request, City $city)
    {
        $this->ensureSuperAdmin();

        // Validation rules
        $rules = [
            'name' => 'required|string|max:255|unique:cities,name,' . ($city->id ?? 'NULL'),
            'description' => 'nullable|string|max:1000',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'nullable|boolean',
        ];

        $request->validate($rules);

        // Handle image upload
        $imageId = $city->image_id;
        if($request->hasFile('image')){
            $image = $request->file('image');

            $media = resizeImage($image, $this->storagePath);
            
            $imageId = $media->id ?? null;
            
            // Delete old image if exists
            if($imageId && $city->image_id && $city->image){
                Storage::disk('public')->delete($city->image->path);
                $city->image->delete();
            }
        }

        // Update city fields
        $city->name = $request->input('name');
        $city->description = $request->input('description');
        $city->image_id = $imageId;
        $city->is_active = $request->input('is_active', false);

        $city->save();

        $message = $city->wasRecentlyCreated ? 'City has been created successfully' : 'City has been updated successfully';

        return redirect()
            ->route('city.index')
            ->with('status', $message);
    }

    // Show the specified city details
    public function show(City $city)
    {
        $this->ensureSuperAdmin();

        $city->load(['image', 'admins', 'services', 'categories', 'config', 'news', 'ads', 'contactMessages', 'users']);
        
        // Get detailed statistics for this city
        $cityStats = [
            'services_count' => $city->services()->count(),
            'active_services_count' => $city->services()->where('valid', true)->count(),
            'categories_count' => $city->categories()->count(),
            'main_categories_count' => $city->categories()->whereNull('parent_id')->count(),
            'sub_categories_count' => $city->categories()->whereNotNull('parent_id')->count(),
            'admins_count' => $city->admins()->count(),
            'users_count' => $city->users()->count(),
            'news_count' => $city->news()->count(),
            'ads_count' => $city->ads()->count(),
            'contact_messages_count' => $city->contactMessages()->count(),
            'unread_messages_count' => $city->contactMessages()->where('is_read', false)->count(),
        ];

        return view('city.show', compact('city', 'cityStats'));
    }

    // Delete the specified city
    public function destroy(City $city)
    {
        $this->ensureSuperAdmin();

        // Check if city has related data
        $hasServices = $city->services()->count() > 0;
        $hasCategories = $city->categories()->count() > 0;
        $hasAdmins = $city->admins()->count() > 0;
        $hasUsers = $city->users()->count() > 0;
        $hasNews = $city->news()->count() > 0;
        $hasAds = $city->ads()->count() > 0;
        $hasContactMessages = $city->contactMessages()->count() > 0;
        $hasSettings = $city->settings()->count() > 0;

        if($hasServices || $hasCategories || $hasAdmins || $hasUsers || $hasNews || $hasAds || $hasContactMessages || $hasSettings){
            $relatedItems = [];
            if ($hasServices) $relatedItems[] = 'services';
            if ($hasCategories) $relatedItems[] = 'categories';
            if ($hasAdmins) $relatedItems[] = 'admins';
            if ($hasUsers) $relatedItems[] = 'users';
            if ($hasNews) $relatedItems[] = 'news';
            if ($hasAds) $relatedItems[] = 'ads';
            if ($hasContactMessages) $relatedItems[] = 'contact messages';
            if ($hasSettings) $relatedItems[] = 'settings';

            return redirect()
                ->route('city.index')
                ->with('error', 'Cannot delete city. It has related ' . implode(', ', $relatedItems) . '.');
        }

        // Delete image if exists
        if($city->image){
            Storage::disk('public')->delete($city->image->path);
            $city->image->delete();
        }

        // Delete city config if exists
        if($city->config){
            $city->config->delete();
        }

        $city->delete();
        
        return redirect()
            ->route('city.index')
            ->with('status', 'City has been deleted successfully');
    }

    // Toggle city active status
    public function toggleStatus(City $city)
    {
        if (!$this->isSuperAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. This action is only available to super administrators.'
            ], 403);
        }

        $city->is_active = !$city->is_active;
        $city->save();

        $status = $city->is_active ? 'activated' : 'deactivated';
        
        return redirect()
            ->back()
            ->with('status', "City has been {$status} successfully");
    }

    // Bulk actions
    public function bulkAction(Request $request)
    {
        $this->ensureSuperAdmin();

        $request->validate([
            'action' => 'required|in:activate,deactivate,delete',
            'city_ids' => 'required|array',
            'city_ids.*' => 'exists:cities,id'
        ]);

        $cities = City::whereIn('id', $request->city_ids);
        $processedItems = [];
        $errors = [];

        switch ($request->action) {
            case 'activate':
                $updated = $cities->update(['is_active' => true]);
                $message = "Activated {$updated} cities successfully";
                break;
                
            case 'deactivate':
                $updated = $cities->update(['is_active' => false]);
                $message = "Deactivated {$updated} cities successfully";
                break;
                
            case 'delete':
                foreach ($cities->get() as $city) {
                    try {
                        // Check if city has related data
                        $hasRelated = $city->services()->count() > 0 ||
                                    $city->categories()->count() > 0 ||
                                    $city->admins()->count() > 0 ||
                                    $city->users()->count() > 0 ||
                                    $city->news()->count() > 0 ||
                                    $city->ads()->count() > 0 ||
                                    $city->contactMessages()->count() > 0 ||
                                    $city->settings()->count() > 0;

                        if ($hasRelated) {
                            $errors[] = "Cannot delete '{$city->name}': has related data";
                            continue;
                        }

                        // Delete image if exists
                        if($city->image){
                            Storage::disk('public')->delete($city->image->path);
                            $city->image->delete();
                        }

                        // Delete city config if exists
                        if($city->config){
                            $city->config->delete();
                        }

                        $cityName = $city->name;
                        $city->delete();
                        $processedItems[] = "city '{$cityName}'";

                    } catch (\Exception $e) {
                        $errors[] = "Error deleting '{$city->name}': " . $e->getMessage();
                    }
                }
                
                $message = !empty($processedItems) ? "Successfully deleted: " . implode(', ', $processedItems) : '';
                break;
        }

        if (!empty($errors)) {
            $message .= (!empty($message) ? " | " : "") . "Errors: " . implode(", ", $errors);
        }

        return redirect()
            ->route('city.index')
            ->with(!empty($processedItems) || $request->action !== 'delete' ? 'status' : 'error', 
                   $message ?: 'No items were processed.');
    }

    // Get city statistics (API endpoint)
    public function getStats()
    {
        if (!$this->isSuperAdmin()) {
            return response()->json([
                'error' => 'Access denied. This action is only available to super administrators.'
            ], 403);
        }

        $stats = [
            'total_cities' => City::count(),
            'active_cities' => City::where('is_active', true)->count(),
            'inactive_cities' => City::where('is_active', false)->count(),
            'cities_with_admins' => City::has('admins')->count(),
            'cities_without_admins' => City::doesntHave('admins')->count(),
            'cities_with_services' => City::has('services')->count(),
            'cities_with_categories' => City::has('categories')->count(),
            'cities_with_users' => City::has('users')->count(),
        ];

        return response()->json($stats);
    }

    // Export cities data
    public function exportCities(Request $request)
    {
        $this->ensureSuperAdmin();

        $query = City::with(['admins'])
                    ->withCount(['services', 'categories', 'admins', 'users', 'news', 'ads', 'contactMessages']);

        // Apply filters if provided
        if ($request->filled('admin_status')) {
            if ($request->admin_status === 'assigned') {
                $query->has('admins');
            } elseif ($request->admin_status === 'unassigned') {
                $query->doesntHave('admins');
            }
        }

        $cities = $query->orderBy('name')->get();
        
        $filename = 'cities_export_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];
        
        $callback = function() use ($cities) {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'Name', 'Status', 'Admins Count', 'Services Count', 
                'Categories Count', 'Users Count', 'News Count', 
                'Ads Count', 'Contact Messages Count', 'Created At'
            ]);
            
            foreach ($cities as $city) {
                fputcsv($file, [
                    $city->name,
                    $city->is_active ? 'Active' : 'Inactive',
                    $city->admins_count,
                    $city->services_count,
                    $city->categories_count,
                    $city->users_count,
                    $city->news_count,
                    $city->ads_count,
                    $city->contact_messages_count,
                    $city->created_at
                ]);
            }
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
}