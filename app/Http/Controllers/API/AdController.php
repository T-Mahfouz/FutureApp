<?php

namespace App\Http\Controllers\API;

use App\Http\Resources\API\AdResource;
use App\Models\Ad;
use App\Models\City;
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
        $ads = $this->pipeline->where('city_id', $this->user->city_id)
            ->with('image')
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

        // Get ads for the city
        $ads = $this->pipeline->where('city_id', $cityId)
            ->with('image')
            ->orderBy('created_at', 'desc')
            ->get();

        $data = AdResource::collection($ads);

        return jsonResponse(200, 'done.', $data);
    }
}
