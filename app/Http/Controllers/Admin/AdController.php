<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ad;
use App\Models\City;
use App\Models\Category;
use App\Models\Service;
use App\Models\Media;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AdController extends Controller
{
    /**
     * Get cities that the current admin can access
     */
    private function getAccessibleCityIds()
    {
        $admin = Auth::guard('admin')->user();
        $adminCities = $admin->cities();
        
        // If admin has no city assignments, they can access all cities (super admin)
        if ($adminCities->count() == 0) {
            return City::pluck('id')->toArray();
        }
        
        // Otherwise, return only assigned cities
        return $adminCities->pluck('cities.id')->toArray();
    }

    /**
     * Apply city restriction to query
     */
    private function applyCityRestriction($query)
    {
        $accessibleCityIds = $this->getAccessibleCityIds();
        return $query->whereIn('city_id', $accessibleCityIds);
    }

    // Show all ads
    public function index(Request $request)
    {
        $query = Ad::with(['city', 'category', 'service', 'image']);
        
        // Apply city restriction based on admin's assigned cities
        $query = $this->applyCityRestriction($query);
        
        // Filter by city if provided (only show cities admin has access to)
        if ($request->has('city_id') && $request->city_id) {
            $accessibleCityIds = $this->getAccessibleCityIds();
            if (in_array($request->city_id, $accessibleCityIds)) {
                $query->where('city_id', $request->city_id);
            }
        }
        
        // Filter by category if provided
        if ($request->has('category_id') && $request->category_id) {
            $query->where('category_id', $request->category_id);
        }
        
        // Filter by service if provided
        if ($request->has('service_id') && $request->service_id) {
            $query->where('service_id', $request->service_id);
        }
        
        // Filter by location if provided
        if ($request->has('location') && $request->location) {
            $query->where('location', $request->location);
        }
        
        if ($request->has('status') && $request->status) {
            if ($request->status === 'active') {
                $query->active();
            } elseif ($request->status === 'expired') {
                $query->expired();
            }
        }
        
        // Search by name
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhereHas('category', function($categoryQuery) use ($search) {
                      $categoryQuery->where('name', 'LIKE', "%{$search}%");
                  })
                  ->orWhereHas('service', function($serviceQuery) use ($search) {
                      $serviceQuery->where('name', 'LIKE', "%{$search}%");
                  });
            });
        }
        
        $ads = $query->latest()->paginate(25)->withQueryString();
        
        // Only show cities admin has access to in filter dropdown
        $accessibleCityIds = $this->getAccessibleCityIds();
        $cities = City::whereIn('id', $accessibleCityIds)->orderBy('name')->get();
        
        // Get categories and services for filtering (from accessible cities only)
        $categories = Category::whereIn('city_id', $accessibleCityIds)->orderBy('name')->get();
        $services = Service::whereIn('city_id', $accessibleCityIds)->orderBy('name')->get();
        
        return view('ad.index', compact('ads', 'cities', 'categories', 'services'));
    }

    // Show the form to create new ad
    public function create()
    {
        $ad = new Ad();
        
        // Only show cities admin has access to
        $accessibleCityIds = $this->getAccessibleCityIds();
        $cities = City::whereIn('id', $accessibleCityIds)->orderBy('name')->get();
        
        return view('ad.edit', compact('ad', 'cities'));
    }

    // Show the form for editing the specified ad
    public function edit(Ad $ad)
    {
        // Check if admin has access to this ad's city
        $accessibleCityIds = $this->getAccessibleCityIds();
        if (!in_array($ad->city_id, $accessibleCityIds)) {
            abort(403, 'You do not have permission to edit this ad.');
        }

        // Only show cities admin has access to
        $cities = City::whereIn('id', $accessibleCityIds)->orderBy('name')->get();
        
        // Get categories for the selected city
        $categories = Category::where('city_id', $ad->city_id)->orderBy('name')->get();
        
        // Get services for the selected city and category (if category is selected)
        $services = collect();
        if ($ad->category_id) {
            $services = Service::where('city_id', $ad->city_id)
                             ->whereHas('categories', function($q) use ($ad) {
                                 $q->where('categories.id', $ad->category_id);
                             })
                             ->orderBy('name')
                             ->get();
        }
        
        return view('ad.edit', compact('ad', 'cities', 'categories', 'services'));
    }

    // Save a newly created ad
    public function store(Request $request)
    {
        $ad = new Ad();
        return $this->update($request, $ad);
    }

    // Update the specified ad
    public function update(Request $request, Ad $ad)
    {
        // For existing ads, check if admin has access to this ad's city
        if ($ad->exists) {
            $accessibleCityIds = $this->getAccessibleCityIds();
            if (!in_array($ad->city_id, $accessibleCityIds)) {
                abort(403, 'You do not have permission to edit this ad.');
            }
        }

        $accessibleCityIds = $this->getAccessibleCityIds();

        // Validation rules
        $rules = [
            'name' => 'required|string|max:255',
            'location' => 'required|in:home,category_profile,service_profile,all_locations',
            'link' => 'nullable|url|max:500', 
            'expiration_date' => 'nullable|date|after:today',
            'city_id' => ['required', 'exists:cities,id', function ($attribute, $value, $fail) use ($accessibleCityIds) {
                if (!in_array($value, $accessibleCityIds)) {
                    $fail('You do not have permission to create/edit ads in this city.');
                }
            }],
            'category_id' => ['nullable', 'exists:categories,id', function ($attribute, $value, $fail) use ($request) {
                if ($value && $request->city_id) {
                    $category = Category::find($value);
                    if ($category && $category->city_id != $request->city_id) {
                        $fail('The selected category must belong to the selected city.');
                    }
                }
            }],
            'service_id' => ['nullable', 'exists:services,id', function ($attribute, $value, $fail) use ($request) {
                if ($value && $request->city_id) {
                    $service = Service::find($value);
                    if ($service && $service->city_id != $request->city_id) {
                        $fail('The selected service must belong to the selected city.');
                    }
                    
                    // If category is selected, ensure service belongs to that category
                    if ($request->category_id) {
                        $serviceInCategory = Service::where('id', $value)
                                                  ->whereHas('categories', function($q) use ($request) {
                                                      $q->where('categories.id', $request->category_id);
                                                  })
                                                  ->exists();
                        if (!$serviceInCategory) {
                            $fail('The selected service must belong to the selected category.');
                        }
                    }
                }
            }],
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];

        $request->validate($rules);

        // Handle image upload
        $imageId = $ad->image_id;
        if($request->hasFile('image')){
            $image = $request->file('image');
            $media = resizeImage($image, $this->storagePath);
            $imageId = $media->id ?? null;
            
            // Delete old image if exists
            if($imageId && $ad->image_id && $ad->image){
                Storage::disk('public')->delete($ad->image->path);
                $ad->image->delete();
            }
        }

        // Update ad fields
        $ad->name = $request->input('name');
        $ad->location = $request->input('location');
        $ad->link = $request->input('link');
        $ad->expiration_date = $request->input('expiration_date') ? Carbon::parse($request->input('expiration_date')) : null;
        $ad->city_id = $request->input('city_id');
        $ad->category_id = $request->input('category_id');
        $ad->service_id = $request->input('service_id');
        $ad->image_id = $imageId;

        $ad->save();

        $message = $ad->wasRecentlyCreated ? 'Ad has been created successfully' : 'Ad has been updated successfully';

        return redirect()
            ->route('ad.index')
            ->with('status', $message);
    }

    // Show the specified ad details
    public function show(Ad $ad)
    {
        // Check if admin has access to this ad's city
        $accessibleCityIds = $this->getAccessibleCityIds();
        if (!in_array($ad->city_id, $accessibleCityIds)) {
            abort(403, 'You do not have permission to view this ad.');
        }

        $ad->load(['city', 'category', 'service', 'image']);
        return view('ad.show', compact('ad'));
    }

    // Delete the specified ad
    public function destroy($id)
    {
        \DB::beginTransaction();
        try {
            $ad = Ad::find($id);
            if (!$ad) {
                return response()->json([
                    'message' => 'Ad not found'
                ], 404);
            }

            // Check if admin has access to this ad's city
            $accessibleCityIds = $this->getAccessibleCityIds();

            if (!in_array($ad->city_id, $accessibleCityIds)) {
                return response()->json([
                        'message' => 'You do not have permission to delete this ad.'
                    ], 403);
            }
            
            if($ad->image){
                Storage::disk('public')->delete($ad->image->path);
                $ad->image->delete();
            }

            $ad->delete();

            \DB::commit();

        } catch (\Exception $ex) {
            \DB::rollBack();
            return response()->json([
                'message' => 'Failed to delete ad: ' . $ex->getMessage()
            ], 500);
        }
        return response()->json([
            'message' => 'Ad has been deleted successfully'
        ]);
    }

    // Bulk actions
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:delete',
            'ad_ids' => 'required|array',
            'ad_ids.*' => 'exists:ads,id'
        ]);

        $accessibleCityIds = $this->getAccessibleCityIds();
        
        // Only get ads that are in accessible cities
        $ads = Ad::whereIn('id', $request->ad_ids)
                ->whereIn('city_id', $accessibleCityIds);

        $adsToProcess = $ads->get();
        $processedCount = $adsToProcess->count();
        $totalRequested = count($request->ad_ids);

        if ($request->action === 'delete') {
            // Delete images for selected ads (only accessible ones)
            foreach ($adsToProcess as $ad) {
                if($ad->image){
                    Storage::disk('public')->delete($ad->image->path);
                    $ad->image->delete();
                }
            }
            
            $ads->delete();
            
            if ($processedCount < $totalRequested) {
                $skipped = $totalRequested - $processedCount;
                $message = "Deleted {$processedCount} ads successfully. {$skipped} ads were skipped (no permission).";
            } else {
                $message = "Deleted {$processedCount} ads successfully.";
            }
        }

        return redirect()->route('ad.index')->with('status', $message);
    }

    /**
     * Get categories for a specific city (AJAX)
     */
    public function getCategoriesByCity(Request $request)
    {
        $cityId = $request->get('city_id');
        $accessibleCityIds = $this->getAccessibleCityIds();
        
        if (!in_array($cityId, $accessibleCityIds)) {
            return response()->json(['error' => 'No permission for this city'], 403);
        }
        
        $categories = Category::where('city_id', $cityId)
                            ->where('active', true)
                            ->orderBy('name')
                            ->get(['id', 'name']);
        
        return response()->json($categories);
    }

    /**
     * Get services for a specific city and category (AJAX)
     */
    public function getServicesByCityAndCategory(Request $request)
    {
        $cityId = $request->get('city_id');
        $categoryId = $request->get('category_id');
        $accessibleCityIds = $this->getAccessibleCityIds();
        
        if (!in_array($cityId, $accessibleCityIds)) {
            return response()->json(['error' => 'No permission for this city'], 403);
        }
        
        $query = Service::where('city_id', $cityId)->where('valid', true);
        
        if ($categoryId) {
            $query->whereHas('categories', function($q) use ($categoryId) {
                $q->where('categories.id', $categoryId);
            });
        }
        
        $services = $query->orderBy('name')->get(['id', 'name']);
        
        return response()->json($services);
    }

    public function getLocationOptions()
    {
        return [
            'home' => 'Home Page',
            'category_profile' => 'Category Profile',
            'service_profile' => 'Service Profile',
            'all_locations' => 'All Locations'
        ];
    }
}