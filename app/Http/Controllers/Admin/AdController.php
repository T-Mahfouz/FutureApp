<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ad;
use App\Models\City;
use App\Models\Media;
use Illuminate\Support\Facades\Storage;

class AdController extends Controller
{
    // Show all ads
    public function index(Request $request)
    {
        $query = Ad::with(['city', 'image']);
        
        // Filter by city if provided
        if ($request->has('city_id') && $request->city_id) {
            $query->where('city_id', $request->city_id);
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
        $cities = City::all();
        
        return view('ad.index', compact('ads', 'cities'));
    }

    // Show the form to create new ad
    public function create()
    {
        $ad = new Ad();
        $cities = City::all();
        return view('ad.edit', compact('ad', 'cities'));
    }

    // Show the form for editing the specified ad
    public function edit(Ad $ad)
    {
        $cities = City::all();
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
        // Validation rules
        $rules = [
            'name' => 'required|string|max:255',
            'location' => 'required|in:home,category_profile,service_profile',
            'city_id' => 'required|exists:cities,id',
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
        $ad->load(['city', 'image']);
        return view('ad.show', compact('ad'));
    }

    // Delete the specified ad
    public function destroy(Ad $ad)
    {
        // Delete image if exists
        if($ad->image){
            Storage::disk('public')->delete($ad->image->path);
            $ad->image->delete();
        }

        $ad->delete();
        
        return redirect()
            ->route('ad.index')
            ->with('status', 'Ad has been deleted successfully');
    }

    // Bulk actions
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:delete',
            'ad_ids' => 'required|array',
            'ad_ids.*' => 'exists:ads,id'
        ]);

        $ads = Ad::whereIn('id', $request->ad_ids);

        if ($request->action === 'delete') {
            // Delete images for selected ads
            foreach ($ads->get() as $ad) {
                if($ad->image){
                    Storage::disk('public')->delete($ad->image->path);
                    $ad->image->delete();
                }
            }
            
            $ads->delete();
            $message = 'Selected ads deleted successfully';
        }

        return redirect()->route('ad.index')->with('status', $message);
    }
}