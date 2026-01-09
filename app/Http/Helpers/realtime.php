<?php

use App\Models\CityConfig;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

/**
 * Send FCM Push Notification to Mobile App (Real-time)
 *
 * @param int $cityID
 * @param string $title
 * @param string $body
 * @param string $type
 * @param array $extra
 * @return array|null
 */

if (!function_exists('getConfig')) {
    function getConfig($cityID) {
        return CityConfig::where('city_id',$cityID)->first();
    }
}

if (!function_exists(function: 'testAuth')) {
    function testAuth() {
        $validation = validateFirebaseCredentials();
        dd($validation);
    }
}

function FCMPush($cityID, $title, $body, $type, $extra = [])
{
    $projectId = 'dalel-75ad2';

    $config = getConfig($cityID);
    if (!$config) return null;

    if (empty($config->firebase_topic)) {
        // No target -> nothing to send
        return null;
    }

    $url = "https://fcm.googleapis.com/v1/projects/$projectId/messages:send";

    // IMPORTANT: this must be a SERVICE ACCOUNT json, not an OAuth client secret json
    $serviceAccountPath = Storage::disk('local')->path('client_secret_google.json');

    $scopes = ['https://www.googleapis.com/auth/firebase.messaging'];
    $credentials = new ServiceAccountCredentials($scopes, $serviceAccountPath);

    $token = $credentials->fetchAuthToken();
    $accessToken = $token['access_token'] ?? null;
    if (!$accessToken) {
        throw new \RuntimeException("Failed to fetch Google access token for FCM.");
    }

    // Ensure all data values are strings
    $data = [
        "type"       => (string) $type,
        "title"      => (string) $title,
        "body"       => (string) $body,
        "image"      => isset($extra['image']) ? (string) $extra['image'] : "",
        "service_id" => isset($extra['service_id']) ? (string) $extra['service_id'] : "0",
    ];

    // Add the rest of $extra as strings (optional)
    foreach ($extra as $k => $v) {
        $data[$k] = is_scalar($v) ? (string) $v : json_encode($v);
    }
    
    /* $fields = [
        "message" => [
            "topic" => $config->firebase_topic,
            "notification" => [
                "title" => (string) $title,
                "body"  => (string) $body,
                "image" => isset($extra['image']) ? (string) $extra['image'] : null,
            ],
            "data" => $data,
            "android" => [
                "priority" => "HIGH",
            ],
        ],
    ]; */
    $fields = [
        "message" => [
            "topic" => $config->firebase_topic ?? 'future_app',
            // Common block (applies to all platforms unless overridden)
            "notification" => [
                "title" => (string) $title,
                "body"  => (string) $body,
                "image" => isset($extra['image']) ? (string) $extra['image'] : null,
            ],
            "data" => $data, // make sure all values are strings
            // Android-specific
            "android" => [
                "priority" => "HIGH",
            ],
            // iOS-specific (APNs)
            "apns" => [
                "headers" => [
                    // Visible notification
                    "apns-push-type" => "alert",
                    // 10 = high priority (immediate)
                    "apns-priority"  => "10",
                ],
                "payload" => [
                    "aps" => [
                        "alert" => [
                            "title" => (string) $title,
                            "body"  => (string) $body,
                        ],
                        "sound" => "default",
                        // "badge" => 1, // optional
                        // If you need Notification Service Extension (e.g., rich media), set this:
                        "mutable-content" => 1,
                    ],
                ],
                "fcm_options" => [
                    // helps you filter reports + (sometimes) rich media handling depending on client setup
                    "analytics_label" => "future_app",
                    // If you use images on iOS with NSE, include it here too:
                    "image" => isset($extra['image']) ? (string) $extra['image'] : null,
                ],
            ],
        ],
    ];

    $headers = [
        'Authorization: Bearer ' . $accessToken,
        'Content-Type: application/json; charset=UTF-8',
    ];

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POSTFIELDS => json_encode($fields, JSON_UNESCAPED_UNICODE),
        // Don't disable SSL verification in production
    ]);
    dd($fields);
    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($result === false) {
        throw new \RuntimeException('cURL failed: ' . curl_error($ch));
    }

    curl_close($ch);

    if ($httpCode >= 400) {
        // FCM returns useful JSON errors here
        throw new \RuntimeException("FCM error ($httpCode): " . $result);
    }
    /* if ($httpCode === 200) {
        $decoded = json_decode($result, true);
        $messageId = null;

        if (!empty($decoded['name']) && preg_match('~/messages/(\d+)$~', $decoded['name'], $m)) {
            $messageId = $m[1];
        }
    } */
    $response = json_decode($result, true);

    return [
        'success' => $httpCode === 200,
        'http_code' => $httpCode,
        'response' => $response,
        'message_id' => $response['name'] ?? null,
    ];
}


