<?php

namespace App\Http\Resources\API;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'email' => $this->email,
            'phone' => $this->phone,
            'email_verified_at' => $this->email_verified_at?->format('Y-m-d H:i:s'),
            'image' => $this->when($this->image, function () {
                return getFullImagePath($this);
            }),
            'city' => $this->when($this->city, function () {
                return [
                    'id' => $this->city->id,
                    'name' => $this->city->name,
                    'image' => $this->when($this->city->image, function () {
                        return getFullImagePath($this->city);
                    }),
                ];
            }),
            'favorites_count' => $this->when($this->favorites, function () {
                return $this->favorites->count();
            }),
            'rates_count' => $this->when($this->rates, function () {
                return $this->rates->count();
            }),
            'favorite_services' => $this->when($this->favorites, function () {
                return $this->favorites->map(function ($favorite) {
                    return [
                        'id' => $favorite->service->id,
                        'name' => $favorite->service->name,
                        'brief_description' => $favorite->service->brief_description,
                        'image' => $favorite->service->image ? getFullImagePath($favorite->service) : null,
                    ];
                });
            }),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
