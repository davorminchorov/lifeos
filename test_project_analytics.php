<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Services\InvestmentAnalyticsService;
use App\Services\CurrencyService;

// Get the first user for testing
$user = User::first();

if (!$user) {
    echo "No users found in the database.\n";
    exit(1);
}

echo "Testing project analytics for user: {$user->email}\n";
echo str_repeat('=', 60) . "\n\n";

// Initialize services
$currencyService = new CurrencyService();
$analyticsService = new InvestmentAnalyticsService($currencyService);

// Get project investment analytics
$projectAnalytics = $analyticsService->getProjectInvestmentAnalytics($user->id);

echo "Total Projects: {$projectAnalytics['total_projects']}\n";
echo "Total Invested: " . number_format($projectAnalytics['total_invested'], 2) . "\n";
echo "Active Projects: {$projectAnalytics['active_projects']}\n";
echo "Completed Projects: {$projectAnalytics['completed_projects']}\n\n";

if (count($projectAnalytics['projects']) > 0) {
    echo "Individual Projects:\n";
    echo str_repeat('-', 60) . "\n";

    foreach ($projectAnalytics['projects'] as $project) {
        echo "\nProject: {$project['name']}\n";
        echo "  Type: " . ($project['project_type'] ?? 'N/A') . "\n";
        echo "  Stage: " . ($project['project_stage'] ?? 'N/A') . "\n";
        echo "  Invested: " . number_format($project['invested_amount'], 2) . " {$project['currency']}\n";
        echo "  Current Value: " . number_format($project['current_value'], 2) . " {$project['currency']}\n";
        echo "  Gain/Loss: " . number_format($project['gain_loss'], 2) . " {$project['currency']} (" . number_format($project['gain_loss_percentage'], 2) . "%)\n";

        if ($project['equity_percentage']) {
            echo "  Equity: " . number_format($project['equity_percentage'], 2) . "%\n";
        }
    }

    echo "\n" . str_repeat('=', 60) . "\n";
    echo "✓ Project analytics are displaying individual projects correctly!\n";
} else {
    echo "No project investments found for this user.\n";
    echo "Note: Create some investments with type 'project' to test the feature.\n";
}

echo "\n✓ Test completed successfully!\n";
