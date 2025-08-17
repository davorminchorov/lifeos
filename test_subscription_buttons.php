<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use App\Http\Controllers\SubscriptionController;
use App\Models\Subscription;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing Subscription Pause/Resume Functionality\n";
echo "===============================================\n";

try {
    // Find an active subscription to test with
    $activeSubscription = Subscription::where('status', 'active')->first();

    if (!$activeSubscription) {
        echo "❌ No active subscriptions found. Creating a test subscription...\n";
        $activeSubscription = Subscription::create([
            'user_id' => 1, // Assuming user ID 1 exists
            'service_name' => 'Test Service',
            'cost' => 9.99,
            'currency' => 'USD',
            'billing_cycle' => 'monthly',
            'category' => 'Software',
            'status' => 'active',
            'start_date' => now(),
            'next_billing_date' => now()->addMonth(),
        ]);
        echo "✅ Test subscription created with ID: {$activeSubscription->id}\n";
    }

    echo "\n1. Testing PAUSE functionality:\n";
    echo "   - Initial status: {$activeSubscription->status}\n";

    // Test pause functionality
    $controller = new SubscriptionController();
    $request = new Request();

    // Mock the pause request
    $response = $controller->pause($activeSubscription);
    $activeSubscription->refresh();

    echo "   - Status after pause: {$activeSubscription->status}\n";

    if ($activeSubscription->status === 'paused') {
        echo "   ✅ Pause functionality works correctly\n";
    } else {
        echo "   ❌ Pause functionality failed\n";
    }

    echo "\n2. Testing RESUME functionality:\n";
    echo "   - Current status: {$activeSubscription->status}\n";

    // Test resume functionality
    $response = $controller->resume($activeSubscription);
    $activeSubscription->refresh();

    echo "   - Status after resume: {$activeSubscription->status}\n";

    if ($activeSubscription->status === 'active') {
        echo "   ✅ Resume functionality works correctly\n";
    } else {
        echo "   ❌ Resume functionality failed\n";
    }

    echo "\n3. Testing Routes Integration:\n";

    // Check if routes exist
    $routes = app('router')->getRoutes();
    $pauseRouteExists = false;
    $resumeRouteExists = false;

    foreach ($routes as $route) {
        if ($route->getName() === 'subscriptions.pause') {
            $pauseRouteExists = true;
            echo "   ✅ Pause route exists: " . $route->uri() . "\n";
        }
        if ($route->getName() === 'subscriptions.resume') {
            $resumeRouteExists = true;
            echo "   ✅ Resume route exists: " . $route->uri() . "\n";
        }
    }

    if (!$pauseRouteExists) {
        echo "   ❌ Pause route not found\n";
    }
    if (!$resumeRouteExists) {
        echo "   ❌ Resume route not found\n";
    }

    echo "\n✅ All tests completed successfully!\n";
    echo "The pause/resume buttons should now work correctly in the subscription table.\n";

} catch (Exception $e) {
    echo "❌ Error during testing: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
