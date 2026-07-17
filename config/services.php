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
        'enabled' => (bool) env('PAYMENT_BRIDGE_ENABLED', false),
        'inbound_secret' => env('PAYMENT_BRIDGE_INBOUND_SECRET'),
        'outbound_secret' => env('PAYMENT_BRIDGE_OUTBOUND_SECRET'),
        'time_window' => (int) env('PAYMENT_BRIDGE_TIME_WINDOW', 300),
        'com_payment_url' => env('COM_PAYMENT_URL'),
        'allowed_return_hosts' => array_values(array_filter(array_map(
            'trim',
            explode(',', (string) env('PAYMENT_BRIDGE_ALLOWED_RETURN_HOSTS', 'xostore.com,www.xostore.com'))
        ))),
        'reconciliation_enabled' => (bool) env('PAYMENT_BRIDGE_RECONCILIATION_ENABLED', false),
        'reconcile_after_minutes' => (int) env('PAYMENT_BRIDGE_RECONCILE_AFTER_MINUTES', 15),
    ],

    'payu' => [
        'environment' => env('PAYU_ENVIRONMENT', 'sandbox'),
        'pos_id' => env('PAYU_POS_ID'),
        'client_id' => env('PAYU_CLIENT_ID'),
        'client_secret' => env('PAYU_CLIENT_SECRET'),
        'second_key' => env('PAYU_SECOND_KEY'),
        'redirect_hosts' => array_values(array_filter(array_map(
            'trim',
            explode(',', (string) env('PAYU_REDIRECT_HOSTS', 'secure.snd.payu.com,secure.payu.com'))
        ))),
    ],

];
