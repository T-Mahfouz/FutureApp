<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\City;
use App\Models\Media;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
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

    /**
     * Delete children recursively (with city access control)
     */
    private function deleteChildrenRecursively($category, &$deletedItems)
    {
        $accessibleCityIds = $this->getAccessibleCityIds();
        
        // Only delete children that are in accessible cities
        $children = $category->children()->whereIn('city_id', $accessibleCityIds)->get();
        
        foreach ($children as $child) {
            // Recursively delete grandchildren
            $this->deleteChildrenRecursively($child, $deletedItems);
            
            // Delete child's image if exists
            if($child->image) {
                Storage::disk('public')->delete($child->image->path);
                $child->image->delete();
            }
            
            // Delete the child category
            $child->delete();
            $deletedItems[] = "category '{$child->name}'";
        }
    }

    // Show all categories
    public function index(Request $request)
    {
        $query = Category::with(['image', 'city', 'parent'])
                         ->withCount(['services', 'children']);

        // Apply city restriction based on admin's assigned cities
        $query = $this->applyCityRestriction($query);

        // Search by name
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('description_search')) {
            $query->where('description', 'like', '%' . $request->description_search . '%');
        }

        // Filter by city (only show cities admin has access to)
        if ($request->filled('city_id')) {
            $accessibleCityIds = $this->getAccessibleCityIds();
            if (in_array($request->city_id, $accessibleCityIds)) {
                $query->where('city_id', $request->city_id);
            }
        }

        // Filter by parent category (only from accessible cities)
        if ($request->filled('parent_filter')) {
            $accessibleCityIds = $this->getAccessibleCityIds();
            
            if ($request->parent_filter === 'main') {
                $query->whereNull('parent_id');
            } elseif ($request->parent_filter === 'sub') {
                $query->whereNotNull('parent_id');
            } elseif (is_numeric($request->parent_filter)) {
                // Ensure parent category is from accessible cities
                $parentCategory = Category::find($request->parent_filter);
                if ($parentCategory && in_array($parentCategory->city_id, $accessibleCityIds)) {
                    $query->where('parent_id', $request->parent_filter);
                }
            }
        }

        // Filter by active status
        if ($request->filled('active')) {
            $query->where('active', $request->active);
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
        
        $allowedSorts = ['name', 'created_at', 'services_count', 'children_count'];
        if (in_array($sortBy, $allowedSorts)) {
            if (in_array($sortBy, ['services_count', 'children_count'])) {
                $query->orderBy($sortBy, $sortDirection);
            } else {
                $query->orderBy($sortBy, $sortDirection);
            }
        }

        $categories = $query->paginate(25)->appends($request->query());
        
        // Get filter options - only cities admin has access to
        $accessibleCityIds = $this->getAccessibleCityIds();
        $cities = City::whereIn('id', $accessibleCityIds)->orderBy('name')->get();
        
        // Only show parent categories from accessible cities
        $parentCategories = Category::whereNull('parent_id')
                                  ->whereIn('city_id', $accessibleCityIds)
                                  ->orderBy('name')
                                  ->get();

        return view('category.index', compact('categories', 'cities', 'parentCategories'));
    }

    // Show the form to create new category
    public function create()
    {
        $category = new Category();
        
        // Only show cities admin has access to
        $accessibleCityIds = $this->getAccessibleCityIds();
        $cities = City::whereIn('id', $accessibleCityIds)->get();
        
        // Only show parent categories from accessible cities
        $parentCategories = Category::whereNull('parent_id')
                                  ->whereIn('city_id', $accessibleCityIds)
                                  ->get();
        
        return view('category.edit', compact('category', 'cities', 'parentCategories'));
    }

    // Show the form for editing the specified category
    public function edit(Category $category)
    {
        // Check if admin has access to this category's city
        $accessibleCityIds = $this->getAccessibleCityIds();
        if (!in_array($category->city_id, $accessibleCityIds)) {
            abort(403, 'You do not have permission to edit this category.');
        }

        // Only show cities admin has access to
        $cities = City::whereIn('id', $accessibleCityIds)->get();
        
        // Only show parent categories from accessible cities (excluding current category)
        $parentCategories = Category::whereNull('parent_id')
                                  ->whereIn('city_id', $accessibleCityIds)
                                  ->where('id', '!=', $category->id)
                                  ->get();
        
        return view('category.edit', compact('category', 'cities', 'parentCategories'));
    }

    // Save a newly created category
    public function store(Request $request)
    {
        $category = new Category();
        return $this->update($request, $category);
    }

    // Update the specified category
    public function update(Request $request, Category $category)
    {
        // For existing categories, check if admin has access to this category's city
        if ($category->exists) {
            $accessibleCityIds = $this->getAccessibleCityIds();
            if (!in_array($category->city_id, $accessibleCityIds)) {
                abort(403, 'You do not have permission to edit this category.');
            }
        }

        $accessibleCityIds = $this->getAccessibleCityIds();

        // Validation rules
        $rules = [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'city_id' => ['required', 'exists:cities,id', function ($attribute, $value, $fail) use ($accessibleCityIds) {
                if (!in_array($value, $accessibleCityIds)) {
                    $fail('You do not have permission to create/edit categories in this city.');
                }
            }],
            'parent_id' => ['nullable', 'exists:categories,id', function ($attribute, $value, $fail) use ($accessibleCityIds) {
                if ($value) {
                    $parentCategory = Category::find($value);
                    if ($parentCategory && !in_array($parentCategory->city_id, $accessibleCityIds)) {
                        $fail('You do not have permission to use this parent category.');
                    }
                }
            }],
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'active' => 'nullable|boolean',
        ];

        $request->validate($rules);

        // Handle image upload
        $imageId = $category->image_id;
        if($request->hasFile('image')){
            $image = $request->file('image');
            
            $media = resizeImage($image, $this->storagePath);
            
            $imageId = $media->id ?? null;
            
            // Delete old image if exists
            if($imageId && $category->image_id && $category->image){
                Storage::disk('public')->delete($category->image->path);
                $category->image->delete();
            }
        }

        // Update category fields
        $category->name = $request->input('name');
        $category->description = $request->input('description');
        $category->city_id = $request->input('city_id');
        $category->parent_id = $request->input('parent_id');
        $category->image_id = $imageId;
        $category->active = $request->input('active', 0) == 1;

        $category->save();

        $message = $category->wasRecentlyCreated ? 'Category has been created successfully' : 'Category has been updated successfully';

        return redirect()
            ->route('category.index')
            ->with('status', $message);
    }

    // Show the specified category details
    public function show(Category $category)
    {
        // Check if admin has access to this category's city
        $accessibleCityIds = $this->getAccessibleCityIds();
        if (!in_array($category->city_id, $accessibleCityIds)) {
            abort(403, 'You do not have permission to view this category.');
        }

        $category->load(['image', 'city', 'parent', 'children', 'services']);
        return view('category.show', compact('category'));
    }

    // Delete the specified category
    public function destroy(Category $category)
    {
        // Check if admin has access to this category's city
        $accessibleCityIds = $this->getAccessibleCityIds();
        if (!in_array($category->city_id, $accessibleCityIds)) {
            abort(403, 'You do not have permission to delete this category.');
        }

        // Check if category has children or services (only count those in accessible cities)
        $hasChildren = $category->children()->whereIn('city_id', $accessibleCityIds)->count() > 0;
        $hasServices = $category->services()->whereIn('city_id', $accessibleCityIds)->count() > 0;

        if($hasChildren || $hasServices){
            return redirect()
                ->route('category.index')
                ->with('error', 'Cannot delete category. It has subcategories or services assigned to it.');
        }

        // Delete image if exists
        if($category->image){
            Storage::disk('public')->delete($category->image->path);
            $category->image->delete();
        }

        $category->delete();
        
        return redirect()
            ->route('category.index')
            ->with('status', 'Category has been deleted successfully');
    }

    // Toggle category active status
    public function toggleStatus(Category $category)
    {
        // Check if admin has access to this category's city
        $accessibleCityIds = $this->getAccessibleCityIds();
        if (!in_array($category->city_id, $accessibleCityIds)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to modify this category.'
            ], 403);
        }

        $category->active = !$category->active;
        $category->save();

        $status = $category->active ? 'activated' : 'deactivated';
        
        return redirect()
            ->back()
            ->with('status', "Category has been {$status} successfully");
    }

    // Bulk delete categories
    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'category_ids' => 'required|array',
            'category_ids.*' => 'exists:categories,id'
        ]);

        $accessibleCityIds = $this->getAccessibleCityIds();
        $deletedItems = [];
        $errors = [];

        foreach ($request->category_ids as $categoryId) {
            try {
                $category = Category::find($categoryId);
                if (!$category) continue;

                // Check if admin has access to this category's city
                if (!in_array($category->city_id, $accessibleCityIds)) {
                    $errors[] = "No permission to delete category '{$category->name}'";
                    continue;
                }

                $categoryName = $category->name;
                $currentDeletedItems = [];

                // Delete all sub-categories (children) recursively (only from accessible cities)
                $this->deleteChildrenRecursively($category, $currentDeletedItems);

                // Delete all services associated with this category (only from accessible cities)
                $services = $category->services()->whereIn('city_id', $accessibleCityIds)->get();
                foreach($services as $service) {
                    // Delete service images
                    foreach($service->images as $serviceImage) {
                        if($serviceImage->path) {
                            Storage::disk('public')->delete($serviceImage->path);
                        }
                        $serviceImage->delete();
                    }

                    // Delete service main image
                    if($service->image) {
                        Storage::disk('public')->delete($service->image->path);
                        $service->image->delete();
                    }

                    // Delete service phones
                    $service->phones()->delete();

                    // Delete service favorites
                    $service->favorites()->delete();

                    // Delete service rates
                    $service->rates()->delete();

                    // Delete service notifications
                    $service->notifications()->delete();

                    // Detach service from categories
                    $service->categories()->detach();

                    // Delete the service
                    $service->delete();
                    $currentDeletedItems[] = "service '{$service->name}'";
                }

                // Delete category image
                if($category->image) {
                    Storage::disk('public')->delete($category->image->path);
                    $category->image->delete();
                }

                // Delete the main category
                $category->delete();
                $currentDeletedItems[] = "category '{$categoryName}'";

                $deletedItems = array_merge($deletedItems, $currentDeletedItems);

            } catch (\Exception $e) {
                $errors[] = "Error deleting category '{$categoryName}': " . $e->getMessage();
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
            ->route('category.index')
            ->with(!empty($deletedItems) ? 'status' : 'error', $message ?: 'No items were deleted.');
    }
}