<?php

require_once __DIR__.'/vendor/autoload.php';

use Illuminate\Support\Facades\Http;

// Bootstrap Laravel
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Testing Currency Refresh Issue\n";
echo "=============================\n\n";

// Test the current configuration
echo "Current Configuration:\n";
echo 'API Provider: '.config('currency.conversion.api_provider')."\n";
echo 'API Key exists: '.(config('currency.conversion.api_key') ? 'Yes' : 'No')."\n";
echo 'Conversion enabled: '.(config('currency.conversion.enabled') ? 'Yes' : 'No')."\n\n";

// Test the problematic HTTP URL
$apiKey = config('currency.conversion.api_key');
$httpUrl = 'http://data.fixer.io/api/latest';

echo "Testing HTTP URL (current implementation):\n";
echo "URL: $httpUrl\n";

try {
    $response = Http::timeout(10)->get($httpUrl, [
        'access_key' => $apiKey,
        'base' => 'USD',
        'symbols' => 'MKD',
    ]);

    echo 'HTTP Status: '.$response->status()."\n";
    echo 'Response Body: '.$response->body()."\n";
} catch (\Exception $e) {
    echo 'HTTP Error: '.$e->getMessage()."\n";
}

echo "\n".str_repeat('-', 50)."\n\n";

// Test the HTTPS URL
$httpsUrl = 'https://data.fixer.io/api/latest';

echo "Testing HTTPS URL (proposed fix):\n";
echo "URL: $httpsUrl\n";

try {
    $response = Http::timeout(10)->get($httpsUrl, [
        'access_key' => $apiKey,
        'base' => 'USD',
        'symbols' => 'MKD',
    ]);

    echo 'HTTPS Status: '.$response->status()."\n";
    $data = $response->json();

    if (isset($data['success']) && $data['success']) {
        echo "Success: Yes\n";
        echo 'USD to MKD Rate: '.($data['rates']['MKD'] ?? 'Not found')."\n";
    } else {
        echo "Success: No\n";
        echo 'Error: '.($data['error']['info'] ?? 'Unknown error')."\n";
    }
} catch (\Exception $e) {
    echo 'HTTPS Error: '.$e->getMessage()."\n";
}

echo "\n".str_repeat('-', 50)."\n\n";

// Test CurrencyService directly
echo "Testing CurrencyService:\n";

try {
    $currencyService = app(\App\Services\CurrencyService::class);

    echo "Getting USD to MKD rate with current service...\n";
    $rate = $currencyService->refreshExchangeRate('USD', 'MKD');

    if ($rate !== null) {
        echo "Success! USD to MKD Rate: $rate\n";
    } else {
        echo "Failed to get USD to MKD rate\n";
    }
} catch (\Exception $e) {
    echo 'CurrencyService Error: '.$e->getMessage()."\n";
}

echo "\nTest completed.\n";
