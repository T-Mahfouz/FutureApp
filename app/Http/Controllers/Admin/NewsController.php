<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NewsImage;
use Illuminate\Http\Request;
use App\Models\News;
use App\Models\Media;
use App\Models\City;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class NewsController extends Controller
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

    // Show all news
    public function index(Request $request)
    {
        $query = News::with(['image', 'city']);
        
        // Apply city restriction based on admin's assigned cities
        $query = $this->applyCityRestriction($query);
        
        // Search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%")
                  ->orWhereHas('city', function($cityQuery) use ($search) {
                      $cityQuery->where('name', 'LIKE', "%{$search}%");
                  });
            });
        }
        
        // City filter (only show cities admin has access to)
        if ($request->filled('city_id')) {
            $accessibleCityIds = $this->getAccessibleCityIds();
            if (in_array($request->get('city_id'), $accessibleCityIds)) {
                $query->where('city_id', $request->get('city_id'));
            }
        }
        
        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->get('date_from'));
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->get('date_to'));
        }
        
        // Has image filter
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
        
        $allowedSorts = ['name', 'created_at', 'updated_at'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->latest();
        }
        
        $news = $query->paginate(25)->withQueryString();
        
        // Get only cities admin has access to for filter dropdown
        $accessibleCityIds = $this->getAccessibleCityIds();
        $cities = City::whereIn('id', $accessibleCityIds)->orderBy('name')->get();
        
        return view('news.index', compact('news', 'cities'));
    }

    // Show the form to create new news
    public function create()
    {
        $news = new News();
        
        // Only show cities admin has access to
        $accessibleCityIds = $this->getAccessibleCityIds();
        $cities = City::whereIn('id', $accessibleCityIds)->get();
        
        return view('news.edit', compact('news', 'cities'));
    }

    // Show the form for editing the specified news
    public function edit(News $news)
    {
        // Check if admin has access to this news's city
        $accessibleCityIds = $this->getAccessibleCityIds();
        if (!in_array($news->city_id, $accessibleCityIds)) {
            abort(403, 'You do not have permission to edit this news.');
        }

        // Only show cities admin has access to
        $cities = City::whereIn('id', $accessibleCityIds)->get();
        
        return view('news.edit', compact('news', 'cities'));
    }

    // Save a newly created news
    public function store(Request $request)
    {
        $news = new News();
        return $this->update($request, $news);
    }

    // Update the specified news
    public function update(Request $request, News $news)
    {
        // For existing news, check if admin has access to this news's city
        if ($news->exists) {
            $accessibleCityIds = $this->getAccessibleCityIds();
            if (!in_array($news->city_id, $accessibleCityIds)) {
                abort(403, 'You do not have permission to edit this news.');
            }
        }

        $accessibleCityIds = $this->getAccessibleCityIds();

        // Validation rules
        $rules = [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'city_id' => ['required', 'exists:cities,id', function ($attribute, $value, $fail) use ($accessibleCityIds) {
                if (!in_array($value, $accessibleCityIds)) {
                    $fail('You do not have permission to create/edit news in this city.');
                }
            }],
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'additional_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];

        $request->validate($rules);

        // Handle main image upload
        $imageId = $news->image_id;
        if($request->hasFile('image')){
            $image = $request->file('image');
            
            $media = resizeImage($image, $this->storagePath);
            
            $imageId = $media->id ?? null;
            
            // Delete old image if exists
            if($imageId && $news->image_id && $news->image){
                Storage::disk('public')->delete($news->image->path);
                $news->image->delete();
            }
        }

        // Update news fields
        $news->name = $request->input('name');
        $news->description = $request->input('description');
        $news->city_id = $request->input('city_id');
        $news->image_id = $imageId;

        $news->save();

        // Handle additional images
        if($request->hasFile('additional_images')){
            foreach($request->file('additional_images') as $additionalImage){
                $path = $additionalImage->store('all_images', 'public');
                
                // Create media record
                $media = Media::create([
                    'path' => $path,
                    'type' => 'image'
                ]);
                
                // Create news_image relationship
                $news->images()->attach($media->id);
            }
        }

        $message = $news->wasRecentlyCreated ? 'News has been created successfully' : 'News has been updated successfully';

        return redirect()
            ->route('news.index')
            ->with('status', $message);
    }

    // Show the specified news details
    public function show(News $news)
    {
        // Check if admin has access to this news's city
        $accessibleCityIds = $this->getAccessibleCityIds();
        if (!in_array($news->city_id, $accessibleCityIds)) {
            abort(403, 'You do not have permission to view this news.');
        }

        $news->load(['image', 'city', 'images', 'notifications']);
        return view('news.show', compact('news'));
    }

    // Delete the specified news
    public function destroy(News $news)
    {
        // Check if admin has access to this news's city
        $accessibleCityIds = $this->getAccessibleCityIds();
        if (!in_array($news->city_id, $accessibleCityIds)) {
            abort(403, 'You do not have permission to delete this news.');
        }

        // Check if news has related notifications
        $hasNotifications = $news->notifications()->count() > 0;

        if($hasNotifications){
            return redirect()
                ->route('news.index')
                ->with('error', 'Cannot delete news. It has related notifications.');
        }

        // Delete main image if exists
        if($news->image){
            Storage::disk('public')->delete($news->image->path);
            $news->image->delete();
        }

        // Delete additional images if exist
        if($news->images->count() > 0){
            foreach($news->images as $image){
                Storage::disk('public')->delete($image->path);
                $image->delete();
            }
        }

        $news->delete();
        
        return redirect()
            ->route('news.index')
            ->with('status', 'News has been deleted successfully');
    }
    
    public function destroyImage($id)
    {
        \DB::beginTransaction();
        try {
            $image = NewsImage::find($id);
            if (!$image) {
                return response()->json([
                    'message' => 'Image not found'
                ], 404);
            }
            
            // Get the news to check city access
            $news = $image->news;
            if ($news) {
                $accessibleCityIds = $this->getAccessibleCityIds();
                if (!in_array($news->city_id, $accessibleCityIds)) {
                    return response()->json([
                        'message' => 'You do not have permission to delete this image.'
                    ], 403);
                }
            }
            
            $media = Media::find($image->image_id);

            if ($media) {
                Storage::disk('public')->delete($media->path);
                $media->delete(); // Also delete the media record
            }
            
            $image->delete();
            \DB::commit();
            
            return response()->json([
                'message' => 'Image has been deleted successfully'
            ]);
            
        } catch (\Exception $ex) {
            \DB::rollBack();
            return response()->json([
                'message' => 'Failed to delete image: ' . $ex->getMessage()
            ], 500);
        }
    }

    // Bulk actions method
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:delete',
            'news_ids' => 'required|array',
            'news_ids.*' => 'exists:news,id'
        ]);

        $accessibleCityIds = $this->getAccessibleCityIds();
        $deletedItems = [];
        $errors = [];

        if ($request->action === 'delete') {
            foreach ($request->news_ids as $newsId) {
                try {
                    $news = News::find($newsId);
                    if (!$news) continue;

                    // Check if admin has access to this news's city
                    if (!in_array($news->city_id, $accessibleCityIds)) {
                        $errors[] = "No permission to delete news '{$news->name}'";
                        continue;
                    }

                    $newsName = $news->name;

                    // Check if news has related notifications
                    if($news->notifications()->count() > 0){
                        $errors[] = "Cannot delete news '{$newsName}': has related notifications";
                        continue;
                    }

                    // Delete main image if exists
                    if($news->image){
                        Storage::disk('public')->delete($news->image->path);
                        $news->image->delete();
                    }

                    // Delete additional images if exist
                    if($news->images->count() > 0){
                        foreach($news->images as $image){
                            Storage::disk('public')->delete($image->path);
                            $image->delete();
                        }
                    }

                    $news->delete();
                    $deletedItems[] = "news '{$newsName}'";

                } catch (\Exception $e) {
                    $errors[] = "Error deleting news '{$newsName}': " . $e->getMessage();
                }
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
            ->route('news.index')
            ->with(!empty($deletedItems) ? 'status' : 'error', $message ?: 'No items were deleted.');
    }
}