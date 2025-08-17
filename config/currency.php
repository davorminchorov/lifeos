<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Currency
    |--------------------------------------------------------------------------
    |
    | The default currency code used throughout the application.
    | This will be the primary currency for all modules.
    |
    */
    'default' => env('DEFAULT_CURRENCY', 'MKD'),

    /*
    |--------------------------------------------------------------------------
    | Supported Currencies
    |--------------------------------------------------------------------------
    |
    | List of supported currencies with their symbols and names.
    | The key should be the 3-letter ISO currency code.
    |
    */
    'supported' => [
        'MKD' => [
            'name' => 'Macedonian Denar',
            'symbol' => 'MKD',
            'decimal_places' => 2,
        ],
        'USD' => [
            'name' => 'US Dollar',
            'symbol' => '$',
            'decimal_places' => 2,
        ],
        'EUR' => [
            'name' => 'Euro',
            'symbol' => '€',
            'decimal_places' => 2,
        ],
        'GBP' => [
            'name' => 'British Pound',
            'symbol' => '£',
            'decimal_places' => 2,
        ],
        'CAD' => [
            'name' => 'Canadian Dollar',
            'symbol' => 'C$',
            'decimal_places' => 2,
        ],
        'AUD' => [
            'name' => 'Australian Dollar',
            'symbol' => 'A$',
            'decimal_places' => 2,
        ],
        'JPY' => [
            'name' => 'Japanese Yen',
            'symbol' => '¥',
            'decimal_places' => 0,
        ],
        'CHF' => [
            'name' => 'Swiss Franc',
            'symbol' => 'CHF',
            'decimal_places' => 2,
        ],
        'RSD' => [
            'name' => 'Serbian Dinar',
            'symbol' => 'RSD',
            'decimal_places' => 2,
        ],
        'BGN' => [
            'name' => 'Bulgarian Lev',
            'symbol' => 'лв',
            'decimal_places' => 2,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Currency Conversion API
    |--------------------------------------------------------------------------
    |
    | Configuration for currency conversion services.
    | You can use free services like exchangerate-api.io or fixer.io
    |
    */
    'conversion' => [
        'enabled' => env('CURRENCY_CONVERSION_ENABLED', true),
        'api_provider' => env('CURRENCY_API_PROVIDER', 'exchangerate'),
        'api_key' => env('CURRENCY_API_KEY', null),
        'cache_duration' => env('CURRENCY_CACHE_DURATION', 3600), // 1 hour in seconds
        'base_currency' => 'MKD', // Base currency for conversions
    ],

    /*
    |--------------------------------------------------------------------------
    | Display Settings
    |--------------------------------------------------------------------------
    |
    | Settings for how currencies are displayed throughout the application.
    |
    */
    'display' => [
        'symbol_position' => 'before', // 'before' or 'after'
        'show_currency_code' => true,
        'thousand_separator' => ',',
        'decimal_separator' => '.',
    ],
];
