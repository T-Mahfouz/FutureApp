<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FirebaseHelper
{
    private static $serverKey;

    public static function init()
    {
        self::$serverKey = env('FIREBASE_SERVER_KEY');
    }

    /**
     * Send Firebase notification
     *
     * @param string $title
     * @param string $body
     * @param string|null $image
     * @param array|string $tokens - Array of tokens or single token
     * @param string|null $topic - Firebase topic
     * @param array $data - Additional data to send
     * @return array
     */
    public static function sendNotification($title, $body, $image = null, $tokens = null, $topic = null, $data = [])
    {
        self::init();

        if (!self::$serverKey) {
            return ['success' => false, 'message' => 'Firebase server key not configured'];
        }

        $notification = [
            'title' => $title,
            'body' => $body,
        ];

        if ($image) {
            $notification['image'] = $image;
        }

        $payload = [
            'notification' => $notification,
            'data' => $data,
        ];

        // Determine target (tokens or topic)
        if ($topic) {
            $payload['to'] = '/topics/' . $topic;
        } elseif ($tokens) {
            if (is_array($tokens)) {
                $payload['registration_ids'] = $tokens;
            } else {
                $payload['to'] = $tokens;
            }
        } else {
            return ['success' => false, 'message' => 'No target specified (tokens or topic)'];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'key=' . self::$serverKey,
                'Content-Type' => 'application/json',
            ])->post('https://fcm.googleapis.com/fcm/send', $payload);

            $result = $response->json();

            if ($response->successful()) {
                Log::info('Firebase notification sent successfully', $result);
                return [
                    'success' => true, 
                    'message' => 'Notification sent successfully',
                    'response' => $result
                ];
            } else {
                Log::error('Firebase notification failed', $result);
                return [
                    'success' => false, 
                    'message' => 'Failed to send notification',
                    'response' => $result
                ];
            }
        } catch (\Exception $e) {
            Log::error('Firebase notification exception: ' . $e->getMessage());
            return [
                'success' => false, 
                'message' => 'Exception occurred: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Send notification to specific city topic
     *
     * @param int $cityId
     * @param string $title
     * @param string $body
     * @param string|null $image
     * @param array $data
     * @return array
     */
    public static function sendToCityTopic($cityId, $title, $body, $image = null, $data = [])
    {
        $topic = 'city_' . $cityId;
        return self::sendNotification($title, $body, $image, null, $topic, $data);
    }

    /**
     * Send notification to multiple cities
     *
     * @param array $cityIds
     * @param string $title
     * @param string $body
     * @param string|null $image
     * @param array $data
     * @return array
     */
    public static function sendToMultipleCities($cityIds, $title, $body, $image = null, $data = [])
    {
        $results = [];
        foreach ($cityIds as $cityId) {
            $results[$cityId] = self::sendToCityTopic($cityId, $title, $body, $image, $data);
        }
        return $results;
    }

    /**
     * Send notification to all users (broadcast)
     *
     * @param string $title
     * @param string $body
     * @param string|null $image
     * @param array $data
     * @return array
     */
    public static function sendBroadcast($title, $body, $image = null, $data = [])
    {
        $topic = 'all_users';
        return self::sendNotification($title, $body, $image, null, $topic, $data);
    }
}