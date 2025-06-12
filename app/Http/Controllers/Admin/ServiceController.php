<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Service;
use App\Models\City;
use App\Models\Category;
use App\Models\Media;
use Illuminate\Support\Facades\Storage;

class ServiceController extends Controller
{

    public function index(Request $request)
    {
        $query = Service::with(['city', 'image', 'categories', 'phones'])
                       ->withCount(['rates', 'favorites', 'images']);

        // Search by name, description, or phone
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%')
                  ->orWhere('brief_description', 'like', '%' . $search . '%')
                  ->orWhereHas('phones', function($phoneQuery) use ($search) {
                      $phoneQuery->where('phone', 'like', '%' . $search . '%');
                  });
            });
        }

        // Filter by city
        if ($request->filled('city_id')) {
            $query->where('city_id', $request->city_id);
        }

        // Filter by category
        if ($request->filled('category_id')) {
            $query->whereHas('categories', function($q) use ($request) {
                $q->where('categories.id', $request->category_id);
            });
        }

        // Filter by status
        if ($request->filled('valid')) {
            $query->where('valid', $request->valid);
        }

        // Filter by service type
        if ($request->filled('service_type')) {
            switch ($request->service_type) {
                case 'main':
                    $query->whereNull('parent_id');
                    break;
                case 'sub':
                    $query->whereNotNull('parent_id');
                    break;
                case 'ad':
                    $query->where('is_add', true);
                    break;
            }
        }

        // Filter by creation date
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');
        
        $allowedSorts = ['name', 'created_at', 'arrangement_order', 'rates_count', 'favorites_count'];
        if (in_array($sortBy, $allowedSorts)) {
            if (in_array($sortBy, ['rates_count', 'favorites_count'])) {
                $query->orderBy($sortBy, $sortDirection);
            } else {
                $query->orderBy($sortBy, $sortDirection);
            }
        }

        $services = $query->paginate(25)->appends($request->query());
        
        // Get filter options
        $cities = City::orderBy('name')->get();
        $categories = Category::orderBy('created_at')->get();

        return view('service.index', compact('services', 'cities', 'categories'));
    }

    public function create()
    {
        $service = new Service();
        $cities = City::all();
        $categories = Category::all();
        $parentServices = Service::where('parent_id', null)->get();
        
        return view('service.edit', compact('service', 'cities', 'categories', 'parentServices'));
    }

    public function edit(Service $service)
    {
        $cities = City::all();
        $categories = Category::all();
        $parentServices = Service::where('parent_id', null)->where('id', '!=', $service->id)->get();
        
        return view('service.edit', compact('service', 'cities', 'categories', 'parentServices'));
    }

    public function store(Request $request)
    {
        $service = new Service();
        return $this->update($request, $service);
    }

    public function update(Request $request, Service $service)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'city_id' => 'required|exists:cities,id',
            'phone' => 'nullable|string|max:255',
            'brief_description' => 'nullable|string|max:500',
            'description' => 'nullable|string',
            'lat' => 'nullable|numeric|between:-90,90',
            'lon' => 'nullable|numeric|between:-180,180',
            'website' => 'nullable|url|max:255',
            'youtube' => 'nullable|url|max:255',
            'facebook' => 'nullable|url|max:255',
            'instagram' => 'nullable|url|max:255',
            'telegram' => 'nullable|url|max:255',
            'whatsapp' => 'nullable|string|max:255',
            'video_link' => 'nullable|url|max:255',
            'address' => 'nullable|string|max:500',
            'valid' => 'boolean',
            'is_add' => 'boolean',
            'arrangement_order' => 'nullable|integer|min:1',
            'parent_id' => 'nullable|exists:services,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
            'additional_images' => 'nullable|array',
            'additional_images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'phones' => 'nullable|array',
            'phones.*' => 'nullable|string|max:255'
        ];

        $request->validate($rules);

        // Handle main image upload
        $imageId = $service->image_id;
        if($request->hasFile('image')){
            $image = $request->file('image');

            $media = resizeImage($image, $this->storagePath);
            
            $imageId = $media->id ?? null;
            
            // Delete old image if exists
            if($imageId && $service->image_id && $service->image){
                Storage::disk('public')->delete($service->image->path);
                $service->image->delete();
            }
        }

        // Update service fields
        $service->fill([
            'name' => $request->name,
            'city_id' => $request->city_id,
            'brief_description' => $request->brief_description,
            'description' => $request->description,
            'lat' => $request->lat,
            'lon' => $request->lon,
            'website' => $request->website,
            'youtube' => $request->youtube,
            'facebook' => $request->facebook,
            'instagram' => $request->instagram,
            'telegram' => $request->telegram,
            'whatsapp' => $request->whatsapp,
            'video_link' => $request->video_link,
            'address' => $request->address,
            'valid' => $request->has('valid'),
            'is_add' => $request->has('is_add'),
            'arrangement_order' => $request->arrangement_order ?? 1,
            'parent_id' => $request->parent_id,
            'image_id' => $imageId,
        ]);

        $service->save();

        // Sync categories
        if($request->has('categories')){
            $service->categories()->sync($request->categories);
        }

        // Handle phone numbers
        $service->phones()->delete(); // Clear existing phones
        
        $phoneNumbers = [];
        
        // Add primary phone if provided
        if($request->filled('phone')){
            $phoneNumbers[] = $request->phone;
        }
        
        // Add additional phones
        if($request->has('phones') && is_array($request->phones)){
            foreach($request->phones as $phone){
                if(!empty(trim($phone))){
                    $phoneNumbers[] = trim($phone);
                }
            }
        }
        
        // Remove duplicates and save unique phone numbers
        $phoneNumbers = array_unique($phoneNumbers);
        foreach($phoneNumbers as $phone){
            $service->phones()->create(['phone' => $phone]);
        }

        // Handle additional images
        if($request->hasFile('additional_images')){
            foreach($request->file('additional_images') as $image){
                
                $media = resizeImage($image, $this->storagePath);
            
                $imageId = $media->id ?? null;
                
                if ($imageId) {
                    $service->images()->attach($media->id);
                }
            }
        }

        $message = $service->wasRecentlyCreated ? 'Service has been created successfully' : 'Service has been updated successfully';

        return redirect()
            ->route('service.index')
            ->with('status', $message);
    }

    public function show(Service $service)
    {
        $service->load([
            'city', 'image', 'categories', 'images', 'phones', 
            'parentService', 'subServices', 'rates.user', 'favorites.user'
        ]);
        
        return view('service.show', compact('service'));
    }

    public function destroy(Service $service)
    {
        // Check if service has sub-services
        if($service->subServices()->count() > 0){
            return redirect()
                ->route('service.index')
                ->with('error', 'Cannot delete service. It has sub-services.');
        }

        // Delete main image if exists
        if($service->image){
            Storage::disk('public')->delete($service->image->path);
            $service->image->delete();
        }

        // Delete additional images
        foreach($service->images as $image){
            Storage::disk('public')->delete($image->path);
            $image->delete();
        }

        // Delete related records
        $service->phones()->delete();
        $service->categories()->detach();
        $service->images()->detach();
        $service->favorites()->delete();
        $service->rates()->delete();

        $service->delete();
        
        return redirect()
            ->route('service.index')
            ->with('status', 'Service has been deleted successfully');
    }

    public function toggleStatus(Service $service)
    {
        $service->update(['valid' => !$service->valid]);
        
        $status = $service->valid ? 'activated' : 'deactivated';
        return response()->json([
            'success' => true,
            'message' => "Service has been {$status} successfully",
            'status' => $service->valid
        ]);
    }

    // Bulk operations for future use
    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'service_ids' => 'required|array',
            'service_ids.*' => 'exists:services,id'
        ]);

        $deletedItems = [];
        $errors = [];

        foreach ($request->service_ids as $serviceId) {
            try {
                $service = Service::find($serviceId);
                if (!$service) continue;

                $serviceName = $service->name;

                // Check if service has sub-services
                if($service->subServices()->count() > 0){
                    $errors[] = "Cannot delete service '{$serviceName}': has sub-services";
                    continue;
                }

                // Delete main image if exists
                if($service->image){
                    Storage::disk('public')->delete($service->image->path);
                    $service->image->delete();
                }

                // Delete additional images
                foreach($service->images as $image){
                    Storage::disk('public')->delete($image->path);
                    $image->delete();
                }

                // Delete related records
                $service->phones()->delete();
                $service->categories()->detach();
                $service->images()->detach();
                $service->favorites()->delete();
                $service->rates()->delete();

                $service->delete();
                $deletedItems[] = "service '{$serviceName}'";

            } catch (\Exception $e) {
                $errors[] = "Error deleting service '{$serviceName}': " . $e->getMessage();
            }
        }

        $message = "";
        if (!empty($deletedItems)) {
            $message = "Successfully deleted: " . implode(', ', $deletedItems);
        }
        if (!empty($errors)) {
            $message .= (!empty($message) ? " | " : "") . "Errors: " . implode(", ", $errors);
        }

        return redirect()
            ->route('service.index')
            ->with(!empty($deletedItems) ? 'status' : 'error', $message ?: 'No items were deleted.');
    }

    public function bulkToggleStatus(Request $request)
    {
        $request->validate([
            'service_ids' => 'required|array',
            'service_ids.*' => 'exists:services,id',
            'status' => 'required|boolean'
        ]);

        $updatedCount = Service::whereIn('id', $request->service_ids)
                              ->update(['valid' => $request->status]);

        $status = $request->status ? 'activated' : 'deactivated';
        
        return redirect()
            ->route('service.index')
            ->with('status', "{$updatedCount} services have been {$status} successfully");
    }
}