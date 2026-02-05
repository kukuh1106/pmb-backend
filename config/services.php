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

    /*
    |--------------------------------------------------------------------------
    | GOWA (Go WhatsApp Web Multi-Device)
    |--------------------------------------------------------------------------
    |
    | Configuration for go-whatsapp-web-multidevice self-hosted WhatsApp API.
    | See: https://github.com/aldinokemal/go-whatsapp-web-multidevice
    |
    */
    'gowa' => [
        'url' => env('GOWA_API_URL', 'http://localhost:3000'),
        'device_id' => env('GOWA_DEVICE_ID', ''),
        'basic_auth_user' => env('GOWA_BASIC_AUTH_USER', ''),
        'basic_auth_password' => env('GOWA_BASIC_AUTH_PASSWORD', ''),
    ],

];
