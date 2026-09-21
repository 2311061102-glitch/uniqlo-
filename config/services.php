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
        'bank_bin'     => env('VIETQR_BANK_BIN'),
        'bank_name'    => env('VIETQR_BANK_NAME'),
        'account_no'   => env('VIETQR_ACCOUNT_NO'),
        'account_name' => env('VIETQR_ACCOUNT_NAME'),
    ],
 
    'sepay' => [
        'webhook_api_key' => env('SEPAY_WEBHOOK_API_KEY'),
    ],

    'shipping' => [
        'warehouse_name' => env('SHIPPING_WAREHOUSE_NAME', 'Trường Đại học Tài nguyên và Môi trường Hà Nội (41A Phú Diễn)'),
        'warehouse_latitude' => (float) env('SHIPPING_WAREHOUSE_LATITUDE', 21.0712),
        'warehouse_longitude' => (float) env('SHIPPING_WAREHOUSE_LONGITUDE', 105.7489),
        'free_threshold' => (float) env('SHIPPING_FREE_THRESHOLD', 500000),
    ],
];
