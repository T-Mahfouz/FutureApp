<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\API\UserServiceRequest;
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
            ->where(function($query) { 
                $query->where('is_request', false) // Admin created services (no approval needed)
                    ->orWhere(function($subQuery) {
                        $subQuery->where('is_request', true)
                                ->whereNotNull('approved_at'); // User requests must be approved
                    });
            })
            ->with('image')
            ->selectRaw('services.*, EXISTS(SELECT 1 FROM favorites WHERE favorites.service_id = services.id AND favorites.user_id = ?) as is_favorite', [$this->user->id])
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
            ->where(function($query) { 
                $query->where('is_request', false) // Admin created services (no approval needed)
                    ->orWhere(function($subQuery) {
                        $subQuery->where('is_request', true)
                                ->whereNotNull('approved_at'); // User requests must be approved
                    });
            })
            ->with(['image', 'categories'])
            ->selectRaw('services.*, EXISTS(SELECT 1 FROM favorites WHERE favorites.service_id = services.id AND favorites.user_id = ?) as is_favorite', [$this->user->id])
            ->orderBy('arrangement_order', 'asc')
            ->orderBy('name', 'asc')
            ->get();
        
        $data = ServiceResource::collection($services);

        return jsonResponse(200, 'Data retrieved successfully', $data);
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
            ->where(function($query) { 
                $query->where('is_request', false) // Admin created services (no approval needed)
                    ->orWhere(function($subQuery) {
                        $subQuery->where('is_request', true)
                                ->whereNotNull('approved_at'); // User requests must be approved
                    });
            })
            ->whereHas('categories', function($query) use ($categoryId) {
                $query->where('category_id', $categoryId);
            })
            ->with(['image', 'categories'])
            ->selectRaw('services.*, EXISTS(SELECT 1 FROM favorites WHERE favorites.service_id = services.id AND favorites.user_id = ?) as is_favorite', [$this->user->id])
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
            ->where(function($query) { 
                $query->where('is_request', false) // Admin created services (no approval needed)
                    ->orWhere(function($subQuery) {
                        $subQuery->where('is_request', true)
                                ->whereNotNull('approved_at'); // User requests must be approved
                    });
            })
            ->with(['image', 'images', 'categories', 'phones', 'rates'])
            ->selectRaw('services.*, EXISTS(SELECT 1 FROM favorites WHERE favorites.service_id = services.id AND favorites.user_id = ?) as is_favorite', [$this->user->id])
            ->find($id);
        
        if (!$service) {
            return jsonResponse(404, 'Service not found.');
            // return $this->notFoundResponse('Item not found');
        }
        
        $data = new ServiceResource($service);

        return jsonResponse(200, 'done.', $data);
        // return $this->successResponse($data, 'Data retrieved successfully');
    }


    /* ================= Requested Services ====================== */

    /**
     * User can request a new service to be added
     * Service will be pending until admin approval
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function requestService(UserServiceRequest $request): JsonResponse // NEW
    {
        $data = [
            'city_id' => $this->user->city_id,
            'user_id' => $this->user->id,
            'name' => $request->name,
            'address' => $request->address,
            'brief_description' => $request->brief_description,
            'description' => $request->description,
            'lat' => $request->lat ?? null,
            'lon' => $request->lon ?? null,
            'website' => $request->website ?? null,
            'facebook' => $request->facebook ?? null,
            'whatsapp' => $request->whatsapp ?? null,
            'instagram' => $request->instagram ?? null,
            'telegram' => $request->telegram ?? null,
            'youtube' => $request->youtube ?? null,
            'video_link' => $request->video_link ?? null,
            'image_id' => $request->image_id ?? null,
            'valid' => false,
            'is_request' => true,
            'requested_at' => now(),
        ];

        if($request->hasFile('image')){
            $image = $request->file('image');

            $media = resizeImage($image, $this->storagePath);
            
            $data['image_id'] = $media->id ?? null;
        }

        $service = $this->pipeline->create($data);

        if($request->hasFile('additional_images')){
            foreach($request->file('additional_images') as $image){
                
                $media = resizeImage($image, $this->storagePath);
            
                $imageId = $media->id ?? null;
                
                if ($imageId) {
                    $service->images()->attach($media->id);
                }
            }
        }
        
        // Attach categories
        $service->categories()->attach($request->category_ids);

        //  Add phone numbers if provided
        if (isset($request->phones)) {
            foreach ($request->phones as $phone) {
                $service->phones()->create(['phone' => $phone]);
            }
        }

        return jsonResponse(201, 'Service request submitted successfully. It will be reviewed by admin.', [
            'service_id' => $service->id,
            'status' => 'pending_review'
        ]);
    }



    /**
     * Get current user's service requests and their status
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getMyServiceRequests(Request $request): JsonResponse // NEW
    {
        $services = $this->pipeline->where('user_id', $this->user->id)
            ->where('is_request', true)
            ->with(['image', 'categories'])
            ->selectRaw('services.*, EXISTS(SELECT 1 FROM favorites WHERE favorites.service_id = services.id AND favorites.user_id = ?) as is_favorite', [$this->user->id])
            ->orderBy('requested_at', 'desc')
            ->get();
        
        $data = ServiceResource::collection($services);

        return jsonResponse(200, 'done.', $data);
    }
}