<?php

namespace App\Http\Requests\API;

use App\Http\Requests\ShapeRequest;

class UserServiceRequest extends ShapeRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'brief_description' => 'required|string|max:500',
            'description' => 'required|string',
            'lat' => 'nullable|numeric|between:-90,90',
            'lon' => 'nullable|numeric|between:-180,180',
            'website' => 'nullable|url',
            'facebook' => 'nullable|url',
            'whatsapp' => 'nullable|string',
            'instagram' => 'nullable|url',
            'telegram' => 'nullable|url',
            'youtube' => 'nullable|url',
            'video_link' => 'nullable|url',
            'image_id' => 'nullable|exists:media,id',
            'category_ids' => 'required|array',
            'category_ids.*' => 'exists:categories,id',
            'phones' => 'nullable|array',
            'phones.*' => 'string|max:20',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'additional_images' => 'nullable|array',
            'additional_images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ];
    }
}
