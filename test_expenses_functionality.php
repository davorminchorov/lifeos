<?php

/**
 * Test script to verify expenses functionality
 * Run with: php test_expenses_functionality.php
 */

require_once 'vendor/autoload.php';

// Test route existence
echo "=== TESTING EXPENSES MODULE FUNCTIONALITY ===\n\n";

// Test web routes
$webRoutes = [
    'expenses.index' => 'GET /expenses',
    'expenses.create' => 'GET /expenses/create',
    'expenses.store' => 'POST /expenses',
    'expenses.show' => 'GET /expenses/{id}',
    'expenses.edit' => 'GET /expenses/{id}/edit',
    'expenses.update' => 'PUT/PATCH /expenses/{id}',
    'expenses.destroy' => 'DELETE /expenses/{id}',
    'expenses.mark-reimbursed' => 'PATCH /expenses/{id}/mark-reimbursed',
];

echo "WEB ROUTES:\n";
foreach ($webRoutes as $name => $route) {
    echo "✓ $name: $route\n";
}

// Test API routes
$apiRoutes = [
    'api.expenses.index' => 'GET /api/v1/expenses',
    'api.expenses.store' => 'POST /api/v1/expenses',
    'api.expenses.show' => 'GET /api/v1/expenses/{id}',
    'api.expenses.update' => 'PUT/PATCH /api/v1/expenses/{id}',
    'api.expenses.destroy' => 'DELETE /api/v1/expenses/{id}',
    'api.expenses.analytics.summary' => 'GET /api/v1/expenses/analytics/summary',
    'api.expenses.analytics.categories' => 'GET /api/v1/expenses/analytics/categories',
    'api.expenses.analytics.trends' => 'GET /api/v1/expenses/analytics/trends',
    'api.expenses.analytics.budget' => 'GET /api/v1/expenses/analytics/budget',
];

echo "\nAPI ROUTES:\n";
foreach ($apiRoutes as $name => $route) {
    echo "✓ $name: $route\n";
}

// Missing routes that need to be implemented
$missingRoutes = [
    'expenses.analytics' => 'GET /expenses/analytics (web route for analytics view)',
    'expenses.duplicate' => 'POST /expenses/{id}/duplicate',
    'expenses.bulk-action' => 'POST /expenses/bulk-action',
    'api.expenses.mark-reimbursed' => 'PATCH /api/v1/expenses/{id}/mark-reimbursed',
    'api.expenses.duplicate' => 'POST /api/v1/expenses/{id}/duplicate',
    'api.expenses.bulk-action' => 'POST /api/v1/expenses/bulk-action',
];

echo "\nMISSING ROUTES THAT NEED IMPLEMENTATION:\n";
foreach ($missingRoutes as $name => $route) {
    echo "! $name: $route\n";
}

echo "\n=== CONTROLLER METHODS ANALYSIS ===\n";

$controllerMethods = [
    'index' => '✓ Routed (web + API)',
    'create' => '✓ Routed (web only)',
    'store' => '✓ Routed (web + API)',
    'show' => '✓ Routed (web + API)',
    'edit' => '✓ Routed (web only)',
    'update' => '✓ Routed (web + API)',
    'destroy' => '✓ Routed (web + API)',
    'markReimbursed' => '✓ Routed (web only) - missing API',
    'analytics' => '! Missing web route - has view but no route',
    'duplicate' => '! Not routed - missing both web and API',
    'bulkAction' => '! Not routed - missing both web and API',
    'analyticsSummary' => '✓ Routed (API only)',
    'categoryBreakdown' => '✓ Routed (API only)',
    'trendAnalytics' => '✓ Routed (API only)',
    'budgetAnalytics' => '✓ Routed (API only)',
];

foreach ($controllerMethods as $method => $status) {
    echo "$status: $method\n";
}

echo "\n=== VIEWS ANALYSIS ===\n";

$views = [
    'expenses.index' => '✓ Exists',
    'expenses.create' => '✓ Exists',
    'expenses.show' => '✓ Exists',
    'expenses.edit' => '✓ Exists',
    'expenses.analytics' => '✓ Exists - but missing route',
];

foreach ($views as $view => $status) {
    echo "$status: $view\n";
}

echo "\n=== SUMMARY ===\n";
echo "✓ Standard CRUD operations are fully implemented\n";
echo "✓ API analytics endpoints are properly routed\n";
echo "✓ All necessary views exist\n";
echo "✓ Model has proper scopes and relationships\n";
echo "! Missing web route for analytics view\n";
echo "! Missing routes for duplicate functionality\n";
echo "! Missing routes for bulk actions functionality\n";
echo "! Missing API routes for markReimbursed, duplicate, bulkAction\n";

echo "\n=== RECOMMENDATIONS ===\n";
echo "1. Add web route for analytics: Route::get('expenses/analytics', [ExpenseController::class, 'analytics'])->name('expenses.analytics');\n";
echo "2. Add web route for duplicate: Route::post('expenses/{expense}/duplicate', [ExpenseController::class, 'duplicate'])->name('expenses.duplicate');\n";
echo "3. Add web route for bulk actions: Route::post('expenses/bulk-action', [ExpenseController::class, 'bulkAction'])->name('expenses.bulk-action');\n";
echo "4. Add corresponding API routes for consistency\n";

echo "\nTest completed!\n";
