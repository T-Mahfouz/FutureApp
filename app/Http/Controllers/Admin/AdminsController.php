<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin;
use App\Models\City;
use App\Models\Media;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class AdminsController extends Controller
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
            abort(403, 'Access denied. Admin management is only available to super administrators.');
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

    // Show all admins
    public function index(Request $request)
    {
        $this->ensureSuperAdmin();

        $query = Admin::with(['cities', 'image'])
                     ->where('email', '!=', 'dev@dev.com');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhereHas('cities', function($cityQuery) use ($search) {
                      $cityQuery->where('name', 'LIKE', "%{$search}%");
                  });
            });
        }

        // Filter by admin type
        if ($request->filled('admin_type')) {
            switch ($request->get('admin_type')) {
                case 'super':
                    $query->doesntHave('cities');
                    break;
                case 'city':
                    $query->has('cities');
                    break;
                // 'all' or default - no additional filter
            }
        }

        // Filter by city assignment
        if ($request->filled('city_id')) {
            $query->whereHas('cities', function($cityQuery) use ($request) {
                $cityQuery->where('cities.id', $request->get('city_id'));
            });
        }

        // Filter by status (has image or not)
        if ($request->filled('has_image')) {
            if ($request->get('has_image') == '1') {
                $query->whereNotNull('image_id');
            } else {
                $query->whereNull('image_id');
            }
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        $allowedSorts = ['name', 'email', 'created_at', 'updated_at'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->latest();
        }

        $admins = $query->paginate(25)->withQueryString();

        // Get filter options
        $cities = City::orderBy('name')->get();

        // Get statistics
        $stats = [
            'total_admins' => Admin::where('email', '!=', 'dev@dev.com')->count(),
            'super_admins' => Admin::where('email', '!=', 'dev@dev.com')->doesntHave('cities')->count(),
            'city_admins' => Admin::where('email', '!=', 'dev@dev.com')->has('cities')->count(),
            'admins_with_images' => Admin::where('email', '!=', 'dev@dev.com')->whereNotNull('image_id')->count(),
        ];

        return view('admin.index', compact('admins', 'cities', 'stats'));
    }

    // Show the form to create new admin
    public function create()
    {
        $this->ensureSuperAdmin();

        $admin = new Admin();
        $cities = City::orderBy('name')->get();
        return view('admin.edit', compact('admin', 'cities'));
    }

    // Show the form for editing the specified admin
    public function edit(Admin $admin)
    {
        $this->ensureSuperAdmin();

        // Prevent editing the dev account
        if ($admin->email === 'dev@dev.com') {
            abort(403, 'The developer account cannot be edited.');
        }

        $cities = City::orderBy('name')->get();
        return view('admin.edit', compact('admin', 'cities'));
    }

    // Save a newly created admin
    public function store(Request $request)
    {
        $this->ensureSuperAdmin();

        $admin = new Admin();
        return $this->update($request, $admin);
    }

    // Update the specified admin
    public function update(Request $request, Admin $admin)
    {
        $this->ensureSuperAdmin();

        // Prevent editing the dev account
        if ($admin->exists && $admin->email === 'dev@dev.com') {
            abort(403, 'The developer account cannot be edited.');
        }

        // Base validation rules
        $rules = [
            'name' => 'required|string|max:255',
            'cities' => 'nullable|array',
            'cities.*' => 'exists:cities,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'admin_type' => 'nullable|in:super,city',
        ];

        // Email validation - unique except current admin
        if($admin->id){
            $rules['email'] = 'required|email|unique:admins,email,' . $admin->id;
        } else {
            $rules['email'] = 'required|email|unique:admins,email';
        }

        // Password validation
        if(!$admin->id){
            // Required for new admins
            $rules['password'] = 'required|string|min:6|confirmed';
        } else {
            // Optional for existing admins, but must be confirmed if provided
            $rules['password'] = 'nullable|string|min:6|confirmed';
        }

        // Prevent dev@dev.com email
        $rules['email'] .= '|not_in:dev@dev.com';

        $request->validate($rules);

        // Handle image upload
        $imageId = $admin->image_id;
        if($request->hasFile('image')){
            $image = $request->file('image');
            
            $media = resizeImage($image, $this->storagePath);
            
            $imageId = $media->id ?? null;
            
            // Delete old image if exists
            if($imageId && $admin->image_id && $admin->image){
                Storage::disk('public')->delete($admin->image->path);
                $admin->image->delete();
            }
        }

        // Update admin fields
        $admin->name = $request->input('name');
        $admin->email = $request->input('email');
        $admin->image_id = $imageId;

        // Hash password if provided
        if($request->input('password')){
            $admin->password = bcrypt($request->input('password'));
        }

        $admin->save();

        // Handle city assignments based on admin type
        $adminType = $request->input('admin_type');
        if ($adminType === 'super' || (!$adminType && !$request->has('cities'))) {
            // Super admin or no cities selected - remove all city assignments
            $admin->cities()->sync([]);
        } else {
            // City admin - sync selected cities
            $cities = $request->input('cities', []);
            $admin->cities()->sync($cities);
        }

        $message = $admin->wasRecentlyCreated ? 'Admin has been created successfully' : 'Admin has been updated successfully';

        return redirect()
            ->route('admin.index')
            ->with('status', $message);
    }

    // Show the specified admin details
    public function show(Admin $admin)
    {
        $this->ensureSuperAdmin();

        $admin->load(['cities', 'image']);
        
        // Get admin statistics
        $adminStats = [
            'cities_count' => $admin->cities->count(),
            'admin_type' => $admin->cities->count() > 0 ? 'City Admin' : 'Super Admin',
            'services_managed' => $admin->cities->count() > 0 ? 
                \App\Models\Service::whereIn('city_id', $admin->cities->pluck('id'))->count() : 
                \App\Models\Service::count(),
            'categories_managed' => $admin->cities->count() > 0 ? 
                \App\Models\Category::whereIn('city_id', $admin->cities->pluck('id'))->count() : 
                \App\Models\Category::count(),
            'last_login' => null, // You can implement this if you track login times
        ];

        return view('admin.show', compact('admin', 'adminStats'));
    }

    // Delete the specified admin
    public function destroy(Admin $admin)
    {
        $this->ensureSuperAdmin();

        // Prevent deleting the dev account
        if ($admin->email === 'dev@dev.com') {
            abort(403, 'The developer account cannot be deleted.');
        }

        // Prevent self-deletion
        if($admin->id == auth()->guard('admin')->id()){
            return redirect()
                ->route('admin.index')
                ->with('error', 'You cannot delete yourself.');
        }

        // Delete image if exists
        if($admin->image){
            Storage::disk('public')->delete($admin->image->path);
            $admin->image->delete();
        }

        // Detach cities before deletion
        $admin->cities()->detach();

        $admin->delete();
        
        return redirect()
            ->route('admin.index')
            ->with('status', 'Admin has been deleted successfully');
    }

    // Bulk actions
    public function bulkAction(Request $request)
    {
        $this->ensureSuperAdmin();

        $request->validate([
            'action' => 'required|in:delete,assign_cities,remove_cities',
            'admin_ids' => 'required|array',
            'admin_ids.*' => 'exists:admins,id',
            'cities' => 'nullable|array',
            'cities.*' => 'exists:cities,id'
        ]);

        $currentAdminId = auth()->guard('admin')->id();
        $adminIds = array_diff($request->admin_ids, [$currentAdminId]); // Remove current admin from bulk actions
        
        $admins = Admin::whereIn('id', $adminIds)
                      ->where('email', '!=', 'dev@dev.com'); // Exclude dev account

        $processedItems = [];
        $errors = [];

        switch ($request->action) {
            case 'assign_cities':
                if (empty($request->cities)) {
                    return redirect()->route('admin.index')
                                   ->with('error', 'Please select cities to assign.');
                }
                
                foreach ($admins->get() as $admin) {
                    try {
                        $admin->cities()->syncWithoutDetaching($request->cities);
                        $processedItems[] = "admin '{$admin->name}'";
                    } catch (\Exception $e) {
                        $errors[] = "Error updating '{$admin->name}': " . $e->getMessage();
                    }
                }
                $message = !empty($processedItems) ? "Assigned cities to: " . implode(', ', $processedItems) : '';
                break;

            case 'remove_cities':
                foreach ($admins->get() as $admin) {
                    try {
                        if (!empty($request->cities)) {
                            $admin->cities()->detach($request->cities);
                        } else {
                            $admin->cities()->detach(); // Remove all cities
                        }
                        $processedItems[] = "admin '{$admin->name}'";
                    } catch (\Exception $e) {
                        $errors[] = "Error updating '{$admin->name}': " . $e->getMessage();
                    }
                }
                $message = !empty($processedItems) ? "Removed cities from: " . implode(', ', $processedItems) : '';
                break;

            case 'delete':
                foreach ($admins->get() as $admin) {
                    try {
                        $adminName = $admin->name;
                        
                        // Delete image if exists
                        if($admin->image){
                            Storage::disk('public')->delete($admin->image->path);
                            $admin->image->delete();
                        }

                        // Detach cities before deletion
                        $admin->cities()->detach();
                        $admin->delete();
                        
                        $processedItems[] = "admin '{$adminName}'";
                    } catch (\Exception $e) {
                        $errors[] = "Error deleting '{$adminName}': " . $e->getMessage();
                    }
                }
                $message = !empty($processedItems) ? "Successfully deleted: " . implode(', ', $processedItems) : '';
                break;
        }

        // Handle exclusions
        if (in_array($currentAdminId, $request->admin_ids)) {
            $errors[] = "Cannot perform actions on yourself";
        }

        $devAdmin = Admin::where('email', 'dev@dev.com')->first();
        if ($devAdmin && in_array($devAdmin->id, $request->admin_ids)) {
            $errors[] = "Cannot perform actions on developer account";
        }

        if (!empty($errors)) {
            $message .= (!empty($message) ? " | " : "") . "Errors: " . implode(", ", $errors);
        }

        return redirect()
            ->route('admin.index')
            ->with(!empty($processedItems) ? 'status' : 'error', $message ?: 'No items were processed.');
    }

    // Toggle admin type (super/city)
    public function toggleType(Admin $admin)
    {
        $this->ensureSuperAdmin();

        if ($admin->email === 'dev@dev.com') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot modify developer account type.'
            ], 403);
        }

        if ($admin->id == auth()->guard('admin')->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot modify your own admin type.'
            ], 403);
        }

        if ($admin->cities()->count() > 0) {
            // Currently city admin, make super admin
            $admin->cities()->detach();
            $newType = 'Super Admin';
        } else {
            // Currently super admin, this action doesn't assign cities
            // You might want to redirect to edit page instead
            return response()->json([
                'success' => false,
                'message' => 'To make this admin a city admin, please edit and assign cities.'
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => "Admin type changed to {$newType} successfully",
            'new_type' => $newType
        ]);
    }

    // Get admin statistics (API endpoint)
    public function getStats()
    {
        $this->ensureSuperAdmin();

        $stats = [
            'total_admins' => Admin::where('email', '!=', 'dev@dev.com')->count(),
            'super_admins' => Admin::where('email', '!=', 'dev@dev.com')->doesntHave('cities')->count(),
            'city_admins' => Admin::where('email', '!=', 'dev@dev.com')->has('cities')->count(),
            'admins_with_images' => Admin::where('email', '!=', 'dev@dev.com')->whereNotNull('image_id')->count(),
            'recent_admins' => Admin::where('email', '!=', 'dev@dev.com')
                                  ->where('created_at', '>=', now()->subWeek())
                                  ->count(),
        ];

        return response()->json($stats);
    }

    // Export admins data
    public function exportAdmins(Request $request)
    {
        $this->ensureSuperAdmin();

        $query = Admin::with(['cities'])
                     ->where('email', '!=', 'dev@dev.com');

        // Apply filters if provided
        if ($request->filled('admin_type')) {
            if ($request->admin_type === 'super') {
                $query->doesntHave('cities');
            } elseif ($request->admin_type === 'city') {
                $query->has('cities');
            }
        }

        if ($request->filled('city_id')) {
            $query->whereHas('cities', function($cityQuery) use ($request) {
                $cityQuery->where('cities.id', $request->city_id);
            });
        }

        $admins = $query->orderBy('name')->get();
        
        $filename = 'admins_export_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];
        
        $callback = function() use ($admins) {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'Name', 'Email', 'Admin Type', 'Assigned Cities', 
                'Cities Count', 'Has Image', 'Created At'
            ]);
            
            foreach ($admins as $admin) {
                $adminType = $admin->cities->count() > 0 ? 'City Admin' : 'Super Admin';
                $assignedCities = $admin->cities->pluck('name')->implode(', ');
                
                fputcsv($file, [
                    $admin->name,
                    $admin->email,
                    $adminType,
                    $assignedCities,
                    $admin->cities->count(),
                    $admin->image_id ? 'Yes' : 'No',
                    $admin->created_at
                ]);
            }
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
}