<?php

return [
    // API key from Trading212 (set in .env)
    'api_key' => env('TRADING212_API_KEY'),

    // practice or live environment (values: 'practice' or 'live')
    'environment' => env('TRADING212_ENV', 'practice'),

    // Optional: override base URL; SDK picks by environment when null
    'base_url' => env('TRADING212_BASE_URL', null),

    // Default broker/account labels used when storing transactions
    'broker_name' => env('TRADING212_BROKER_NAME', 'Trading212'),
    'account_number' => env('TRADING212_ACCOUNT_NUMBER'),

    // How many days back to fetch on first run
    'initial_lookback_days' => env('TRADING212_LOOKBACK_DAYS', 30),
];