function FCMPushNew($cityID, $title, $body, $type, $extra = [])
{
    $config = getConfig($cityID);

    if (!$config) {
        Log::error('FCM Push: Config not found', ['cityID' => $cityID]);
        return null;
    }

    if (!$config->firebase_topic) {
        Log::error('FCM Push: Firebase topic not configured', ['cityID' => $cityID]);
        return null;
    }

    // Firebase Project ID - update this or store in config
    // $projectId = $config->firebase_project_id ?? 'dalel-75ad2';
    $projectId = 'dalel-75ad2';
    $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

    try {
        // Get Access Token (cached for performance)
        $accessToken = getFirebaseAccessToken();

        if (!$accessToken) {
            Log::error('FCM Push: Failed to obtain access token');
            return null;
        }

        // Build the message payload
        $message = buildFCMMessage($config->firebase_topic, $title, $body, $type, $extra);

        // Send the notification
        $response = sendFCMRequest($url, $accessToken, $message);

        Log::info('FCM Push notification sent', [
            'cityID' => $cityID,
            'topic' => $config->firebase_topic,
            'title' => $title,
            'type' => $type,
            'success' => $response['success'] ?? false,
        ]);

        return $response;

    } catch (\Exception $e) {
        Log::error('FCM Push Exception', [
            'cityID' => $cityID,
            'title' => $title,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
        return null;
    }
}

/**
 * Get Firebase Access Token using Service Account Credentials
 * Token is cached for 50 minutes (expires in 60 minutes)
 *
 * @return string|null
 * @throws \Exception
 */
function getFirebaseAccessToken()
{
    // Cache the token to avoid fetching on every request
    return Cache::remember('firebase_access_token', 3000, function () {
        $credentialsPath = Storage::path('client_secret_google.json');

        if (!file_exists($credentialsPath)) {
            throw new \Exception('Firebase credentials file not found at: ' . $credentialsPath);
        }

        $scopes = ['https://www.googleapis.com/auth/firebase.messaging'];
        $credentials = new ServiceAccountCredentials($scopes, $credentialsPath);

        $token = $credentials->fetchAuthToken();

        if (!isset($token['access_token'])) {
            throw new \Exception('Failed to fetch Firebase access token');
        }

        return $token['access_token'];
    });
}

/**
 * Build FCM Message Payload optimized for Mobile Apps (Android & iOS)
 *
 * @param string $topic
 * @param string $title
 * @param string $body
 * @param string $type
 * @param array $extra
 * @return array
 */
function buildFCMMessage($topic, $title, $body, $type, $extra = [])
{
    // Prepare data payload - all values must be strings for FCM
    $dataPayload = [
        'title' => (string) $title,
        'body' => (string) $body,
        'type' => (string) $type,
        'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
        'timestamp' => (string) now()->timestamp,
        'notification_id' => (string) uniqid('notif_'),
    ];

    // Add institute_id if provided
    if (isset($extra['institute_id'])) {
        $dataPayload['institute_id'] = (string) $extra['institute_id'];
    }

    // Add all extra data (convert to strings)
    foreach ($extra as $key => $value) {
        if ($key === 'image') continue; // Handle image separately
        $dataPayload[$key] = is_array($value) ? json_encode($value) : (string) $value;
    }

    // Base message structure
    $message = [
        'message' => [
            'topic' => $topic,

            // Notification payload - displays in system tray
            'notification' => [
                'title' => (string) $title,
                'body' => (string) $body,
            ],

            // Data payload - always delivered to app
            'data' => $dataPayload,

            // Android specific configuration for real-time delivery
            'android' => [
                'priority' => 'high',           // ← Correct location
                'ttl' => '0s',
                'notification' => [
                    'channel_id' => 'high_importance_channel',
                    'sound' => 'default',
                    'default_vibrate_timings' => true,
                    'default_light_settings' => true,
                    'notification_count' => 1,
                ],
            ],
            

            // iOS (APNs) specific configuration for real-time delivery
            'apns' => [
                'headers' => [
                    'apns-priority' => '10',
                    'apns-push-type' => 'alert',
                    'apns-expiration' => '0',
                ],
                'payload' => [
                    'aps' => [
                        'alert' => [
                            'title' => (string) $title,
                            'body' => (string) $body,
                        ],
                        'sound' => 'default',
                        'badge' => 1,
                        'content-available' => 1,
                        'mutable-content' => 1,
                    ],
                ],
            ],
        ],
    ];

    // Add image if provided
    if (!empty($extra['image'])) {
        $imageUrl = (string) $extra['image'];
        $message['message']['notification']['image'] = $imageUrl;
        $message['message']['android']['notification']['image'] = $imageUrl;
        $message['message']['apns']['payload']['aps']['mutable-content'] = 1;
        $message['message']['apns']['fcm_options']['image'] = $imageUrl;
        $dataPayload['image'] = $imageUrl;
        $message['message']['data'] = $dataPayload;
    }
    
    return $message;
}

/**
 * Send FCM HTTP Request
 *
 * @param string $url
 * @param string $accessToken
 * @param array $message
 * @return array
 * @throws \Exception
 */
function sendFCMRequest($url, $accessToken, $message)
{
    $headers = [
        'Authorization: Bearer ' . $accessToken,
        'Content-Type: application/json',
    ];

    $ch = curl_init();

    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_POSTFIELDS => json_encode($message),
        CURLOPT_TIMEOUT => 30,
        CURLOPT_CONNECTTIMEOUT => 10,
    ]);

    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);

    curl_close($ch);

    if ($result === false) {
        throw new \Exception('cURL request failed: ' . $curlError);
    }

    $response = json_decode($result, true);

    // Log error responses
    if ($httpCode !== 200) {
        Log::error('FCM API Error Response', [
            'http_code' => $httpCode,
            'response' => $response,
            'message_topic' => $message['message']['topic'] ?? 'unknown',
        ]);
    }

    return [
        'success' => $httpCode === 200,
        'http_code' => $httpCode,
        'response' => $response,
        'message_id' => $response['name'] ?? null,
    ];
}

