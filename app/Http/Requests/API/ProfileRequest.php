<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;

class ProfileRequest extends FormRequest
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
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:users,email,' . getUser()->id,
            'phone' => 'sometimes|required|string|max:20|unique:users,phone,' . getUser()->id,
            'city_id' => 'sometimes|required|exists:cities,id',
            'image' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
            'password' => 'sometimes|required|string|min:6|confirmed',
        ];
    }
}
