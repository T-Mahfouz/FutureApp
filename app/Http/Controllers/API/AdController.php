<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\API\AdFilterRequest;
use App\Http\Resources\API\AdResource;
use App\Models\Ad;
use App\Models\Category;
use App\Models\City;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AdController extends InitController
{
    public function __construct()
    {
        parent::__construct();

        $this->pipeline->setModel('Ad');
    }
    
    /**
     * Get all ads for the current user's city
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getByCityId(Request $request): JsonResponse
    {
        $cityId = $this->user->city_id;
        $location = $request->query('location');

        $query = $this->pipeline->where('city_id', $cityId);

        $query = $query->where(function($q) {
            $q->whereNull('expiration_date')
              ->orWhere('expiration_date', '>', now());
        });

        if ($location) {
           $query = $query->where('location', $location);
        }

        $ads = $query->with('image')
            ->orderBy('created_at', 'desc')
            ->get();

        $data = AdResource::collection($ads);

        return jsonResponse(200, 'done.', $data);
    }

    /**
     * Get all ads for a specific city
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getCityAds(Request $request): JsonResponse
    {
        $cityId = $request->header('X-City-ID') ?? $request->query('city_id');
        $location = $request->query('location');

        if (!$cityId) {
            return response()->json([
                'success' => false,
                'message' => 'City ID is required',
            ], 400);
        }

        $this->pipeline->setModel('City');

        // Check if city exists
        if (!$this->pipeline->find($cityId)) {
            return response()->json([
                'success' => false,
                'message' => 'City not found',
            ], 404);
        }

        $this->pipeline->setModel('Ad');

        $query = $this->pipeline->where('city_id', $cityId);

        $query = $query->where(function($q) {
            $q->whereNull('expiration_date')
              ->orWhere('expiration_date', '>', now());
        });

        if ($location) {
           $query = $query->where('location', $location);
        }

        $ads = $query->with('image')
            ->orderBy('created_at', 'desc')
            ->get();

        $data = AdResource::collection($ads);

        return jsonResponse(200, 'done.', $data);
    }

    /**
     * Get ads by user's city and location type
     * Returns: id, image, link
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getAdsWithFilter(AdFilterRequest $request): JsonResponse
    {
        $location = $request->query('location');
        $cityId = $request->query('city_id');
        $categoryId = $request->query('category_id');
        $serviceId = $request->query('service_id');
        
        if (!$location) {
            return jsonResponse(400, 'Location parameter is required.');
        }

        $query = $this->pipeline->where('city_id', $this->user->city_id)
            ->where('location', $location)
            ->where(function($q) {
                $q->whereNull('expiration_date')
                  ->orWhere('expiration_date', '>', now());
            });
            
        if ($cityId) {
            $query = $query->where('city_id', $cityId);
        }    
        if ($categoryId) {
            $query = $query->where('category_id', $categoryId);
        }    
        if ($serviceId) {
            $query = $query->where('service_id', $serviceId);
        }
        if ($location) {
            $query = $query->where('location', $location);
        }
            

        $ads = $query->with('image')->orderBy('created_at', 'desc')->get();

        $data = AdResource::collection($ads);

        return jsonResponse(200, 'done.', $data);
    }
}