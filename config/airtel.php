<?php

return [

    'enabled' => env('AIRTEL_ENABLED', false),

    'environment' => env('AIRTEL_ENV', 'sandbox'),

    'client_id' => env('AIRTEL_CLIENT_ID'),
    'client_secret' => env('AIRTEL_CLIENT_SECRET'),

    'country' => env('AIRTEL_COUNTRY', 'TZ'),
    'currency' => env('AIRTEL_CURRENCY', 'TZS'),

    'base_url' => env('AIRTEL_BASE_URL'),

    'callback_url' => env('AIRTEL_CALLBACK_URL'),

];
