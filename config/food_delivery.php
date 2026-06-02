<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Operations hub (Dar es Salaam default) for distance-based delivery fees
    |--------------------------------------------------------------------------
    */
    'hub_latitude' => env('DELIVERY_HUB_LAT', -6.7924),
    'hub_longitude' => env('DELIVERY_HUB_LNG', 39.2083),

    'payment_reminder_minutes' => (int) env('PAYMENT_REMINDER_MINUTES', 30),

    /*
    |--------------------------------------------------------------------------
    | Traveler GPS considered fresh for auto-assignment (minutes)
    |--------------------------------------------------------------------------
    */
    'traveler_gps_fresh_minutes' => (int) env('TRAVELER_GPS_FRESH_MINUTES', 15),

    /*
    |--------------------------------------------------------------------------
    | Default max items per vehicle type (overridden by traveler max_load_capacity)
    |--------------------------------------------------------------------------
    */
    'vehicle_capacity' => [
        'bicycle' => 3,
        'bike' => 3,
        'motorcycle' => 8,
        'motorbike' => 8,
        'scooter' => 8,
        'car' => 20,
        'sedan' => 20,
        'van' => 50,
        'truck' => 50,
        'pickup' => 50,
        'default' => 10,
    ],

];
