<?php

return [
    /*
     * |--------------------------------------------------------------------------
     * | Third Party Services
     * |--------------------------------------------------------------------------
     * |
     * | This file is for storing the credentials for third party services such
     * | as Mailgun, Postmark, AWS and more. This file provides the de facto
     * | location for this type of information, allowing packages to have
     * | a conventional file to locate the various service credentials.
     * |
     */
    'face_api' => [
        'url' => env('FACE_API_URL', 'http://127.0.0.1:7999'),
        'key' => env('FACE_API_KEY'),
        'connect_timeout' => (int) env('FACE_API_CONNECT_TIMEOUT', 5),
        'timeout' => (int) env('FACE_API_TIMEOUT', 60),
        'login_cooldown_ms' => (int) env('FACE_LOGIN_COOLDOWN_MS', 3000),
        'identity_link_threshold' => (float) env('FACE_IDENTITY_LINK_THRESHOLD', 0.60),
        'selection_ttl_seconds' => (int) env('FACE_LOGIN_SELECTION_TTL', 60),
    ],
    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],
    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],
    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],
    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

   'whatsapp' => [
    'url' => env('WA_GATEWAY_URL'),
    'api_id' => env('WA_GATEWAY_API_ID'),
    'api_key' => env('WA_GATEWAY_API_KEY'),
],
];
