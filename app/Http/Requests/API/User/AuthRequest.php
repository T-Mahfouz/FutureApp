<?php

namespace App\Http\Requests\API\User;

use App\Http\Requests\ShapeRequest;

class AuthRequest extends ShapeRequest
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
            'name' => 'required|min:2',
            'email' => 'required|email|unique:users,email',
            'city_id' => 'nullable|exists:cities,id',
            'password' => 'required|min:6|confirmed',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];
    }
}
