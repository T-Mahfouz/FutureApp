<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ad;
use App\Models\City;
use App\Models\Media;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

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
        $query = Ad::with(['city', 'image']);
        
        // Apply city restriction based on admin's assigned cities
        $query = $this->applyCityRestriction($query);
        
        // Filter by city if provided (only show cities admin has access to)
        if ($request->has('city_id') && $request->city_id) {
            $accessibleCityIds = $this->getAccessibleCityIds();
            if (in_array($request->city_id, $accessibleCityIds)) {
                $query->where('city_id', $request->city_id);
            }
        }
        
        // Filter by location if provided
        if ($request->has('location') && $request->location) {
            $query->where('location', $request->location);
        }
        
        // Search by name
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%");
        }
        
        $ads = $query->latest()->paginate(25);
        
        // Only show cities admin has access to in filter dropdown
        $accessibleCityIds = $this->getAccessibleCityIds();
        $cities = City::whereIn('id', $accessibleCityIds)->get();
        
        return view('ad.index', compact('ads', 'cities'));
    }

    // Show the form to create new ad
    public function create()
    {
        $ad = new Ad();
        
        // Only show cities admin has access to
        $accessibleCityIds = $this->getAccessibleCityIds();
        $cities = City::whereIn('id', $accessibleCityIds)->get();
        
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
        $cities = City::whereIn('id', $accessibleCityIds)->get();
        
        return view('ad.edit', compact('ad', 'cities'));
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
            'location' => 'required|in:home,category_profile,service_profile',
            'city_id' => ['required', 'exists:cities,id', function ($attribute, $value, $fail) use ($accessibleCityIds) {
                if (!in_array($value, $accessibleCityIds)) {
                    $fail('You do not have permission to create/edit ads in this city.');
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
        $ad->city_id = $request->input('city_id');
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

        $ad->load(['city', 'image']);
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
                // abort(403, 'You do not have permission to delete this ad.');
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

    // Additional method to get location options (for consistency)
    public function getLocationOptions()
    {
        return [
            'home' => 'Home Page',
            'category_profile' => 'Category Profile',
            'service_profile' => 'Service Profile'
        ];
    }
}