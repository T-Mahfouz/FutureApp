<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\City;
use App\Models\Media;
use Illuminate\Support\Facades\Storage;

class CityController extends Controller
{
    // Show all cities
    public function index()
    {
        $cities = City::with(['image', 'admins', 'services', 'categories'])
                     ->withCount(['services', 'categories', 'admins'])
                     ->paginate(25);
        return view('city.index', compact('cities'));
    }

    // Show the form to create new city
    public function create()
    {
        $city = new City();
        return view('city.edit', compact('city'));
    }

    // Show the form for editing the specified city
    public function edit(City $city)
    {
        return view('city.edit', compact('city'));
    }

    // Save a newly created city
    public function store(Request $request)
    {
        $city = new City();
        return $this->update($request, $city);
    }

    // Update the specified city
    public function update(Request $request, City $city)
    {
        // Validation rules
        $rules = [
            'name' => 'required|string|max:255|unique:cities,name,' . ($city->id ?? 'NULL'),
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];

        $request->validate($rules);

        // Handle image upload
        $imageId = $city->image_id;
        if($request->hasFile('image')){
            $image = $request->file('image');
            $path = $image->store('all_images','public');
            
            // Create media record
            $media = Media::create([
                'path' => $path,
                'type' => 'image'
            ]);
            
            $imageId = $media->id;
            
            // Delete old image if exists
            if($city->image_id && $city->image){
                Storage::disk('public')->delete($city->image->path);
                $city->image->delete();
            }
        }

        // Update city fields
        $city->name = $request->input('name');
        $city->image_id = $imageId;

        $city->save();

        $message = $city->wasRecentlyCreated ? 'City has been created successfully' : 'City has been updated successfully';

        return redirect()
            ->route('city.index')
            ->with('status', $message);
    }

    // Show the specified city details
    public function show(City $city)
    {
        $city->load(['image', 'admins', 'services', 'categories', 'config']);
        return view('city.show', compact('city'));
    }

    // Delete the specified city
    public function destroy(City $city)
    {
        // Check if city has related data
        $hasServices = $city->services()->count() > 0;
        $hasCategories = $city->categories()->count() > 0;
        $hasAdmins = $city->admins()->count() > 0;
        $hasUsers = $city->users()->count() > 0;

        if($hasServices || $hasCategories || $hasAdmins || $hasUsers){
            return redirect()
                ->route('city.index')
                ->with('error', 'Cannot delete city. It has related services, categories, admins, or users.');
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
}