<?php

namespace App\Http\Resources\API;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'is_verified' => (bool) $this->is_verified,
            'city' => [
                'id' => $this->city_id,
                'name' => $this->city->name
            ],
            'image' => getFullImagePath($this),
            'access_token' => $this->when($this->access_token, $this->access_token)
        ];
    }
}
