<?php

namespace Database\Seeders;

use App\Models\Iou;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class IouSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all users
        $users = User::all();

        if ($users->isEmpty()) {
            $this->command->warn('No users found. Please seed users first.');

            return;
        }

        $this->command->info('Seeding IOUs...');

        // Create IOUs for each user
        foreach ($users as $user) {
            $this->createIousForUser($user);
        }

        // Create some additional random IOUs
        Iou::factory()
            ->count(15)
            ->create();

        $this->command->info('IOU seeding completed!');
    }

    /**
     * Create realistic IOUs for a specific user.
     */
    private function createIousForUser(User $user): void
    {
        // Money I owe - Pending
        $iOweScenarios = [
            [
                'person_name' => 'John Smith',
                'amount' => 5000.00,
                'description' => 'Borrowed money for car repairs',
                'category' => 'Loan',
                'due_date' => Carbon::now()->addDays(30),
            ],
            [
                'person_name' => 'Jane Doe',
                'amount' => 1500.00,
                'description' => 'Rent split for shared apartment',
                'category' => 'Rent',
                'due_date' => Carbon::now()->addDays(15),
            ],
            [
                'person_name' => 'Michael Johnson',
                'amount' => 800.00,
                'description' => 'Share of vacation expenses',
                'category' => 'Travel',
                'due_date' => Carbon::now()->addDays(45),
            ],
        ];

        foreach ($iOweScenarios as $scenario) {
            Iou::factory()
                ->owe()
                ->pending()
                ->create([
                    'user_id' => $user->id,
                    ...$scenario,
                    'currency' => 'MKD',
                    'transaction_date' => Carbon::now()->subDays(rand(5, 20)),
                    'payment_method' => 'Bank Transfer',
                ]);
        }

        // Money owed to me - Pending
        $owedToMeScenarios = [
            [
                'person_name' => 'Sarah Williams',
                'amount' => 3000.00,
                'description' => 'Lent money for business startup',
                'category' => 'Loan',
                'due_date' => Carbon::now()->addDays(60),
            ],
            [
                'person_name' => 'David Brown',
                'amount' => 2500.00,
                'description' => 'Payment for freelance design work',
                'category' => 'Service',
                'due_date' => Carbon::now()->addDays(10),
            ],
            [
                'person_name' => 'Emily Davis',
                'amount' => 600.00,
                'description' => 'Borrowed money for concert tickets',
                'category' => 'Entertainment',
                'due_date' => Carbon::now()->addDays(20),
            ],
        ];

        foreach ($owedToMeScenarios as $scenario) {
            Iou::factory()
                ->owed()
                ->pending()
                ->create([
                    'user_id' => $user->id,
                    ...$scenario,
                    'currency' => 'MKD',
                    'transaction_date' => Carbon::now()->subDays(rand(5, 25)),
                    'payment_method' => 'Cash',
                ]);
        }

        // Partially paid IOUs - I owe
        Iou::factory()
            ->owe()
            ->create([
                'user_id' => $user->id,
                'person_name' => 'Robert Miller',
                'amount' => 4000.00,
                'amount_paid' => 2000.00,
                'status' => 'partially_paid',
                'description' => 'Borrowed for laptop purchase',
                'category' => 'Loan',
                'currency' => 'MKD',
                'transaction_date' => Carbon::now()->subDays(30),
                'due_date' => Carbon::now()->addDays(30),
                'payment_method' => 'Bank Transfer',
                'notes' => 'Paid half, remaining due next month',
            ]);

        // Partially paid IOUs - Owed to me
        Iou::factory()
            ->owed()
            ->create([
                'user_id' => $user->id,
                'person_name' => 'Jennifer Wilson',
                'amount' => 3500.00,
                'amount_paid' => 1500.00,
                'status' => 'partially_paid',
                'description' => 'Lent for medical expenses',
                'category' => 'Emergency',
                'currency' => 'MKD',
                'transaction_date' => Carbon::now()->subDays(40),
                'due_date' => Carbon::now()->addDays(20),
                'payment_method' => 'Bank Transfer',
                'notes' => 'Monthly installments agreed',
            ]);

        // Overdue IOUs - I owe
        Iou::factory()
            ->owe()
            ->overdue()
            ->create([
                'user_id' => $user->id,
                'person_name' => 'James Moore',
                'amount' => 2000.00,
                'amount_paid' => 0,
                'description' => 'Borrowed for emergency home repair',
                'category' => 'Emergency',
                'currency' => 'MKD',
                'transaction_date' => Carbon::now()->subDays(60),
                'payment_method' => 'Cash',
                'notes' => 'Need to pay urgently',
            ]);

        // Overdue IOUs - Owed to me
        Iou::factory()
            ->owed()
            ->overdue()
            ->create([
                'user_id' => $user->id,
                'person_name' => 'Patricia Taylor',
                'amount' => 1800.00,
                'amount_paid' => 0,
                'description' => 'Lent for moving expenses',
                'category' => 'Service',
                'currency' => 'MKD',
                'transaction_date' => Carbon::now()->subDays(50),
                'payment_method' => 'Bank Transfer',
                'notes' => 'Payment overdue, need to follow up',
            ]);

        // Paid IOUs - I owe
        Iou::factory()
            ->owe()
            ->paid()
            ->create([
                'user_id' => $user->id,
                'person_name' => 'Christopher Anderson',
                'amount' => 2500.00,
                'description' => 'Borrowed for furniture',
                'category' => 'Shopping',
                'currency' => 'MKD',
                'transaction_date' => Carbon::now()->subDays(90),
                'due_date' => Carbon::now()->subDays(10),
                'payment_method' => 'Bank Transfer',
                'notes' => 'Fully paid on time',
            ]);

        // Paid IOUs - Owed to me
        Iou::factory()
            ->owed()
            ->paid()
            ->create([
                'user_id' => $user->id,
                'person_name' => 'Amanda Thomas',
                'amount' => 3200.00,
                'description' => 'Lent for vacation',
                'category' => 'Travel',
                'currency' => 'MKD',
                'transaction_date' => Carbon::now()->subDays(80),
                'due_date' => Carbon::now()->subDays(5),
                'payment_method' => 'Bank Transfer',
                'notes' => 'Received full payment',
            ]);

        // Cancelled IOU - I owe
        Iou::factory()
            ->owe()
            ->create([
                'user_id' => $user->id,
                'person_name' => 'Daniel Jackson',
                'amount' => 1000.00,
                'amount_paid' => 0,
                'status' => 'cancelled',
                'description' => 'Borrowed for event tickets',
                'category' => 'Entertainment',
                'currency' => 'MKD',
                'transaction_date' => Carbon::now()->subDays(70),
                'due_date' => Carbon::now()->subDays(30),
                'payment_method' => 'Cash',
                'notes' => 'Event was cancelled, debt forgiven',
            ]);

        // IOUs with different currencies
        Iou::factory()
            ->owed()
            ->pending()
            ->create([
                'user_id' => $user->id,
                'person_name' => 'Maria Garcia',
                'amount' => 500.00,
                'currency' => 'EUR',
                'description' => 'Lent money during trip abroad',
                'category' => 'Travel',
                'transaction_date' => Carbon::now()->subDays(15),
                'due_date' => Carbon::now()->addDays(45),
                'payment_method' => 'Cash',
            ]);

        Iou::factory()
            ->owe()
            ->pending()
            ->create([
                'user_id' => $user->id,
                'person_name' => 'Thomas White',
                'amount' => 300.00,
                'currency' => 'USD',
                'description' => 'Online course payment shared',
                'category' => 'Education',
                'transaction_date' => Carbon::now()->subDays(10),
                'due_date' => Carbon::now()->addDays(30),
                'payment_method' => 'PayPal',
            ]);

        // Recurring IOUs
        Iou::factory()
            ->owe()
            ->create([
                'user_id' => $user->id,
                'person_name' => 'Lisa Martinez',
                'amount' => 500.00,
                'amount_paid' => 0,
                'status' => 'pending',
                'description' => 'Monthly rent contribution',
                'category' => 'Rent',
                'currency' => 'MKD',
                'transaction_date' => Carbon::now()->startOfMonth(),
                'due_date' => Carbon::now()->endOfMonth(),
                'payment_method' => 'Bank Transfer',
                'is_recurring' => true,
                'recurring_schedule' => 'monthly',
                'notes' => 'Recurring monthly payment',
            ]);
    }
}
