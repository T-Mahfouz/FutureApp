<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'sms' => [
        'url' => env('SMS_API_URL', 'https://smsmisr.com/api/SMS/'),
        'environment' => env('SMS_ENVIRONMENT', '1'),
        'sender' => env('SMS_SENDER'),
        'username' => env('SMS_USERNAME'),
        'password' => env('SMS_PASSWORD'),
    ],

    'beon' => [
        'url' => env('BEON_API_URL', 'https://v3.api.beon.chat/api/v3'),
        'token' => env('BEON_TOKEN'),
    ],

    'whatsapp' => [
        'api_key' => env('WHATSAPP_API_KEY'),
        'source_number' => env('WHATSAPP_SOURCE_NUMBER'),
        'app_name' => env('WHATSAPP_APP_NAME', 'Future'),
    ],

    'firebase' => [
        'project_id' => env('FIREBASE_PROJECT_ID', 'dalel-75ad2'),
        'server_key' => env('FIREBASE_SERVER_KEY'),
        'credentials_file' => env('FIREBASE_CREDENTIALS_FILE', 'client_secret_google.json'),
    ],

];
