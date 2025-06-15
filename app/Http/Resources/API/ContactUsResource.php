<?php

namespace App\Http\Resources\API;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContactUsResource extends JsonResource
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
            'phone' => $this->phone,
            'message' => $this->message,
            'is_read' => $this->is_read,
            'status' => $this->is_read ? 'processed' : 'pending',
            'city' => $this->when($this->city, function () {
                return [
                    'id' => $this->city->id,
                    'name' => $this->city->name,
                    'image' => $this->when($this->city->image, function () {
                        return getFullImagePath($this->city);
                    }),
                ];
            }),
            'user' => $this->when($this->user, function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                ];
            }),
            'is_anonymous' => $this->user_id === null,
            'can_edit' => !$this->user_id === null, // Can only edit unread messages
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
