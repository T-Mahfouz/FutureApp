<?php

namespace App\Http\Resources\API;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceResource extends JsonResource
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
            'brief_description' => $this->brief_description,
            'description' => $this->when($request->routeIs('*.show') || $request->has('detailed'), $this->description ?? ''),
            'address' => $this->when($request->routeIs('*.show') || $request->has('detailed'), $this->address ?? ''),
            'lat' => $this->when($request->routeIs('*.show') || $request->has('detailed'), $this->lat ?? ''),
            'lon' => $this->when($request->routeIs('*.show') || $request->has('detailed'), $this->lon ?? ''),
            'website' => $this->when($request->routeIs('*.show') || $request->has('detailed'), $this->website ?? ''),
            'facebook' => $this->when($request->routeIs('*.show') || $request->has('detailed'), $this->facebook ?? ''),
            'instagram' => $this->when($request->routeIs('*.show') || $request->has('detailed'), $this->instagram ?? ''),
            'whatsapp' => $this->when($request->routeIs('*.show') || $request->has('detailed'), $this->whatsapp ?? ''),
            'telegram' => $this->when($request->routeIs('*.show') || $request->has('detailed'), $this->telegram ?? ''),
            'youtube' => $this->when($request->routeIs('*.show') || $request->has('detailed'), $this->youtube ?? ''),
            'video_link' => $this->when($request->routeIs('*.show') || $request->has('detailed'), $this->video_link ?? ''),
            'image' => $this->whenLoaded('image', function () {
                return getFullImagePath($this);
            }),
            'images' => $this->whenLoaded('images', function() {
                return $this->images->map(function($image) {
                    return getFullImagePath($image);
                });
            }),
            'categories' => CategoryResource::collection($this->whenLoaded('categories')),
            'phones' => $this->whenLoaded('phones', function() {
                return $this->phones->map(function($phone) {
                    return [
                        'id' => $phone->id,
                        'phone' => $phone->phone,
                    ];
                });
            }),
            'average_rating' => $this->whenLoaded('rates', function() {
                return round($this->averageRating(), 1);
            }),
            'ratings_count' => $this->whenLoaded('rates', function() {
                return $this->rates->count();
            }),
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
