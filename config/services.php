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

    'payment_bridge' => [
        'enabled' => env('PAYMENT_BRIDGE_ENABLED', false),
        'shop_url' => env('SHOP_PAYMENT_URL'),
        'outbound_secret' => env('PAYMENT_BRIDGE_OUTBOUND_SECRET'),
        'inbound_secret' => env('PAYMENT_BRIDGE_INBOUND_SECRET'),
        'time_window' => env('PAYMENT_BRIDGE_TIME_WINDOW', 300),
        'allowed_redirect_hosts' => array_values(array_filter(array_map(
            'trim',
            explode(',', env('PAYMENT_BRIDGE_ALLOWED_REDIRECT_HOSTS', 'secure.payu.com,secure.snd.payu.com'))
        ))),
    ],

];
