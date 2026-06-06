<?php

return [
    'default_country_code' => '255',

    /*
    | dial_code => country meta
    | national_length: exact digits after country code (no leading zero)
    | placeholder: example shown in the input
    */
    'countries' => [
        '255' => ['label' => 'Tanzania', 'iso' => 'TZ', 'national_length' => 9, 'placeholder' => '712 345 678'],
        '254' => ['label' => 'Kenya', 'iso' => 'KE', 'national_length' => 9, 'placeholder' => '712 345 678'],
        '256' => ['label' => 'Uganda', 'iso' => 'UG', 'national_length' => 9, 'placeholder' => '712 345 678'],
        '250' => ['label' => 'Rwanda', 'iso' => 'RW', 'national_length' => 9, 'placeholder' => '781 234 567'],
        '257' => ['label' => 'Burundi', 'iso' => 'BI', 'national_length' => 8, 'placeholder' => '79 12 34 56'],
        '260' => ['label' => 'Zambia', 'iso' => 'ZM', 'national_length' => 9, 'placeholder' => '971 234 567'],
        '263' => ['label' => 'Zimbabwe', 'iso' => 'ZW', 'national_length' => 9, 'placeholder' => '771 234 567'],
        '27' => ['label' => 'South Africa', 'iso' => 'ZA', 'national_length' => 9, 'placeholder' => '82 123 4567'],
        '251' => ['label' => 'Ethiopia', 'iso' => 'ET', 'national_length' => 9, 'placeholder' => '911 234 567'],
        '1' => ['label' => 'United States / Canada', 'iso' => 'US', 'national_length' => 10, 'placeholder' => '202 555 0123'],
        '44' => ['label' => 'United Kingdom', 'iso' => 'GB', 'national_length' => 10, 'placeholder' => '7400 123456'],
    ],
];
