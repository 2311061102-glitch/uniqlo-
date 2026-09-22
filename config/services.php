<?php

return [

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

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI', env('APP_URL').'/dang-nhap/google/callback'),
    ],

    'vietqr' => [
        'bank_bin' => env('VIETQR_BANK_BIN'),
        'account_no' => env('VIETQR_ACCOUNT_NO'),
        'account_name' => env('VIETQR_ACCOUNT_NAME'),
    ],

    'vnpay' => [
        'tmn_code' => env('VNPAY_TMN_CODE'),
        'hash_secret' => env('VNPAY_HASH_SECRET'),
        'payment_url' => env('VNPAY_PAYMENT_URL', 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html'),
        'return_url' => env('VNPAY_RETURN_URL', env('APP_URL').'/thanh-toan/vnpay/return'),
        'ipn_url' => env('VNPAY_IPN_URL', env('APP_URL').'/thanh-toan/vnpay/ipn'),
    ],

    'sepay' => [
        'webhook_api_key' => env('SEPAY_WEBHOOK_API_KEY'),
    ],

    'shipping' => [
        'warehouse_name' => env('SHIPPING_WAREHOUSE_NAME', 'Hệ thống cửa hàng'),
        'warehouse_latitude' => (float) env('SHIPPING_WAREHOUSE_LATITUDE', 21.0712),
        'warehouse_longitude' => (float) env('SHIPPING_WAREHOUSE_LONGITUDE', 105.7489),
        'free_threshold' => (float) env('SHIPPING_FREE_THRESHOLD', 500000),
        'branches' => [
            ['code' => 'HN01', 'name' => 'UNIS Cầu Giấy', 'address' => 'Cầu Giấy, Hà Nội', 'latitude' => 21.0285, 'longitude' => 105.8542],
            ['code' => 'HN02', 'name' => 'UNIS Hà Đông', 'address' => 'Hà Đông, Hà Nội', 'latitude' => 20.9711, 'longitude' => 105.7788],
            ['code' => 'HP01', 'name' => 'UNIS Hải Phòng', 'address' => 'Lê Chân, Hải Phòng', 'latitude' => 20.8449, 'longitude' => 106.6881],
            ['code' => 'QN01', 'name' => 'UNIS Hạ Long', 'address' => 'Hạ Long, Quảng Ninh', 'latitude' => 20.9710, 'longitude' => 107.0448],
            ['code' => 'BN01', 'name' => 'UNIS Bắc Ninh', 'address' => 'TP. Bắc Ninh, Bắc Ninh', 'latitude' => 21.1861, 'longitude' => 106.0763],
            ['code' => 'TN01', 'name' => 'UNIS Thái Nguyên', 'address' => 'TP. Thái Nguyên, Thái Nguyên', 'latitude' => 21.5944, 'longitude' => 105.8482],
            ['code' => 'HD01', 'name' => 'UNIS Hải Dương', 'address' => 'TP. Hải Dương, Hải Dương', 'latitude' => 20.9373, 'longitude' => 106.3146],
            ['code' => 'ND01', 'name' => 'UNIS Nam Định', 'address' => 'TP. Nam Định, Nam Định', 'latitude' => 20.4388, 'longitude' => 106.1621],
            ['code' => 'TH01', 'name' => 'UNIS Thanh Hóa', 'address' => 'TP. Thanh Hóa, Thanh Hóa', 'latitude' => 19.8067, 'longitude' => 105.7852],
            ['code' => 'NA01', 'name' => 'UNIS Vinh', 'address' => 'TP. Vinh, Nghệ An', 'latitude' => 18.6796, 'longitude' => 105.6813],
            ['code' => 'LS01', 'name' => 'UNIS Lạng Sơn', 'address' => 'TP. Lạng Sơn, Lạng Sơn', 'latitude' => 21.8537, 'longitude' => 106.7610],
            ['code' => 'LC01', 'name' => 'UNIS Lào Cai', 'address' => 'TP. Lào Cai, Lào Cai', 'latitude' => 22.4856, 'longitude' => 103.9707],
            ['code' => 'YB01', 'name' => 'UNIS Yên Bái', 'address' => 'TP. Yên Bái, Yên Bái', 'latitude' => 21.7168, 'longitude' => 104.9113],
            ['code' => 'SL01', 'name' => 'UNIS Sơn La', 'address' => 'TP. Sơn La, Sơn La', 'latitude' => 21.3270, 'longitude' => 103.9144],
            ['code' => 'TQ01', 'name' => 'UNIS Tuyên Quang', 'address' => 'TP. Tuyên Quang, Tuyên Quang', 'latitude' => 21.8233, 'longitude' => 105.2147],
            ['code' => 'DN01', 'name' => 'UNIS Đà Nẵng', 'address' => 'Hải Châu, Đà Nẵng', 'latitude' => 16.0544, 'longitude' => 108.2022],
            ['code' => 'HUE1', 'name' => 'UNIS Huế', 'address' => 'TP. Huế, Thừa Thiên Huế', 'latitude' => 16.4637, 'longitude' => 107.5909],
            ['code' => 'QB01', 'name' => 'UNIS Đồng Hới', 'address' => 'TP. Đồng Hới, Quảng Bình', 'latitude' => 17.4689, 'longitude' => 106.6223],
            ['code' => 'QNG1', 'name' => 'UNIS Quảng Ngãi', 'address' => 'TP. Quảng Ngãi, Quảng Ngãi', 'latitude' => 15.1214, 'longitude' => 108.8044],
            ['code' => 'QN02', 'name' => 'UNIS Quy Nhơn', 'address' => 'TP. Quy Nhơn, Bình Định', 'latitude' => 13.7820, 'longitude' => 109.2196],
            ['code' => 'NT01', 'name' => 'UNIS Nha Trang', 'address' => 'TP. Nha Trang, Khánh Hòa', 'latitude' => 12.2388, 'longitude' => 109.1967],
            ['code' => 'BMT1', 'name' => 'UNIS Buôn Ma Thuột', 'address' => 'TP. Buôn Ma Thuột, Đắk Lắk', 'latitude' => 12.6667, 'longitude' => 108.0382],
            ['code' => 'PL01', 'name' => 'UNIS Pleiku', 'address' => 'TP. Pleiku, Gia Lai', 'latitude' => 13.9833, 'longitude' => 108.0000],
            ['code' => 'PT01', 'name' => 'UNIS Phan Thiết', 'address' => 'TP. Phan Thiết, Bình Thuận', 'latitude' => 10.9289, 'longitude' => 108.1021],
            ['code' => 'HCM01', 'name' => 'UNIS Quận 1', 'address' => 'Quận 1, TP. Hồ Chí Minh', 'latitude' => 10.7769, 'longitude' => 106.7009],
            ['code' => 'HCM02', 'name' => 'UNIS Thủ Đức', 'address' => 'TP. Thủ Đức, TP. Hồ Chí Minh', 'latitude' => 10.8505, 'longitude' => 106.7717],
            ['code' => 'BD01', 'name' => 'UNIS Bình Dương', 'address' => 'TP. Thủ Dầu Một, Bình Dương', 'latitude' => 10.9804, 'longitude' => 106.6519],
            ['code' => 'BH01', 'name' => 'UNIS Biên Hòa', 'address' => 'TP. Biên Hòa, Đồng Nai', 'latitude' => 10.9574, 'longitude' => 106.8426],
            ['code' => 'VT01', 'name' => 'UNIS Vũng Tàu', 'address' => 'TP. Vũng Tàu, Bà Rịa - Vũng Tàu', 'latitude' => 10.4114, 'longitude' => 107.1362],
            ['code' => 'CT01', 'name' => 'UNIS Cần Thơ', 'address' => 'Ninh Kiều, Cần Thơ', 'latitude' => 10.0452, 'longitude' => 105.7469],
        ],
    ],
];
