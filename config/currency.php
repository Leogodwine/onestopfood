<?php

return [
    /*
    | Base currency: all meal prices and payments are stored/charged in TZS.
    | Other currencies are display-only conversions at checkout.
    */
    'base' => 'TZS',

    'default' => 'TZS',

    /*
    | rate = how many TZS equal 1 unit of this currency (e.g. 1 USD ≈ 2,500 TZS)
    */
    'currencies' => [
        'TZS' => [
            'label' => 'Tanzanian Shilling',
            'symbol' => 'TZS',
            'decimals' => 0,
            'rate' => 1,
        ],
        'USD' => [
            'label' => 'US Dollar',
            'symbol' => '$',
            'decimals' => 2,
            'rate' => 2500,
        ],
        'EUR' => [
            'label' => 'Euro',
            'symbol' => '€',
            'decimals' => 2,
            'rate' => 2700,
        ],
        'GBP' => [
            'label' => 'British Pound',
            'symbol' => '£',
            'decimals' => 2,
            'rate' => 3200,
        ],
        'KES' => [
            'label' => 'Kenyan Shilling',
            'symbol' => 'KES',
            'decimals' => 0,
            'rate' => 19,
        ],
        'UGX' => [
            'label' => 'Ugandan Shilling',
            'symbol' => 'UGX',
            'decimals' => 0,
            'rate' => 0.65,
        ],
    ],
];
