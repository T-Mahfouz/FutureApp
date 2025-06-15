<?php

namespace App\Http\Controllers\API;

use App\Http\Resources\API\ServiceResource;
use App\Models\Service;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ServiceController extends InitController
{
    use ApiResponse;

    public function __construct()
    {
        parent::__construct();

        $this->pipeline->setModel('Service');
    }

    /**
     * Get last added valid services in user's city
     * Returns: id, image, name, description
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getLatestServices(Request $request): JsonResponse
    {
        $limit = $request->query('limit', 2);
        
        $services = $this->pipeline->where('city_id', $this->user->city_id)
            ->where('valid', 1)
            ->with('image')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
        
        $data = ServiceResource::collection($services);

        return jsonResponse(200, 'done.', $data);
        // return $this->successResponse($data, 'Data retrieved successfully');
    }

    /**
     * Get all valid services in user's city
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getCityServices(Request $request): JsonResponse
    {
        $services = $this->pipeline->where('city_id', $this->user->city_id)
            ->where('valid', 1)
            ->with(['image', 'categories'])
            ->orderBy('arrangement_order', 'asc')
            ->orderBy('name', 'asc')
            ->get();
        
        $data = ServiceResource::collection($services);

        return jsonResponse(200, 'done.', $data);
        // return $this->successResponse($data, 'Data retrieved successfully');
    }

    /**
     * Get services by category
     * 
     * @param Request $request
     * @param int $categoryId
     * @return JsonResponse
     */
    public function getServicesByCategory(Request $request, int $categoryId): JsonResponse
    {
        $services = $this->pipeline->where('city_id', $this->user->city_id)
            ->where('valid', 1)
            ->whereHas('categories', function($query) use ($categoryId) {
                $query->where('category_id', $categoryId);
            })
            ->with(['image', 'categories'])
            ->orderBy('arrangement_order', 'asc')
            ->orderBy('name', 'asc')
            ->get();
        
        $data = ServiceResource::collection($services);

        return jsonResponse(200, 'done.', $data);
        // return $this->successResponse($data, 'Data retrieved successfully');
    }

    /**
     * Get single service details
     * 
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function getServiceById(Request $request, int $id): JsonResponse
    {
        $service = $this->pipeline->where('city_id', $this->user->city_id)
            ->where('valid', 1)
            ->with(['image', 'images', 'categories', 'phones', 'rates'])
            ->find($id);
        
        if (!$service) {
            return jsonResponse(404, 'Service not found.');
            // return $this->notFoundResponse('Item not found');
        }
        
        $data = new ServiceResource($service);

        return jsonResponse(200, 'done.', $data);
        // return $this->successResponse($data, 'Data retrieved successfully');
    }
}