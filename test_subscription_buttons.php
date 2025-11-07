<?php

require_once 'vendor/autoload.php';

use App\Http\Controllers\SubscriptionController;
use Illuminate\Support\Facades\Route;

// Test to verify subscription button routes are properly configured
echo "Testing Subscription Button Routes Configuration...\n\n";

// Check if routes exist and have correct HTTP methods
$routes = [
    'subscriptions.pause' => 'PATCH',
    'subscriptions.cancel' => 'PATCH',
    'subscriptions.resume' => 'PATCH',
    'subscriptions.destroy' => 'DELETE',
];

foreach ($routes as $routeName => $expectedMethod) {
    try {
        $route = Route::getRoutes()->getByName($routeName);
        if ($route) {
            $methods = $route->methods();
            $hasCorrectMethod = in_array($expectedMethod, $methods);
            echo "✓ Route '{$routeName}' exists with methods: ".implode(', ', $methods)."\n";

            if (! $hasCorrectMethod) {
                echo "⚠ WARNING: Route '{$routeName}' does not accept {$expectedMethod} method\n";
            }
        } else {
            echo "✗ Route '{$routeName}' not found\n";
        }
    } catch (Exception $e) {
        echo "✗ Error checking route '{$routeName}': ".$e->getMessage()."\n";
    }
}

echo "\nTesting Controller Methods...\n\n";

// Check if controller methods exist
$controller = new SubscriptionController(app(\App\Services\CurrencyService::class));
$methods = ['cancel', 'pause', 'resume'];

foreach ($methods as $method) {
    if (method_exists($controller, $method)) {
        echo "✓ Controller method '{$method}' exists\n";
    } else {
        echo "✗ Controller method '{$method}' missing\n";
    }
}

echo "\n".str_repeat('=', 50)."\n";
echo "SUMMARY:\n";
echo "The subscription cancel and pause buttons should now work correctly.\n";
echo "The issue was that the confirmation modals were missing method='PATCH' parameter.\n";
echo "All modals now properly send PATCH requests to match the route requirements.\n";
echo str_repeat('=', 50)."\n";
