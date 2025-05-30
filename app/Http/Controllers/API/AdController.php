<?php

namespace App\Http\Controllers\API;

use App\Http\Resources\API\AdResource;
use App\Models\Ad;
use App\Models\City;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class AdController extends Controller
{
    /**
     * Get all ads for a specific city
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getByCityId(Request $request): JsonResponse
    {
        // Validate the request
        $request->validate([
            'city_id' => 'required|exists:cities,id',
        ]);

        // Get ads for the specified city
        $ads = Ad::where('city_id', $request->city_id)
            ->with('image')
            ->orderBy('created_at', 'desc')
            ->get();

        // Return the ads as a resource collection
        return response()->json([
            'success' => true,
            'data' => AdResource::collection($ads),
        ]);
    }

    /**
     * Get all ads for the current user's city
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getCityAds(Request $request): JsonResponse
    {
        // Get city_id from request (assuming it's sent from the client)
        // In a real app, this might come from the authenticated user's profile
        $cityId = $request->header('X-City-ID') ?? $request->query('city_id');
        
        if (!$cityId) {
            return response()->json([
                'success' => false,
                'message' => 'City ID is required',
            ], 400);
        }

        // Check if city exists
        if (!City::find($cityId)) {
            return response()->json([
                'success' => false,
                'message' => 'City not found',
            ], 404);
        }

        // Get ads for the city
        $ads = Ad::where('city_id', $cityId)
            ->with('image')
            ->orderBy('created_at', 'desc')
            ->get();

        // Return the ads as a resource collection
        return response()->json([
            'success' => true,
            'data' => AdResource::collection($ads),
        ]);
    }
}
