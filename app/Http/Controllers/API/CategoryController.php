<?php

namespace App\Http\Controllers\API;

use App\Http\Resources\API\CategoryResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CategoryController extends InitController
{
    public function __construct()
    {
        parent::__construct();
        $this->pipeline->setModel('Category');
    }

    /**
     * Get all active categories by user's city with caching
     * Cache for 1 hour, invalidate when categories are updated
     * 
     * @param Request $request
     */
    public function getActiveCategories(Request $request)
    {
        $cacheKey = "categories_active_city_{$this->user->city_id}";
        
        $categories = Cache::remember($cacheKey, 3600, function () {
            return $this->pipeline->where('city_id', $this->user->city_id)
                ->where('active', 1)
                ->with('image')
                ->orderBy('name', 'asc')
                ->get();
        });
        
        $data = CategoryResource::collection($categories);
        return jsonResponse(200, 'done.', $data);
    }


    /**
     * Get active categories with their children
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getActiveCategoriesWithChildren(Request $request): JsonResponse
    {
        $cacheKey = "categories_active_with_subs_{$this->user->city_id}";

        $categories = Cache::remember($cacheKey, 3600, function() {
            return $this->pipeline->where('city_id', $this->user->city_id)
                ->where('active', 1)
                ->whereNull('parent_id') // Get only parent categories
                ->with(['image', 'children' => function($query) {
                    $query->where('active', 1)->with('image');
                }])
                ->orderBy('name', 'asc')
                ->get();
        });
        
        $data = CategoryResource::collection($categories);

        return jsonResponse(200, 'done.', $data);
    }

    
    /**
     * Clear cache when categories are updated
     * Call this method in admin panel when categories are modified
     */
    public static function clearCategoriesCache($cityId)
    {
        Cache::forget("categories_active_city_{$cityId}");
        Cache::forget("categories_with_children_city_{$cityId}");
    }
}