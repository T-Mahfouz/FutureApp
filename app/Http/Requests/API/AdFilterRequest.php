<?php

namespace App\Http\Requests\API;

use App\Http\Requests\ShapeRequest;
use App\Models\Category;
use App\Models\Service;

class AdFilterRequest extends ShapeRequest
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
        $userCityId = getUser()->city_id;

        $cityId = $this->input('city_id');
        $categoryId = $this->input('category_id');
        
        return [
            'location' => 'required|in:home,category_profile,service_profile,all_locations',
            'city_id' => ['nullable', 'exists:cities,id', function ($attribute, $value, $fail) use ($userCityId) {
                if ($value != $userCityId) {
                    $fail('You do not belong to this city.');
                }
            }],
            'category_id' => ['nullable', 'exists:categories,id', function ($attribute, $value, $fail) use ($cityId) {
                if ($value && $cityId) {
                    $category = Category::find($value);
                    if ($category && $category->city_id != $cityId) {
                        $fail('The selected category must belong to the selected city.');
                    }
                }
            }],
            'service_id' => ['nullable', 'exists:services,id', function ($attribute, $value, $fail) use ($cityId, $categoryId) {
                if ($value && $cityId) {
                    $service = Service::find($value);
                    if ($service && $service->city_id != $cityId) {
                        $fail('The selected service must belong to the selected city.');
                    }
                    if ($categoryId) {
                        $serviceInCategory = Service::where('id', $value)
                            ->whereHas('categories', function($q) use ($categoryId) {
                                $q->where('categories.id', $categoryId);
                            })
                            ->exists();
                        if (!$serviceInCategory) {
                            $fail('The selected service must belong to the selected category.');
                        }
                    }
                }
            }],
        ];
    }
}
