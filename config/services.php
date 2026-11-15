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

    'vietqr' => [
        'bank_id' => env('BANK_ID', 'VCB'),
        'bank_name' => env('BANK_NAME', 'Vietcombank (VCB) - CN TP.HCM'),
        'bank_account' => env('BANK_ACCOUNT', '1029384756'),
        'bank_account_name' => env('BANK_ACCOUNT_NAME', 'SHINE BEAUTY COSMETICS'),
        'qr_template' => env('QR_TEMPLATE', 'compact2'),
        'payment_timeout_minutes' => (int) env('PAYMENT_TIMEOUT_MINUTES', 15),
    ],

    'sepay' => [
        'webhook_token' => env('SEPAY_WEBHOOK_TOKEN', 'sepay_secret_webhook_token_2026'),
        'api_token' => env('SEPAY_API_TOKEN', ''),
        'match_prefix' => env('SEPAY_MATCH_PREFIX', 'DH'),
    ],

];
