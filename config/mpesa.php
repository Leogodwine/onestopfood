<?php

return [

    'enabled' => env('MPESA_ENABLED', false),

    'environment' => env('MPESA_ENV', 'sandbox'),

    'consumer_key' => env('MPESA_CONSUMER_KEY'),
    'consumer_secret' => env('MPESA_CONSUMER_SECRET'),
    'shortcode' => env('MPESA_SHORTCODE'),
    'passkey' => env('MPESA_PASSKEY'),

  /*
    |--------------------------------------------------------------------------
    | Override base URL (Tanzania Vodacom / Kenya Safaricom Daraja)
    | Sandbox TZ: https://openapi.m-pesa.com/sandbox/ipg/v2/vodacomTZN
    | Sandbox KE: https://sandbox.safaricom.co.ke
    |--------------------------------------------------------------------------
    */
    'base_url' => env('MPESA_BASE_URL'),

    'callback_url' => env('MPESA_CALLBACK_URL'),

    'transaction_type' => env('MPESA_TRANSACTION_TYPE', 'CustomerPayBillOnline'),

];
