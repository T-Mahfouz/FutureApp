<?php

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\User;

if (!function_exists('sendOtp')) {
    /**
     * Send OTP via Beon Chat API.
     *
     * @param string $phone  Phone number without country code (e.g. "1001234567")
     * @param string $name   User name (optional)
     * @param string $lang   Language code (e.g. "en", "ar")
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    function sendOtp(string $phone, string $name = '', string $lang = 'en'): array
    {
        // Throttle: allow re-sending only after 1 minute using DB timestamp
        $user = User::where('phone', $phone)->first();

        if (!$user) {
            return [
                'success' => false,
                'message' => 'User not found.',
                'data' => null,
            ];
        }

        if ($user->otp_expires_at && now()->diffInSeconds($user->otp_expires_at, false) > 240) {
            $remainingSeconds = 60 - (300 - now()->diffInSeconds($user->otp_expires_at, false));
            $remainingSeconds = ceil(max($remainingSeconds, 0));

            return [
                'success' => false,
                'message' => "Please wait {$remainingSeconds} seconds before requesting a new OTP.",
                'data' => null,
            ];
        }

        $baseUrl = config('services.beon.url');
        $token = config('services.beon.token');

        if (!$token) {
            Log::error('Beon API token is not configured.');
            return [
                'success' => false,
                'message' => 'SMS service is not configured.',
                'data' => null,
            ];
        }

        try {
            $response = Http::withHeaders([
                'beon-token' => $token,
            ])->asMultipart()->post($baseUrl . '/messages/otp', [
                ['name' => 'phoneNumber', 'contents' => '+2' . $phone],
                ['name' => 'name', 'contents' => $name],
                ['name' => 'type', 'contents' => 'sms'],
                ['name' => 'otp_length', 'contents' => '4'],
                ['name' => 'lang', 'contents' => $lang],
            ]);

            if ($response->successful()) {
                $responseData = $response->json();
                $otpCode = $responseData['otp'] ?? $responseData['data']['otp'] ?? $responseData['code'] ?? null;

                if ($otpCode) {
                    $user->update([
                        'otp_code' => (string) $otpCode,
                        'otp_expires_at' => now()->addSeconds(300),
                    ]);
                }

                return [
                    'success' => true,
                    'message' => 'OTP sent successfully.',
                    'data' => $responseData,
                ];
            }

            Log::warning('Beon OTP API returned non-success response', [
                'phone' => $phone,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to send OTP. Please try again later.',
                'data' => $response->json(),
            ];
        } catch (\Exception $e) {
            Log::error('Beon OTP API exception', [
                'phone' => $phone,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'SMS service is currently unavailable.',
                'data' => null,
            ];
        }
    }
}
