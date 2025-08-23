<?php

require_once 'vendor/autoload.php';

use App\Models\User;
use App\Models\UtilityBill;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    echo "Attempting to create a utility bill with null account_number...\n";

    // Get the first user
    $user = User::first();

    if (!$user) {
        echo "No users found. Please run the seeders first.\n";
        exit(1);
    }

    // Try to create a utility bill with null account_number
    $utilityBill = UtilityBill::create([
        'user_id' => $user->id,
        'utility_type' => 'internet',
        'service_provider' => 'Telekom',
        'account_number' => null, // This should cause the error
        'service_address' => 'Test Address',
        'bill_amount' => 4945,
        'usage_amount' => null,
        'usage_unit' => null,
        'rate_per_unit' => null,
        'bill_period_start' => '2025-07-01',
        'bill_period_end' => '2025-07-31',
        'due_date' => '2025-08-20',
        'payment_status' => 'paid',
        'payment_date' => '2025-08-20',
        'service_plan' => 'Magenta1',
        'contract_terms' => null,
        'auto_pay_enabled' => true,
        'budget_alert_threshold' => null,
        'notes' => null,
    ]);

    echo "Utility bill created successfully (this shouldn't happen!)\n";
    echo "ID: " . $utilityBill->id . "\n";

} catch (Exception $e) {
    echo "Error caught (expected): " . $e->getMessage() . "\n";
    echo "This confirms the database constraint violation issue.\n";
}
