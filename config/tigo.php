<?php

return [

    'enabled' => env('TIGO_ENABLED', false),

    'environment' => env('TIGO_ENV', 'sandbox'),

    'client_id' => env('TIGO_CLIENT_ID'),
    'client_secret' => env('TIGO_CLIENT_SECRET'),
    'merchant_id' => env('TIGO_MERCHANT_ID'),
    'account_msisdn' => env('TIGO_ACCOUNT_MSISDN'),

    'base_url' => env('TIGO_BASE_URL'),

    'callback_url' => env('TIGO_CALLBACK_URL'),

    'push_path' => env('TIGO_PUSH_PATH', '/v1/payment/push'),
    'token_path' => env('TIGO_TOKEN_PATH', '/oauth/token'),

];
