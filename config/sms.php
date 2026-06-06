<?php

return [
    'enabled' => (bool) env('SMS_ENABLED', false),

    'driver' => env('SMS_DRIVER', 'log'),

    'from' => env('SMS_FROM', 'OneStopFood'),

    'africas_talking' => [
        'username' => env('AFRICAS_TALKING_USERNAME'),
        'api_key' => env('AFRICAS_TALKING_API_KEY'),
        'from' => env('AFRICAS_TALKING_FROM', env('SMS_FROM', 'OneStopFood')),
    ],
];
