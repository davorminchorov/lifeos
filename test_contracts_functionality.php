<?php

// Simple test to verify contracts functionality
echo "Testing Contracts Module Implementation:\n\n";

// Check if all required files exist
$files_to_check = [
    'app/Models/Contract.php',
    'app/Http/Controllers/ContractController.php',
    'app/Http/Resources/ContractResource.php',
    'app/Http/Requests/StoreContractRequest.php',
    'app/Http/Requests/UpdateContractRequest.php',
    'app/Policies/ContractPolicy.php',
    'resources/views/contracts/index.blade.php',
    'resources/views/contracts/show.blade.php',
    'resources/views/contracts/create.blade.php',
    'resources/views/contracts/edit.blade.php',
    'database/migrations/2025_08_13_220146_create_contracts_table.php',
    'tests/Feature/ContractTest.php',
];

echo "1. Checking if all required files exist:\n";
foreach ($files_to_check as $file) {
    $full_path = "/Users/davorminchorov/Code/GitHub/lifeos/$file";
    if (file_exists($full_path)) {
        echo "✓ $file exists\n";
    } else {
        echo "✗ $file missing\n";
    }
}

echo "\n2. Checking Contract Model features:\n";
$contract_model = file_get_contents('/Users/davorminchorov/Code/GitHub/lifeos/app/Models/Contract.php');
if (strpos($contract_model, 'use HasFactory, SoftDeletes;') !== false) {
    echo "✓ SoftDeletes trait added\n";
} else {
    echo "✗ SoftDeletes trait missing\n";
}

if (strpos($contract_model, 'public function scopeActive') !== false) {
    echo "✓ Active scope implemented\n";
} else {
    echo "✗ Active scope missing\n";
}

if (strpos($contract_model, 'public function scopeExpiringSoon') !== false) {
    echo "✓ ExpiringSoon scope implemented\n";
} else {
    echo "✗ ExpiringSoon scope missing\n";
}

echo "\n3. Checking ContractController features:\n";
$controller = file_get_contents('/Users/davorminchorov/Code/GitHub/lifeos/app/Http/Controllers/ContractController.php');
if (strpos($controller, 'public function __construct()') !== false) {
    echo "✓ Authorization middleware added\n";
} else {
    echo "✗ Authorization middleware missing\n";
}

if (strpos($controller, "->where('user_id', auth()->id())") !== false) {
    echo "✓ User filtering in index method\n";
} else {
    echo "✗ User filtering missing\n";
}

$required_methods = ['index', 'create', 'store', 'show', 'edit', 'update', 'destroy', 'terminate', 'renew', 'addAmendment', 'analyticsSummary', 'expiringAnalytics', 'performanceAnalytics'];
foreach ($required_methods as $method) {
    if (strpos($controller, "public function $method") !== false) {
        echo "✓ $method method implemented\n";
    } else {
        echo "✗ $method method missing\n";
    }
}

echo "\n4. Checking ContractPolicy:\n";
$policy = file_get_contents('/Users/davorminchorov/Code/GitHub/lifeos/app/Policies/ContractPolicy.php');
if (strpos($policy, 'return $contract->user_id === $user->id;') !== false) {
    echo "✓ User ownership authorization implemented\n";
} else {
    echo "✗ User ownership authorization missing\n";
}

echo "\n5. Checking Migration:\n";
$migration = file_get_contents('/Users/davorminchorov/Code/GitHub/lifeos/database/migrations/2025_08_13_220146_create_contracts_table.php');
if (strpos($migration, '$table->softDeletes();') !== false) {
    echo "✓ SoftDeletes column added to migration\n";
} else {
    echo "✗ SoftDeletes column missing from migration\n";
}

echo "\nContracts module implementation check completed!\n";
