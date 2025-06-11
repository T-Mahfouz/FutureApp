<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\City;
use App\Models\Media;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    // Show all categories
    public function index(Request $request)
    {
        $query = Category::with(['image', 'city', 'parent'])
                         ->withCount(['services', 'children']);

        // Search by name
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Filter by city
        if ($request->filled('city_id')) {
            $query->where('city_id', $request->city_id);
        }

        // Filter by parent category
        if ($request->filled('parent_filter')) {
            if ($request->parent_filter === 'main') {
                $query->whereNull('parent_id');
            } elseif ($request->parent_filter === 'sub') {
                $query->whereNotNull('parent_id');
            } elseif (is_numeric($request->parent_filter)) {
                $query->where('parent_id', $request->parent_filter);
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
        
        // Get filter options
        $cities = City::orderBy('name')->get();
        $parentCategories = Category::whereNull('parent_id')->orderBy('name')->get();

        return view('category.index', compact('categories', 'cities', 'parentCategories'));
    }

    // Show the form to create new category
    public function create()
    {
        $category = new Category();
        $cities = City::all();
        $parentCategories = Category::whereNull('parent_id')->get();
        return view('category.edit', compact('category', 'cities', 'parentCategories'));
    }

    // Show the form for editing the specified category
    public function edit(Category $category)
    {
        $cities = City::all();
        $parentCategories = Category::whereNull('parent_id')->where('id', '!=', $category->id)->get();
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
        // Validation rules
        $rules = [
            'name' => 'required|string|max:255',
            'city_id' => 'required|exists:cities,id',
            'parent_id' => 'nullable|exists:categories,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'active' => 'nullable|boolean',
        ];

        $request->validate($rules);

        // Handle image upload
        $imageId = $category->image_id;
        if($request->hasFile('image')){
            $image = $request->file('image');
            $path = $image->store('all_images', 'public');
            
            // Create media record
            $media = Media::create([
                'path' => $path,
                'type' => 'image'
            ]);
            
            $imageId = $media->id;
            
            // Delete old image if exists
            if($category->image_id && $category->image){
                Storage::disk('public')->delete($category->image->path);
                $category->image->delete();
            }
        }

        // Update category fields
        $category->name = $request->input('name');
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
        $category->load(['image', 'city', 'parent', 'children', 'services']);
        return view('category.show', compact('category'));
    }

    // Delete the specified category
    public function destroy(Category $category)
    {
        // Check if category has children or services
        $hasChildren = $category->children()->count() > 0;
        $hasServices = $category->services()->count() > 0;

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

        $deletedItems = [];
        $errors = [];

        foreach ($request->category_ids as $categoryId) {
            try {
                $category = Category::find($categoryId);
                if (!$category) continue;

                $categoryName = $category->name;
                $currentDeletedItems = [];

                // Delete all sub-categories (children) recursively
                $this->deleteChildrenRecursively($category, $currentDeletedItems);

                // Delete all services associated with this category
                $services = $category->services;
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