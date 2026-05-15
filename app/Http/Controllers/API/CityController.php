<?php

namespace App\Http\Controllers\API;

use App\Http\Resources\API\CityResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CityController extends InitController
{
    public function __construct()
    {
        parent::__construct();

        $this->pipeline->setModel('City');
    }

    /**
     * Get all cities
     * Returns: id, name
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $cities = $this->pipeline->get();

        $data = CityResource::collection($cities);

        return jsonResponse(200, 'done.', $data);
    }
}
