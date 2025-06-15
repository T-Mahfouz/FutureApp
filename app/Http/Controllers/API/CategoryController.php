<?php

namespace App\Http\Controllers\API;

use App\Http\Resources\API\CategoryResource;
use App\Http\Resources\API\ServiceResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Request;

// Example: Enhanced CategoryController with caching
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

// Example: Enhanced ServiceController with caching
class ServiceControllerWithCaching extends InitController
{
    /**
     * Get latest services with caching
     * Cache for 30 minutes since services are frequently added
     */
    public function getLatestServices(Request $request)
    {
        $limit = $request->query('limit', 2);
        $cacheKey = "services_latest_city_{$this->user->city_id}_limit_{$limit}";
        
        $services = Cache::remember($cacheKey, 1800, function () use ($limit) {
            return $this->pipeline->where('city_id', $this->user->city_id)
                ->where('valid', 1)
                ->with('image')
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();
        });
        
        $data = ServiceResource::collection($services);
        return jsonResponse(200, 'done.', $data);
    }

    /**
     * Clear service caches
     */
    public static function clearServicesCache($cityId)
    {
        $pattern = "services_*_city_{$cityId}_*";
        
        // Clear all service-related cache keys for this city
        if (config('cache.default') === 'redis') {
            $keys = Redis::keys($pattern);
            if (!empty($keys)) {
                Redis::del($keys);
            }
        } else {
            // For file/database cache, you'd need to track keys differently
            Cache::flush(); // Less efficient but works
        }
    }
}