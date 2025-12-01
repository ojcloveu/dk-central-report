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

    'resend' => [
        'key' => env('RESEND_KEY'),
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

    'dk' => [
        'dk_url' => env('DK_API_URL'),
        'dk_token' => env('DK_API_TOKEN'),
    ],

    /*
    |--------------------------------------------------------------------------
    | MLM Report API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for MLM Report API integration.
    | Includes client credentials, API endpoint, and request timeout settings.
    |
    */
    'mlm' => [
        'client_id' => env('MLM_CLIENT_ID'),
        'client_secret' => env('MLM_CLIENT_SECRET'),
        'api_url' => env('MLM_API_URL'),
        'api_timeout' => env('MLM_API_TIMEOUT', 3000),
    ],
];
