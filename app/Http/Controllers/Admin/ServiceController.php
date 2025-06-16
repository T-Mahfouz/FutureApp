<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Service;
use App\Models\City;
use App\Models\Category;
use App\Models\Media;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class ServiceController extends Controller
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

    public function index(Request $request)
    {
        $query = Service::with(['city', 'image', 'categories', 'phones'])
                       ->withCount(['rates', 'favorites', 'images']);

        // Apply city restriction based on admin's assigned cities
        $query = $this->applyCityRestriction($query);

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

        // Filter by city (only show cities admin has access to)
        if ($request->filled('city_id')) {
            $accessibleCityIds = $this->getAccessibleCityIds();
            if (in_array($request->city_id, $accessibleCityIds)) {
                $query->where('city_id', $request->city_id);
            }
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

        if ($request->filled('request_status')) {
            switch ($request->request_status) {
                case 'pending':
                    $query->pending();
                    break;
                case 'approved':
                    $query->approved();
                    break;
                case 'rejected':
                    $query->rejected();
                    break;
                case 'user_requests':
                    $query->userRequests();
                    break;
                case 'admin_created':
                    $query->adminCreated();
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
        
        // Get filter options - only cities admin has access to
        $accessibleCityIds = $this->getAccessibleCityIds();
        $cities = City::whereIn('id', $accessibleCityIds)->orderBy('name')->get();
        
        // Get categories - only from accessible cities
        $categories = Category::whereIn('city_id', $accessibleCityIds)->orderBy('created_at')->get();

        return view('service.index', compact('services', 'cities', 'categories'));
    }

    public function create()
    {
        $service = new Service();
        
        // Only show cities admin has access to
        $accessibleCityIds = $this->getAccessibleCityIds();
        $cities = City::whereIn('id', $accessibleCityIds)->get();
        
        // Only show categories from accessible cities
        $categories = Category::whereIn('city_id', $accessibleCityIds)->get();
        
        // Only show parent services from accessible cities
        $parentServices = Service::whereIn('city_id', $accessibleCityIds)
                                ->where('parent_id', null)
                                ->get();
        
        return view('service.edit', compact('service', 'cities', 'categories', 'parentServices'));
    }

    public function edit(Service $service)
    {
        // Check if admin has access to this service's city
        $accessibleCityIds = $this->getAccessibleCityIds();
        if (!in_array($service->city_id, $accessibleCityIds)) {
            abort(403, 'You do not have permission to edit this service.');
        }

        // Only show cities admin has access to
        $cities = City::whereIn('id', $accessibleCityIds)->get();
        
        // Only show categories from accessible cities
        $categories = Category::whereIn('city_id', $accessibleCityIds)->get();
        
        // Only show parent services from accessible cities (excluding current service)
        $parentServices = Service::whereIn('city_id', $accessibleCityIds)
                                ->where('parent_id', null)
                                ->where('id', '!=', $service->id)
                                ->get();
        
        return view('service.edit', compact('service', 'cities', 'categories', 'parentServices'));
    }

    public function store(Request $request)
    {
        $service = new Service();
        return $this->update($request, $service);
    }

    public function update(Request $request, Service $service)
    {
        // For existing services, check if admin has access to this service's city
        if ($service->exists) {
            $accessibleCityIds = $this->getAccessibleCityIds();
            if (!in_array($service->city_id, $accessibleCityIds)) {
                abort(403, 'You do not have permission to edit this service.');
            }
        }

        $accessibleCityIds = $this->getAccessibleCityIds();
        
        $rules = [
            'name' => 'required|string|max:255',
            'city_id' => ['required', 'exists:cities,id', function ($attribute, $value, $fail) use ($accessibleCityIds) {
                if (!in_array($value, $accessibleCityIds)) {
                    $fail('You do not have permission to create/edit services in this city.');
                }
            }],
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
            'parent_id' => ['nullable', 'exists:services,id', function ($attribute, $value, $fail) use ($accessibleCityIds) {
                if ($value) {
                    $parentService = Service::find($value);
                    if ($parentService && !in_array($parentService->city_id, $accessibleCityIds)) {
                        $fail('You do not have permission to use this parent service.');
                    }
                }
            }],
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'categories' => 'nullable|array',
            'categories.*' => ['exists:categories,id', function ($attribute, $value, $fail) use ($accessibleCityIds) {
                $category = Category::find($value);
                if ($category && !in_array($category->city_id, $accessibleCityIds)) {
                    $fail('You do not have permission to use this category.');
                }
            }],
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


            'user_id' => $request->user_id ?? $service->user_id,
            'is_request' => $request->has('is_request') ? true : $service->is_request,
            'requested_at' => $request->requested_at ?? $service->requested_at,
            'approved_at' => $service->approved_at,
            'approved_by' => $service->approved_by,
            'rejected_at' => $service->rejected_at,
            'rejected_by' => $service->rejected_by,
            'rejection_reason' => $service->rejection_reason,
        ]);

        $service->save();

        // Sync categories (only from accessible cities)
        if($request->has('categories')){
            $accessibleCategories = Category::whereIn('id', $request->categories)
                                          ->whereIn('city_id', $accessibleCityIds)
                                          ->pluck('id')
                                          ->toArray();
            $service->categories()->sync($accessibleCategories);
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
        // Check if admin has access to this service's city
        $accessibleCityIds = $this->getAccessibleCityIds();
        if (!in_array($service->city_id, $accessibleCityIds)) {
            abort(403, 'You do not have permission to view this service.');
        }

        $service->load([
            'city', 'image', 'categories', 'images', 'phones', 
            'parentService', 'subServices', 'rates.user', 'favorites.user'
        ]);
        
        return view('service.show', compact('service'));
    }

    public function destroy(Service $service)
    {
        // Check if admin has access to this service's city
        $accessibleCityIds = $this->getAccessibleCityIds();
        if (!in_array($service->city_id, $accessibleCityIds)) {
            abort(403, 'You do not have permission to delete this service.');
        }

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
        // Check if admin has access to this service's city
        $accessibleCityIds = $this->getAccessibleCityIds();
        if (!in_array($service->city_id, $accessibleCityIds)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to modify this service.'
            ], 403);
        }

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

        $accessibleCityIds = $this->getAccessibleCityIds();
        $deletedItems = [];
        $errors = [];

        foreach ($request->service_ids as $serviceId) {
            try {
                $service = Service::find($serviceId);
                if (!$service) continue;

                // Check if admin has access to this service's city
                if (!in_array($service->city_id, $accessibleCityIds)) {
                    $errors[] = "No permission to delete service '{$service->name}'";
                    continue;
                }

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

        $accessibleCityIds = $this->getAccessibleCityIds();
        
        // Only update services in accessible cities
        $updatedCount = Service::whereIn('id', $request->service_ids)
                              ->whereIn('city_id', $accessibleCityIds)
                              ->update(['valid' => $request->status]);

        $status = $request->status ? 'activated' : 'deactivated';
        
        return redirect()
            ->route('service.index')
            ->with('status', "{$updatedCount} services have been {$status} successfully");
    }




    /* ================== Requested Services Management =========================== */

    /**
     * NEW: Show pending service requests
     */
    public function requests(Request $request)
    {
        $query = Service::with(['city', 'image', 'categories', 'phones', 'user'])
                       ->withCount(['rates', 'favorites', 'images'])
                       ->userRequests(); // Only user-submitted requests

        // Apply city restriction
        $query = $this->applyCityRestriction($query);

        // Filter by request status
        if ($request->filled('request_status')) {
            switch ($request->request_status) {
                case 'pending':
                    $query->pending();
                    break;
                case 'approved':
                    $query->approved();
                    break;
                case 'rejected':
                    $query->rejected();
                    break;
            }
        } else {
            // Default to pending requests
            $query->pending();
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%')
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('name', 'like', '%' . $search . '%')
                               ->orWhere('email', 'like', '%' . $search . '%');
                  });
            });
        }

        // Filter by city
        if ($request->filled('city_id')) {
            $accessibleCityIds = $this->getAccessibleCityIds();
            if (in_array($request->city_id, $accessibleCityIds)) {
                $query->where('city_id', $request->city_id);
            }
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('requested_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('requested_at', '<=', $request->date_to);
        }

        $services = $query->latest('requested_at')->paginate(25)->appends($request->query());
        
        // Get filter options
        $accessibleCityIds = $this->getAccessibleCityIds();
        $cities = City::whereIn('id', $accessibleCityIds)->orderBy('name')->get();

        return view('service.requests', compact('services', 'cities'));
    }

    /**
     * NEW: Approve a service request
     */
    public function approve(Service $service)
    {
        // Check if admin has access to this service's city
        $accessibleCityIds = $this->getAccessibleCityIds();
        if (!in_array($service->city_id, $accessibleCityIds)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to approve this service.'
            ], 403);
        }

        // Check if it's a pending request
        if (!$service->isPending()) {
            return response()->json([
                'success' => false,
                'message' => 'This service is not pending approval.'
            ], 400);
        }

        $service->approve(Auth::guard('admin')->id());

        return response()->json([
            'success' => true,
            'message' => 'Service has been approved successfully.',
            'status' => 'approved'
        ]);
    }

    /**
     * NEW: Reject a service request
     */
    public function reject(Request $request, Service $service)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:1000'
        ]);

        // Check if admin has access to this service's city
        $accessibleCityIds = $this->getAccessibleCityIds();
        if (!in_array($service->city_id, $accessibleCityIds)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to reject this service.'
            ], 403);
        }

        // Check if it's a pending request
        if (!$service->isPending()) {
            return response()->json([
                'success' => false,
                'message' => 'This service is not pending approval.'
            ], 400);
        }

        $service->reject($request->rejection_reason, Auth::guard('admin')->id());

        return response()->json([
            'success' => true,
            'message' => 'Service has been rejected.',
            'status' => 'rejected'
        ]);
    }

    /**
     * NEW: Bulk approve/reject service requests
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:approve,reject',
            'service_ids' => 'required|array',
            'service_ids.*' => 'exists:services,id',
            'rejection_reason' => 'required_if:action,reject|string|max:1000'
        ]);

        $accessibleCityIds = $this->getAccessibleCityIds();
        $adminId = Auth::guard('admin')->id();
        
        // Only process services in accessible cities and pending status
        $services = Service::whereIn('id', $request->service_ids)
            ->whereIn('city_id', $accessibleCityIds)
            ->pending()
            ->get();

        $processedCount = 0;
        
        foreach ($services as $service) {
            if ($request->action === 'approve') {
                $service->approve($adminId);
            } else {
                $service->reject($request->rejection_reason, $adminId);
            }
            $processedCount++;
        }

        $action = $request->action === 'approve' ? 'approved' : 'rejected';
        $totalRequested = count($request->service_ids);
        
        if ($processedCount < $totalRequested) {
            $skipped = $totalRequested - $processedCount;
            $message = "{$processedCount} services {$action} successfully. {$skipped} services were skipped (no permission or not pending).";
        } else {
            $message = "{$processedCount} services {$action} successfully.";
        }

        return redirect()->route('service.requests')->with('status', $message);
    }
}