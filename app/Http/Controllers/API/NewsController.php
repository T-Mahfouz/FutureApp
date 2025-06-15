<?php

namespace App\Http\Controllers\API;

use App\Http\Resources\API\NewsResource;
use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class NewsController extends InitController
{
    public function __construct()
    {
        parent::__construct();

        $this->pipeline->setModel('News');
    }

    /**
     * Get all news in user's city
     * Returns: id, image, name, description
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getCityNews(Request $request): JsonResponse
    {
        $news = $this->pipeline->where('city_id', $this->user->city_id)
            ->with('image')
            ->orderBy('created_at', 'desc')
            ->get();
        
        $data = NewsResource::collection($news);

        return jsonResponse(200, 'done.', $data);
    }

    /**
     * Get paginated news for user's city
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getCityNewsPaginated(Request $request): JsonResponse
    {
        $perPage = $request->query('per_page', 10);
        
        $news = $this->pipeline->where('city_id', $this->user->city_id)
            ->with('image')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
        
        return jsonResponse(200, 'done.', [
            'data' => NewsResource::collection($news->items()),
            'pagination' => [
                'current_page' => $news->currentPage(),
                'last_page' => $news->lastPage(),
                'per_page' => $news->perPage(),
                'total' => $news->total(),
            ]
        ]);
    }

    /**
     * Get single news item
     * 
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function getNewsById(Request $request, int $id): JsonResponse
    {
        $news = $this->pipeline->where('city_id', $this->user->city_id)
            ->with(['image', 'images'])
            ->find($id);
        
        if (!$news) {
            return jsonResponse(404, 'News not found.');
        }
        
        $data = new NewsResource($news);

        return jsonResponse(200, 'done.', $data);
    }
}