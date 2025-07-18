<?php

namespace App\Http\Resources\API;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'image' => $this->whenLoaded('image', function () {
                return getFullImagePath($this);
            }),
            'children' => CategoryResource::collection($this->whenLoaded('children')),
            'description' => $this->when($request->has('include_description'), $this->description),
        ];
    }
}
