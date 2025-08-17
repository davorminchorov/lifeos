<?php

namespace Database\Seeders;

use App\Models\Contract;
use App\Models\Expense;
use App\Models\Investment;
use App\Models\Subscription;
use App\Models\User;
use App\Models\UtilityBill;
use App\Models\Warranty;
use Illuminate\Database\Seeder;

class LifeOSSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a demo user if none exists
        $user = User::firstOrCreate(
            ['email' => 'davor@lifeos.com'],
            [
                'name' => 'Davor Minchorov',
                'email_verified_at' => now(),
                'password' => bcrypt('password'),
            ]
        );

        $this->command->info('Created demo user: '.$user->email);

        // Create Subscriptions
        $this->createSubscriptions($user);

        // Create Contracts
        $this->createContracts($user);

        // Create Warranties
        $this->createWarranties($user);

        // Create Investments
        $this->createInvestments($user);

        // Create Expenses
        $this->createExpenses($user);

        // Create Utility Bills
        $this->createUtilityBills($user);

        $this->command->info('LifeOS demo data seeded successfully!');
    }

    private function createSubscriptions(User $user): void
    {
        $subscriptions = [
            [
                'service_name' => 'Netflix',
                'description' => 'Premium streaming subscription',
                'category' => 'Entertainment',
                'cost' => 911.43,
                'billing_cycle' => 'monthly',
                'currency' => 'MKD',
                'start_date' => now()->subMonths(8),
                'next_billing_date' => now()->addDays(15),
                'payment_method' => 'Credit Card',
                'merchant_info' => 'Netflix Inc.',
                'auto_renewal' => true,
                'cancellation_difficulty' => 2,
                'tags' => ['entertainment', 'streaming', 'essential'],
                'status' => 'active',
            ],
            [
                'service_name' => 'Adobe Creative Suite',
                'description' => 'Creative Cloud All Apps subscription',
                'category' => 'Software',
                'cost' => 3020.43,
                'billing_cycle' => 'monthly',
                'currency' => 'MKD',
                'start_date' => now()->subMonths(12),
                'next_billing_date' => now()->addDays(5),
                'payment_method' => 'Credit Card',
                'merchant_info' => 'Adobe Systems',
                'auto_renewal' => true,
                'cancellation_difficulty' => 3,
                'tags' => ['software', 'work', 'creative'],
                'status' => 'active',
            ],
            [
                'service_name' => 'Gym Membership',
                'description' => 'Premium fitness center membership',
                'category' => 'Fitness',
                'cost' => 4559.43,
                'billing_cycle' => 'monthly',
                'currency' => 'MKD',
                'start_date' => now()->subMonths(6),
                'next_billing_date' => now()->addDays(12),
                'payment_method' => 'Bank Transfer',
                'merchant_info' => 'FitLife Gym',
                'auto_renewal' => true,
                'cancellation_difficulty' => 4,
                'tags' => ['fitness', 'health'],
                'status' => 'active',
            ],
        ];

        foreach ($subscriptions as $subscriptionData) {
            Subscription::factory()->create(array_merge($subscriptionData, ['user_id' => $user->id]));
        }

        // Create additional random subscriptions
        Subscription::factory(7)->create(['user_id' => $user->id]);

        $this->command->info('Created 10 subscriptions');
    }

    private function createContracts(User $user): void
    {
        $contracts = [
            [
                'contract_type' => 'lease',
                'title' => 'Apartment Lease - Downtown',
                'counterparty' => 'Metropolitan Properties LLC',
                'start_date' => now()->subYear(),
                'end_date' => now()->addYear(),
                'notice_period_days' => 60,
                'auto_renewal' => false,
                'contract_value' => 1368000.00,
                'payment_terms' => 'Monthly',
                'key_obligations' => 'Monthly rent payment, property maintenance, no subletting without permission',
                'status' => 'active',
            ],
            [
                'contract_type' => 'service',
                'title' => 'Internet Service Contract',
                'counterparty' => 'TechConnect ISP',
                'start_date' => now()->subMonths(8),
                'end_date' => now()->addMonths(16),
                'notice_period_days' => 30,
                'auto_renewal' => true,
                'contract_value' => 68400.00,
                'payment_terms' => 'Monthly',
                'key_obligations' => 'Provide 1Gbps internet service with 99.9% uptime guarantee',
                'status' => 'active',
            ],
            [
                'contract_type' => 'insurance',
                'title' => 'Auto Insurance Policy',
                'counterparty' => 'SafeDrive Insurance Co.',
                'start_date' => now()->subMonths(4),
                'end_date' => now()->addMonths(8),
                'notice_period_days' => 30,
                'auto_renewal' => true,
                'contract_value' => 136800.00,
                'payment_terms' => 'Semi-annually',
                'key_obligations' => 'Comprehensive coverage including collision and theft',
                'status' => 'active',
            ],
        ];

        foreach ($contracts as $contractData) {
            Contract::factory()->create(array_merge($contractData, ['user_id' => $user->id]));
        }

        // Create additional random contracts
        Contract::factory(5)->create(['user_id' => $user->id]);

        $this->command->info('Created 8 contracts');
    }

    private function createWarranties(User $user): void
    {
        $warranties = [
            [
                'product_name' => 'MacBook Pro 16-inch',
                'brand' => 'Apple',
                'model' => 'MBP16-2023',
                'serial_number' => 'C02ABC123DEF',
                'purchase_date' => now()->subMonths(10),
                'purchase_price' => 142443.00,
                'retailer' => 'Apple Store',
                'warranty_duration_months' => 12,
                'warranty_type' => 'manufacturer',
                'warranty_expiration_date' => now()->addMonths(2),
                'current_status' => 'active',
            ],
            [
                'product_name' => 'Sony WH-1000XM5 Headphones',
                'brand' => 'Sony',
                'model' => 'WH-1000XM5',
                'serial_number' => 'SN789XYZ456',
                'purchase_date' => now()->subMonths(6),
                'purchase_price' => 22799.43,
                'retailer' => 'Best Buy',
                'warranty_duration_months' => 24,
                'warranty_type' => 'extended',
                'warranty_expiration_date' => now()->addMonths(18),
                'current_status' => 'active',
            ],
            [
                'product_name' => 'Tesla Model 3',
                'brand' => 'Tesla',
                'model' => 'Model 3 Long Range',
                'serial_number' => 'TSLA2023456789',
                'purchase_date' => now()->subMonths(18),
                'purchase_price' => 3134943.00,
                'retailer' => 'Tesla Motors',
                'warranty_duration_months' => 48,
                'warranty_type' => 'manufacturer',
                'warranty_expiration_date' => now()->addMonths(30),
                'current_status' => 'active',
            ],
        ];

        foreach ($warranties as $warrantyData) {
            Warranty::factory()->create(array_merge($warrantyData, ['user_id' => $user->id]));
        }

        // Create additional random warranties
        Warranty::factory(7)->create(['user_id' => $user->id]);

        $this->command->info('Created 10 warranties');
    }

    private function createInvestments(User $user): void
    {
        $investments = [
            [
                'investment_type' => 'stocks',
                'symbol_identifier' => 'AAPL',
                'name' => 'Apple Inc.',
                'quantity' => 50.00000000,
                'purchase_date' => now()->subMonths(24),
                'purchase_price' => 8564.25000000,
                'current_value' => 10573.50000000,
                'total_dividends_received' => 7153.50,
                'total_fees_paid' => 569.43,
                'risk_tolerance' => 'moderate',
                'account_broker' => 'Fidelity',
                'status' => 'active',
            ],
            [
                'investment_type' => 'etf',
                'symbol_identifier' => 'SPY',
                'name' => 'SPDR S&P 500 ETF',
                'quantity' => 25.00000000,
                'purchase_date' => now()->subMonths(18),
                'purchase_price' => 23982.75000000,
                'current_value' => 25376.40000000,
                'total_dividends_received' => 5087.25,
                'total_fees_paid' => 427.50,
                'risk_tolerance' => 'conservative',
                'account_broker' => 'Charles Schwab',
                'status' => 'active',
            ],
            [
                'investment_type' => 'crypto',
                'symbol_identifier' => 'BTC',
                'name' => 'Bitcoin',
                'quantity' => 0.25000000,
                'purchase_date' => now()->subMonths(12),
                'purchase_price' => 2565000.00000000,
                'current_value' => 2964000.00000000,
                'total_dividends_received' => 0.00,
                'total_fees_paid' => 7125.00,
                'risk_tolerance' => 'aggressive',
                'account_broker' => 'Coinbase',
                'status' => 'active',
            ],
        ];

        foreach ($investments as $investmentData) {
            Investment::factory()->create(array_merge($investmentData, ['user_id' => $user->id]));
        }

        // Create additional random investments
        Investment::factory(12)->create(['user_id' => $user->id]);

        $this->command->info('Created 15 investments');
    }

    private function createExpenses(User $user): void
    {
        // Create expenses for the current month
        $categories = ['food', 'transport', 'entertainment', 'shopping', 'healthcare'];

        foreach ($categories as $category) {
            // Create 5-10 expenses per category
            Expense::factory(rand(5, 10))->create([
                'user_id' => $user->id,
                'category' => $category,
                'expense_date' => now()->subDays(rand(1, 30)),
            ]);
        }

        // Create additional random expenses for the past 6 months
        Expense::factory(150)->create([
            'user_id' => $user->id,
            'expense_date' => now()->subDays(rand(31, 180)),
        ]);

        $this->command->info('Created 190+ expenses');
    }

    private function createUtilityBills(User $user): void
    {
        $utilities = [
            [
                'utility_type' => 'electricity',
                'service_provider' => 'Metro Electric Company',
                'account_number' => '1234567890',
                'service_address' => '123 Main St, Downtown City',
                'bill_amount' => 7167.75,
                'usage_amount' => 850.50,
                'usage_unit' => 'kWh',
                'rate_per_unit' => 8.44,
                'bill_period_start' => now()->subMonth()->startOfMonth(),
                'bill_period_end' => now()->subMonth()->endOfMonth(),
                'due_date' => now()->addDays(10),
                'payment_status' => 'pending',
            ],
            [
                'utility_type' => 'gas',
                'service_provider' => 'City Gas Services',
                'account_number' => '9876543210',
                'service_address' => '123 Main St, Downtown City',
                'bill_amount' => 4460.25,
                'usage_amount' => 65.25,
                'usage_unit' => 'therms',
                'rate_per_unit' => 68.40,
                'bill_period_start' => now()->subMonth()->startOfMonth(),
                'bill_period_end' => now()->subMonth()->endOfMonth(),
                'due_date' => now()->addDays(15),
                'payment_status' => 'pending',
            ],
            [
                'utility_type' => 'internet',
                'service_provider' => 'FiberNet Communications',
                'account_number' => '5555444433',
                'service_address' => '123 Main St, Downtown City',
                'bill_amount' => 5129.43,
                'usage_amount' => 1250.00,
                'usage_unit' => 'GB',
                'bill_period_start' => now()->subMonth()->startOfMonth(),
                'bill_period_end' => now()->subMonth()->endOfMonth(),
                'due_date' => now()->addDays(20),
                'payment_status' => 'paid',
                'payment_date' => now()->subDays(5),
            ],
        ];

        foreach ($utilities as $utilityData) {
            UtilityBill::factory()->create(array_merge($utilityData, ['user_id' => $user->id]));
        }

        // Create additional random utility bills for past months
        UtilityBill::factory(20)->create(['user_id' => $user->id]);

        $this->command->info('Created 23 utility bills');
    }
}
