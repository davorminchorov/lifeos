<?php

namespace Database\Seeders;

use App\Models\Budget;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class BudgetSeeder extends Seeder
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

        $this->command->info('Seeding budgets...');

        // Create budgets for each user
        foreach ($users as $user) {
            $this->createBudgetsForUser($user);
        }

        // Create some additional random budgets
        Budget::factory()
            ->count(20)
            ->create();

        $this->command->info('Budget seeding completed!');
    }

    /**
     * Create realistic budgets for a specific user.
     */
    private function createBudgetsForUser(User $user): void
    {
        // Essential monthly budgets
        $essentialBudgets = [
            ['category' => 'Food & Dining', 'amount' => 800.00],
            ['category' => 'Transportation', 'amount' => 400.00],
            ['category' => 'Bills & Utilities', 'amount' => 600.00],
            ['category' => 'Groceries', 'amount' => 500.00],
            ['category' => 'Health & Fitness', 'amount' => 150.00],
        ];

        foreach ($essentialBudgets as $budgetData) {
            Budget::factory()
                ->monthly()
                ->active()
                ->forUser($user)
                ->forCategory($budgetData['category'])
                ->create([
                    'amount' => $budgetData['amount'],
                    'currency' => 'MKD',
                    'alert_threshold' => 80,
                ]);
        }

        // Discretionary monthly budgets
        $discretionaryBudgets = [
            ['category' => 'Entertainment', 'amount' => 200.00, 'threshold' => 90],
            ['category' => 'Shopping', 'amount' => 300.00, 'threshold' => 85],
            ['category' => 'Personal Care', 'amount' => 100.00, 'threshold' => 75],
            ['category' => 'Technology', 'amount' => 250.00, 'threshold' => 80],
        ];

        foreach ($discretionaryBudgets as $budgetData) {
            Budget::factory()
                ->monthly()
                ->active()
                ->forUser($user)
                ->forCategory($budgetData['category'])
                ->create([
                    'amount' => $budgetData['amount'],
                    'currency' => 'MKD',
                    'alert_threshold' => $budgetData['threshold'],
                ]);
        }

        // Quarterly budgets
        $quarterlyBudgets = [
            ['category' => 'Clothing', 'amount' => 600.00],
            ['category' => 'Home & Garden', 'amount' => 800.00],
        ];

        foreach ($quarterlyBudgets as $budgetData) {
            Budget::factory()
                ->quarterly()
                ->active()
                ->forUser($user)
                ->forCategory($budgetData['category'])
                ->withRollover()
                ->create([
                    'amount' => $budgetData['amount'],
                    'currency' => 'MKD',
                    'alert_threshold' => 85,
                ]);
        }

        // Yearly budgets
        $yearlyBudgets = [
            ['category' => 'Travel', 'amount' => 3000.00, 'rollover' => true],
            ['category' => 'Education', 'amount' => 2000.00, 'rollover' => true],
            ['category' => 'Gifts & Donations', 'amount' => 1500.00, 'rollover' => false],
        ];

        foreach ($yearlyBudgets as $budgetData) {
            $factory = Budget::factory()
                ->yearly()
                ->active()
                ->forUser($user)
                ->forCategory($budgetData['category']);

            if ($budgetData['rollover']) {
                $factory = $factory->withRollover();
            }

            $factory->create([
                'amount' => $budgetData['amount'],
                'currency' => 'MKD',
                'alert_threshold' => 75,
                'notes' => 'Annual budget for ' . strtolower($budgetData['category']),
            ]);
        }

        // Custom period budget (vacation budget)
        $vacationStart = Carbon::now()->addMonths(2);
        $vacationEnd = Carbon::now()->addMonths(2)->addDays(14);

        Budget::factory()
            ->customPeriod($vacationStart, $vacationEnd)
            ->active()
            ->forUser($user)
            ->forCategory('Travel')
            ->create([
                'amount' => 1500.00,
                'currency' => 'EUR',
                'alert_threshold' => 90,
                'notes' => 'Summer vacation budget',
            ]);

        // Some inactive/expired budgets for history
        Budget::factory()
            ->count(3)
            ->inactive()
            ->forUser($user)
            ->create([
                'start_date' => Carbon::now()->subMonths(3)->toDateString(),
                'end_date' => Carbon::now()->subMonths(2)->toDateString(),
                'notes' => 'Previous budget period',
            ]);

        // Emergency fund budget (high threshold)
        Budget::factory()
            ->yearly()
            ->active()
            ->forUser($user)
            ->forCategory('Emergency Fund')
            ->highAlert()
            ->create([
                'amount' => 10000.00,
                'currency' => 'MKD',
                'alert_threshold' => 95,
                'notes' => 'Emergency fund - use only for real emergencies',
                'rollover_unused' => true,
            ]);
    }
}
