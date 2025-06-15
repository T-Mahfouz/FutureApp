<?php

namespace App\Http\Controllers\API;

use App\Http\Resources\API\ServiceResource;
use App\Models\Favorite;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class FavoriteController extends InitController
{
    public function __construct()
    {
        parent::__construct();

        $this->pipeline->setModel('Favorite');
    }

    /**
     * Add service to user's favorites
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function addToFavorites(Request $request): JsonResponse
    {
        $serviceId = $request->input('service_id');
        
        if (!$serviceId) {
            return jsonResponse(400, 'Service ID is required.');
        }

        // Check if service exists and is valid in user's city
        $this->pipeline->setModel('Service');
        $service = $this->pipeline->where('id', $serviceId)
            ->where('city_id', $this->user->city_id)
            ->where('valid', 1)
            ->first();

        if (!$service) {
            return jsonResponse(404, 'Service not found or not available in your city.');
        }

        // Check if already in favorites
        $this->pipeline->setModel('Favorite');
        $existingFavorite = $this->pipeline->where('user_id', $this->user->id)
            ->where('service_id', $serviceId)
            ->first();

        if ($existingFavorite) {
            return jsonResponse(409, 'Service is already in your favorites.');
        }

        DB::beginTransaction();
        try {
            // Add to favorites
            $favorite = $this->pipeline->create([
                'user_id' => $this->user->id,
                'service_id' => $serviceId,
            ]);

            DB::commit();
            
            return jsonResponse(201, 'Service added to favorites successfully.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return jsonResponse(500, 'Failed to add service to favorites.');
        }
    }

    /**
     * Remove service from user's favorites
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function removeFromFavorites(Request $request): JsonResponse
    {
        $serviceId = $request->input('service_id');
        
        if (!$serviceId) {
            return jsonResponse(400, 'Service ID is required.');
        }

        $this->pipeline->setModel('Favorite');
        $favorite = $this->pipeline->where('user_id', $this->user->id)
            ->where('service_id', $serviceId)
            ->first();

        if (!$favorite) {
            return jsonResponse(404, 'Service not found in your favorites.');
        }

        DB::beginTransaction();
        try {
            $favorite->delete();
            DB::commit();
            
            return jsonResponse(200, 'Service removed from favorites successfully.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return jsonResponse(500, 'Failed to remove service from favorites.');
        }
    }

    /**
     * Toggle service favorite status
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function toggleFavorite(Request $request): JsonResponse
    {
        $serviceId = $request->input('service_id');
        
        if (!$serviceId) {
            return jsonResponse(400, 'Service ID is required.');
        }

        // Check if service exists and is valid in user's city
        $this->pipeline->setModel('Service');
        $service = $this->pipeline->where('id', $serviceId)
            ->where('city_id', $this->user->city_id)
            ->where('valid', 1)
            ->first();

        if (!$service) {
            return jsonResponse(404, 'Service not found or not available in your city.');
        }

        $this->pipeline->setModel('Favorite');
        $favorite = $this->pipeline->where('user_id', $this->user->id)
            ->where('service_id', $serviceId)
            ->first();

        DB::beginTransaction();
        try {
            if ($favorite) {
                // Remove from favorites
                $favorite->delete();
                $message = 'Service removed from favorites successfully.';
                $isFavorite = false;
            } else {
                // Add to favorites
                $this->pipeline->create([
                    'user_id' => $this->user->id,
                    'service_id' => $serviceId,
                ]);
                $message = 'Service added to favorites successfully.';
                $isFavorite = true;
            }

            DB::commit();
            
            return jsonResponse(200, $message, ['is_favorite' => $isFavorite]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return jsonResponse(500, 'Failed to toggle favorite status.');
        }
    }

    /**
     * Get user's favorite services
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getUserFavorites(Request $request): JsonResponse
    {
        $favorites = $this->pipeline->setModel('Service')
            ->whereHas('favorites', function($query) {
                $query->where('user_id', $this->user->id);
            })
            ->where('city_id', $this->user->city_id)
            ->where('valid', 1)
            ->with(['image', 'categories'])
            ->orderBy('name', 'asc')
            ->get();
        
        $data = ServiceResource::collection($favorites);

        return jsonResponse(200, 'done.', $data);
    }

    /**
     * Check if service is in user's favorites
     * 
     * @param Request $request
     * @param int $serviceId
     * @return JsonResponse
     */
    public function checkFavoriteStatus(Request $request, int $serviceId): JsonResponse
    {
        $isFavorite = $this->pipeline->where('user_id', $this->user->id)
            ->where('service_id', $serviceId)
            ->exists();
        
        return jsonResponse(200, 'done.', ['is_favorite' => $isFavorite]);
    }
}