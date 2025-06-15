<?php

namespace App\Http\Resources\API;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
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
            'title' => $this->title,
            'body' => $this->body,
            'image' => $this->when($this->image, function () {
                return getFullImagePath($this);
            }),
            'service_id' => $this->service_id,
            'news_id' => $this->news_id,
            'service' => $this->when($this->service, function () {
                return [
                    'id' => $this->service->id,
                    'name' => $this->service->name,
                ];
            }),
            'news' => $this->when($this->news, function () {
                return [
                    'id' => $this->news->id,
                    'name' => $this->news->name,
                ];
            }),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
