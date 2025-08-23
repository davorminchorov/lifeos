<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Test the reimbursement functionality
echo "Testing Expense reimbursement issue...\n\n";

// Create a test expense or get an existing one
$user = \App\Models\User::first();
if (!$user) {
    echo "ERROR: No users found in database\n";
    exit(1);
}

// Create a test expense
$expense = \App\Models\Expense::factory()->create([
    'user_id' => $user->id,
    'status' => 'confirmed',
    'description' => 'Test expense for reimbursement bug'
]);

echo "Created test expense with ID: {$expense->id}\n";
echo "Initial status: {$expense->status}\n";

// Test 1: Check if is_reimbursed property exists (this should fail)
echo "\nTest 1: Checking is_reimbursed property...\n";
try {
    $isReimbursed = $expense->is_reimbursed;
    echo "is_reimbursed value: " . ($isReimbursed ? 'true' : 'false') . "\n";
} catch (Exception $e) {
    echo "ERROR accessing is_reimbursed: " . $e->getMessage() . "\n";
}

// Test 2: Update status to reimbursed (this should work)
echo "\nTest 2: Updating status to reimbursed...\n";
$expense->update(['status' => 'reimbursed']);
$expense->refresh();
echo "Updated status: {$expense->status}\n";

// Test 3: Check is_reimbursed again after status change
echo "\nTest 3: Checking is_reimbursed after status change...\n";
try {
    $isReimbursed = $expense->is_reimbursed;
    echo "is_reimbursed value: " . ($isReimbursed ? 'true' : 'false') . "\n";
} catch (Exception $e) {
    echo "ERROR accessing is_reimbursed: " . $e->getMessage() . "\n";
}

// Clean up
$expense->delete();
echo "\nTest expense deleted.\n";
echo "\nConclusion: The is_reimbursed accessor method is missing from the Expense model.\n";
echo "This causes both display issues in views and conditional logic failures.\n";
