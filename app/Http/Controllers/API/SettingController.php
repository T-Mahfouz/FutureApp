<?php

namespace App\Http\Controllers\API;

use App\Http\Resources\API\CityContactsResource;
use App\Http\Resources\API\SettingResource;
use App\Models\ContactUs;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class SettingController extends InitController
{
    public function __construct()
    {
        parent::__construct();
        $this->pipeline->setModel('Setting');
    }

    /**
     * Get city settings by key (contact_us, about_us, etc.)
     * For contact_us: includes settings + city contacts
     * For other keys: returns settings only
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getCitySetting(Request $request): JsonResponse
    {
        // Validation
        $validator = Validator::make($request->all(), [
            'key' => 'required|string|in:contacts,about_us,privacy_policy,terms_conditions,help,faq',
        ]);

        if ($validator->fails()) {
            return jsonResponse(422, 'Invalid key parameter.', $validator->errors());
        }

        $key = $request->get('key');

        try {
            // Get settings for the specified key and user's city
            $settings = $this->pipeline
                ->where('city_id', $this->user->city_id)
                ->where('key', 'LIKE', $key . '%')
                ->orderBy('key', 'asc')
                ->get();

            // Prepare response data
            $responseData = [
                'key' => $key,
                'city_id' => $this->user->city_id,
                'settings' => SettingResource::collection($settings),
            ];

            # Special handling for contacts
            if ($key === 'contacts') {
                $this->pipeline->setModel('Contact');
                $settings = $this->pipeline->where('city_id', $this->user->city_id)->pluck('value', 'name')->unique();
                
                $responseData = $settings;
            }

            // Check if any settings found
            if ($settings->isEmpty()) {
                return jsonResponse(404, "No {$key} settings found for your city.");
            }

            return jsonResponse(200, 'Settings retrieved successfully.', $responseData);

        } catch (\Exception $e) {
            return jsonResponse(500, 'Failed to retrieve settings. Please try again.'. $e->getMessage());
        }
    }

    /**
     * Get all available setting keys for user's city
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getAvailableKeys(Request $request): JsonResponse
    {
        try {
            $keys = $this->pipeline
                ->where('city_id', $this->user->city_id)
                ->distinct()
                ->pluck('key')
                ->map(function ($key) {
                    // Extract base key (remove suffixes like _phone, _email, etc.)
                    $baseKey = explode('_', $key)[0];
                    if (in_array($baseKey, ['contact', 'about'])) {
                        return $baseKey . '_us';
                    }
                    return $baseKey;
                })
                ->unique()
                ->values();

            return jsonResponse(200, 'Available setting keys retrieved successfully.', [
                'city_id' => $this->user->city_id,
                'available_keys' => $keys,
            ]);

        } catch (\Exception $e) {
            return jsonResponse(500, 'Failed to retrieve available keys. Please try again.');
        }
    }

    /**
     * Get multiple settings by keys
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getMultipleSettings(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'keys' => 'required|array',
            'keys.*' => 'string|in:contact_us,about_us,privacy_policy,terms_conditions,help,faq',
        ]);

        if ($validator->fails()) {
            return jsonResponse(422, 'Invalid keys parameter.', $validator->errors());
        }

        try {
            $keys = $request->get('keys');
            $responseData = [];

            foreach ($keys as $key) {
                $settings = $this->pipeline
                    ->where('city_id', $this->user->city_id)
                    ->where('key', 'LIKE', $key . '%')
                    ->orderBy('key', 'asc')
                    ->get();

                $keyData = [
                    'key' => $key,
                    'settings' => SettingResource::collection($settings),
                ];

                // Add city contacts for contact_us
                if ($key === 'contact_us') {
                    $cityContacts = ContactUs::where('city_id', $this->user->city_id)
                        ->with('user')
                        ->orderBy('created_at', 'desc')
                        ->limit(10)
                        ->get()
                        ->map(function ($contact) {
                            return [
                                'id' => $contact->id,
                                'name' => $contact->name,
                                'phone' => $contact->phone,
                                'message' => $contact->message,
                                'is_read' => $contact->is_read,
                                'user' => $contact->user ? [
                                    'id' => $contact->user->id,
                                    'name' => $contact->user->name,
                                ] : null,
                                'created_at' => $contact->created_at?->format('Y-m-d H:i:s'),
                            ];
                        });

                    $keyData['city_contacts'] = $cityContacts;
                }

                $responseData[] = $keyData;
            }

            return jsonResponse(200, 'Settings retrieved successfully.', [
                'city_id' => $this->user->city_id,
                'data' => $responseData,
            ]);

        } catch (\Exception $e) {
            return jsonResponse(500, 'Failed to retrieve settings. Please try again.');
        }
    }
}