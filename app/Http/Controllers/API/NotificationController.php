<?php

namespace App\Http\Controllers\API;

use App\Http\Resources\API\NotificationResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class NotificationController extends InitController
{
    public function __construct()
    {
        parent::__construct();
        $this->pipeline->setModel('Notification');
    }

    /**
     * Get notifications in user's city
     * Returns: id, title, body, image, service_id, news_id
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getCityNotifications(Request $request): JsonResponse
    {
        $limit = $request->query('limit', 20);
        $page = $request->query('page', 1);
        $offset = ($page - 1) * $limit;

        // Get notifications that belong to services or news in user's city
        $notifications = $this->pipeline
            ->with(['image', 'service', 'news'])
            ->where(function($query) {
                $query->whereHas('service', function($subQuery) {
                    $subQuery->where('city_id', $this->user->city_id);
                })
                ->orWhereHas('news', function($subQuery) {
                    $subQuery->where('city_id', $this->user->city_id);
                });
            })
            ->orderBy('created_at', 'desc')
            ->offset($offset)
            ->limit($limit)
            ->get();

        $data = NotificationResource::collection($notifications);

        return jsonResponse(200, 'done.', $data);
    }

    /**
     * Get latest notifications in user's city
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getLatestNotifications(Request $request): JsonResponse
    {
        $limit = $request->query('limit', 5);

        $notifications = $this->pipeline
            ->with(['image', 'service', 'news'])
            ->where(function($query) {
                $query->whereHas('service', function($subQuery) {
                    $subQuery->where('city_id', $this->user->city_id);
                })
                ->orWhereHas('news', function($subQuery) {
                    $subQuery->where('city_id', $this->user->city_id);
                });
            })
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        $data = NotificationResource::collection($notifications);

        return jsonResponse(200, 'done.', $data);
    }

    /**
     * Get notification by ID (if it belongs to user's city)
     * 
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function getNotificationById(Request $request, int $id): JsonResponse
    {
        $notification = $this->pipeline
            ->with(['image', 'service', 'news'])
            ->where(function($query) {
                $query->whereHas('service', function($subQuery) {
                    $subQuery->where('city_id', $this->user->city_id);
                })
                ->orWhereHas('news', function($subQuery) {
                    $subQuery->where('city_id', $this->user->city_id);
                });
            })
            ->find($id);

        if (!$notification) {
            return jsonResponse(404, 'Notification not found.');
        }

        $data = new NotificationResource($notification);

        return jsonResponse(200, 'done.', $data);
    }
}