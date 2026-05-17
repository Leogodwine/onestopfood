<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Operations hub (Dar es Salaam default) for distance-based delivery fees
    |--------------------------------------------------------------------------
    */
    'hub_latitude' => env('DELIVERY_HUB_LAT', -6.7924),
    'hub_longitude' => env('DELIVERY_HUB_LNG', 39.2083),

    /*
    |--------------------------------------------------------------------------
    | Auto-confirm non-COD payments (development only — disable in production)
    |--------------------------------------------------------------------------
    */
    'auto_confirm_payments' => env('AUTO_CONFIRM_PAYMENTS', false),

];
