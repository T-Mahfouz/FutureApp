<?php

namespace App\Http\Resources\API;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NewsResource extends JsonResource
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
            'description' => $this->description,
            'image' => $this->whenLoaded('image', function () {
                return getFullImagePath($this);
            }),
            'images' => $this->whenLoaded('images', function() {
                return $this->images->map(function($image) {
                    return getFullImagePath($image);
                });
            }),
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