/**
 * Send FCM Push to specific device token instead of topic
 *
 * @param string $deviceToken
 * @param string $title
 * @param string $body
 * @param string $type
 * @param array $extra
 * @param string|null $projectId
 * @return array|null
 */
function FCMPushToDevice($deviceToken, $title, $body, $type, $extra = [], $projectId = null)
{
    $projectId = $projectId ?? config('services.firebase.project_id', 'dalel-75ad2');
    $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

    try {
        $accessToken = getFirebaseAccessToken();

        if (!$accessToken) {
            Log::error('FCM Push to Device: Failed to obtain access token');
            return null;
        }

        // Build message for device token
        $message = buildFCMMessageForDevice($deviceToken, $title, $body, $type, $extra);

        $response = sendFCMRequest($url, $accessToken, $message);

        Log::info('FCM Push to device sent', [
            'device_token' => substr($deviceToken, 0, 20) . '...',
            'title' => $title,
            'success' => $response['success'] ?? false,
        ]);

        return $response;

    } catch (\Exception $e) {
        Log::error('FCM Push to Device Exception', [
            'error' => $e->getMessage(),
        ]);
        return null;
    }
}

/**
 * Build FCM Message for specific device token
 *
 * @param string $deviceToken
 * @param string $title
 * @param string $body
 * @param string $type
 * @param array $extra
 * @return array
 */
function buildFCMMessageForDevice($deviceToken, $title, $body, $type, $extra = [])
{
    $message = buildFCMMessage('', $title, $body, $type, $extra);

    // Replace topic with token
    unset($message['message']['topic']);
    $message['message']['token'] = $deviceToken;

    return $message;
}

/**
 * Send FCM Push to multiple device tokens
 *
 * @param array $deviceTokens
 * @param string $title
 * @param string $body
 * @param string $type
 * @param array $extra
 * @return array
 */
function FCMPushToMultipleDevices($deviceTokens, $title, $body, $type, $extra = [])
{
    $results = [
        'success_count' => 0,
        'failure_count' => 0,
        'responses' => [],
    ];

    foreach ($deviceTokens as $token) {
        $response = FCMPushToDevice($token, $title, $body, $type, $extra);

        if ($response && $response['success']) {
            $results['success_count']++;
        } else {
            $results['failure_count']++;
        }

        $results['responses'][] = [
            'token' => substr($token, 0, 20) . '...',
            'success' => $response['success'] ?? false,
        ];
    }

    return $results;
}

/**
 * Clear cached Firebase access token
 * Useful when token needs to be refreshed
 *
 * @return bool
 */
function clearFirebaseTokenCache()
{
    return Cache::forget('firebase_access_token');
}

/**
 * Validate Firebase credentials file exists and is readable
 *
 * @return array
 */
function validateFirebaseCredentials()
{
    $credentialsPath = Storage::path('client_secret_google.json');

    $result = [
        'valid' => false,
        'path' => $credentialsPath,
        'exists' => false,
        'readable' => false,
        'project_id' => null,
        'errors' => [],
    ];

    if (!file_exists($credentialsPath)) {
        $result['errors'][] = 'Credentials file does not exist';
        return $result;
    }

    $result['exists'] = true;

    if (!is_readable($credentialsPath)) {
        $result['errors'][] = 'Credentials file is not readable';
        return $result;
    }

    $result['readable'] = true;

    $contents = file_get_contents($credentialsPath);
    $credentials = json_decode($contents, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        $result['errors'][] = 'Invalid JSON in credentials file';
        return $result;
    }

    if (!isset($credentials['project_id'])) {
        $result['errors'][] = 'Missing project_id in credentials';
        return $result;
    }

    $result['project_id'] = $credentials['project_id'];
    $result['valid'] = true;

    return $result;
}