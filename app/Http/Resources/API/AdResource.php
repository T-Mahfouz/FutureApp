<?php

namespace App\Http\Resources\API;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'location' => $this->location,
            'link' => $this->link,
            'expiration_date' => $this->expiration_date != null ? $this->expiration_date->format('M d, Y g:i A') : '',
            'city' => $this->whenLoaded('city', function () {
                return [
                    'id' => $this->city_id,
                    'name' => $this->city->name];
            }),
            'category' => $this->category_id ? [
                'id' => $this->category_id,
                'name' => $this->category->name
            ] : null,
            'service' => $this->service_id ? [
                'id' => $this->service_id,
                'name' => $this->service->name
            ] : null,
            'image' => $this->whenLoaded('image', function () {
                return getFullImagePath($this);
            }),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
