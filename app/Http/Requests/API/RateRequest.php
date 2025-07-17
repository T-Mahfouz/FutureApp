<?php

namespace App\Http\Requests\API;

use App\Http\Requests\ShapeRequest;

class RateRequest extends ShapeRequest
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
            'service_id' => 'required|exists:services,id',
            'rate' => 'required|numeric|min:0|max:5',
        ];
    }
}
