<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Budget;
use App\Models\Contract;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\Investment;
use App\Models\InvestmentGoal;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Iou;
use App\Models\JobApplication;
use App\Models\JobApplicationInterview;
use App\Models\JobApplicationOffer;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\TenantMember;
use App\Models\User;
use App\Models\UtilityBill;
use App\Models\Warranty;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Creating demo user and tenant...');

        $user = User::firstOrCreate(
            ['email' => 'demo@lifeos.test'],
            [
                'name' => 'Demo User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        $tenant = Tenant::firstOrCreate(
            ['owner_id' => $user->id],
            ['name' => 'Demo Workspace', 'slug' => 'demo-workspace']
        );

        TenantMember::firstOrCreate([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
        ], ['role' => 'owner']);

        $user->update(['current_tenant_id' => $tenant->id]);

        $ctx = ['user_id' => $user->id, 'tenant_id' => $tenant->id];

        $this->command->info('Seeding subscriptions...');
        $this->seedSubscriptions($ctx);

        $this->command->info('Seeding expenses...');
        $this->seedExpenses($ctx);

        $this->command->info('Seeding budgets...');
        $this->seedBudgets($ctx);

        $this->command->info('Seeding utility bills...');
        $this->seedUtilityBills($ctx);

        $this->command->info('Seeding contracts...');
        $this->seedContracts($ctx);

        $this->command->info('Seeding warranties...');
        $this->seedWarranties($ctx);

        $this->command->info('Seeding investments...');
        $this->seedInvestments($ctx);

        $this->command->info('Seeding IOUs...');
        $this->seedIous($ctx);

        $this->command->info('Seeding job applications...');
        $this->seedJobApplications($ctx);

        $this->command->info('Seeding invoicing...');
        $this->seedInvoicing($ctx);

        $this->command->info('Demo data seeded! Login as demo@lifeos.test / password');
    }

    private function seedSubscriptions(array $ctx): void
    {
        $subs = [
            ['service_name' => 'Netflix', 'category' => 'Entertainment', 'cost' => 15.99, 'currency' => 'USD', 'billing_cycle' => 'monthly', 'status' => 'active'],
            ['service_name' => 'Spotify Premium', 'category' => 'Entertainment', 'cost' => 9.99, 'currency' => 'USD', 'billing_cycle' => 'monthly', 'status' => 'active'],
            ['service_name' => 'GitHub Pro', 'category' => 'Development', 'cost' => 4.00, 'currency' => 'USD', 'billing_cycle' => 'monthly', 'status' => 'active'],
            ['service_name' => 'JetBrains All Products', 'category' => 'Development', 'cost' => 289.00, 'currency' => 'USD', 'billing_cycle' => 'yearly', 'status' => 'active'],
            ['service_name' => 'iCloud+ 200GB', 'category' => 'Storage', 'cost' => 2.99, 'currency' => 'USD', 'billing_cycle' => 'monthly', 'status' => 'active'],
            ['service_name' => 'ChatGPT Plus', 'category' => 'Software', 'cost' => 20.00, 'currency' => 'USD', 'billing_cycle' => 'monthly', 'status' => 'active'],
            ['service_name' => 'Gym Membership', 'category' => 'Fitness', 'cost' => 1500.00, 'currency' => 'MKD', 'billing_cycle' => 'monthly', 'status' => 'active'],
            ['service_name' => 'Adobe Creative Cloud', 'category' => 'Software', 'cost' => 54.99, 'currency' => 'USD', 'billing_cycle' => 'monthly', 'status' => 'paused'],
            ['service_name' => 'Hulu', 'category' => 'Entertainment', 'cost' => 7.99, 'currency' => 'USD', 'billing_cycle' => 'monthly', 'status' => 'cancelled'],
        ];

        foreach ($subs as $i => $sub) {
            Subscription::factory()->create(array_merge($ctx, $sub, [
                'start_date' => now()->subMonths(rand(1, 12)),
                'next_billing_date' => now()->addDays(rand(1, 30)),
                'auto_renewal' => true,
                'payment_method' => collect(['Credit Card', 'PayPal', 'Bank Transfer'])->random(),
            ]));
        }
    }

    private function seedExpenses(array $ctx): void
    {
        $categories = ['Food', 'Transport', 'Shopping', 'Entertainment', 'Health', 'Education', 'Bills', 'Travel'];

        for ($i = 0; $i < 50; $i++) {
            Expense::factory()->create(array_merge($ctx, [
                'category' => $categories[array_rand($categories)],
                'amount' => rand(100, 15000),
                'currency' => collect(['MKD', 'EUR', 'USD'])->random(),
                'expense_date' => now()->subDays(rand(0, 90)),
            ]));
        }
    }

    private function seedBudgets(array $ctx): void
    {
        $budgets = [
            ['category' => 'Food', 'amount' => 15000, 'budget_period' => 'monthly'],
            ['category' => 'Transport', 'amount' => 5000, 'budget_period' => 'monthly'],
            ['category' => 'Entertainment', 'amount' => 8000, 'budget_period' => 'monthly'],
            ['category' => 'Shopping', 'amount' => 10000, 'budget_period' => 'monthly'],
            ['category' => 'Health', 'amount' => 3000, 'budget_period' => 'monthly'],
        ];

        foreach ($budgets as $b) {
            Budget::factory()->create(array_merge($ctx, $b, [
                'currency' => 'MKD',
                'is_active' => true,
            ]));
        }
    }

    private function seedUtilityBills(array $ctx): void
    {
        $bills = [
            ['utility_type' => 'electricity', 'service_provider' => 'EVN Macedonia', 'bill_amount' => rand(2000, 5000)],
            ['utility_type' => 'water', 'service_provider' => 'Vodovod Skopje', 'bill_amount' => rand(500, 1500)],
            ['utility_type' => 'internet', 'service_provider' => 'A1 Macedonia', 'bill_amount' => 1299],
            ['utility_type' => 'gas', 'service_provider' => 'MER', 'bill_amount' => rand(1000, 4000)],
            ['utility_type' => 'phone', 'service_provider' => 'T-Mobile', 'bill_amount' => 999],
        ];

        foreach ($bills as $bill) {
            for ($m = 0; $m < 3; $m++) {
                UtilityBill::factory()->create(array_merge($ctx, $bill, [
                    'currency' => 'MKD',
                    'due_date' => now()->subMonths($m)->endOfMonth(),
                    'payment_status' => $m > 0 ? 'paid' : 'pending',
                    'bill_period_start' => now()->subMonths($m)->startOfMonth(),
                    'bill_period_end' => now()->subMonths($m)->endOfMonth(),
                ]));
            }
        }
    }

    private function seedContracts(array $ctx): void
    {
        Contract::factory()->count(5)->create(array_merge($ctx, [
            'status' => 'active',
        ]));
        Contract::factory()->count(2)->create(array_merge($ctx, [
            'status' => 'expired',
            'end_date' => now()->subMonths(rand(1, 6)),
        ]));
    }

    private function seedWarranties(array $ctx): void
    {
        Warranty::factory()->count(6)->create(array_merge($ctx, [
            'current_status' => 'active',
        ]));
        Warranty::factory()->count(2)->create(array_merge($ctx, [
            'current_status' => 'expired',
            'warranty_expiration_date' => now()->subMonths(rand(1, 12)),
        ]));
    }

    private function seedInvestments(array $ctx): void
    {
        Investment::factory()->count(8)->create($ctx);

        InvestmentGoal::factory()->count(3)->create($ctx);
    }

    private function seedIous(array $ctx): void
    {
        Iou::factory()->count(6)->create($ctx);
    }

    private function seedJobApplications(array $ctx): void
    {
        $statuses = ['wishlist', 'applied', 'screening', 'interview', 'assessment', 'offer', 'accepted', 'rejected'];

        foreach ($statuses as $status) {
            $app = JobApplication::factory()->create(array_merge($ctx, [
                'status' => $status,
                'applied_at' => $status === 'wishlist' ? null : now()->subDays(rand(5, 60)),
            ]));

            if (in_array($status, ['interview', 'assessment', 'offer', 'accepted'])) {
                JobApplicationInterview::factory()->create([
                    'job_application_id' => $app->id,
                    'user_id' => $ctx['user_id'],
                    'tenant_id' => $ctx['tenant_id'],
                ]);
            }

            if (in_array($status, ['offer', 'accepted'])) {
                JobApplicationOffer::factory()->create([
                    'job_application_id' => $app->id,
                    'user_id' => $ctx['user_id'],
                    'tenant_id' => $ctx['tenant_id'],
                ]);
            }
        }
    }

    private function seedInvoicing(array $ctx): void
    {
        $customers = Customer::factory()->count(5)->create($ctx);

        foreach ($customers as $customer) {
            $invoice = Invoice::factory()->create(array_merge($ctx, [
                'customer_id' => $customer->id,
            ]));

            InvoiceItem::factory()->count(rand(1, 4))->create([
                'invoice_id' => $invoice->id,
                'tenant_id' => $ctx['tenant_id'],
            ]);
        }
    }
}
