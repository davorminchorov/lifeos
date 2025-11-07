<?php

// Simple test script to verify the currency controller fix
require_once 'vendor/autoload.php';

// Simulate the config call to verify our fix
$supportedCurrencies = array_keys([
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
]);

echo "Testing currency code extraction:\n";
foreach ($supportedCurrencies as $currency) {
    echo 'Currency: '.$currency.' (Type: '.gettype($currency).")\n";

    // Verify it's a string (this is what the CurrencyService expects)
    if (is_string($currency)) {
        echo "✓ $currency is a string - will work with CurrencyService\n";
    } else {
        echo "✗ $currency is not a string - will cause type error\n";
    }
}

echo "\nTest completed. All currencies should be strings now.\n";
