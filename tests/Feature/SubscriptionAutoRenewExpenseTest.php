<?php

namespace Tests\Feature;

use App\Jobs\CreateSubscriptionAutoRenewExpenses;
use App\Models\Expense;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionAutoRenewExpenseTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_expense_for_auto_renewed_subscription_due_today(): void
    {
        $user = User::factory()->create();

        $subscription = Subscription::factory()->create([
            'user_id' => $user->id,
            'status' => 'active',
            'auto_renewal' => true,
            'next_billing_date' => now()->toDateString(),
            'cost' => 999.99,
            'currency' => 'MKD',
            'service_name' => 'Test Service',
            'category' => 'Entertainment',
        ]);

        // Run the job synchronously
        (new CreateSubscriptionAutoRenewExpenses)->handle();

        $this->assertDatabaseHas('expenses', [
            'user_id' => $user->id,
            'amount' => 999.99,
            'currency' => 'MKD',
            'category' => 'Entertainment',
            'expense_date' => now()->toDateString(),
            'status' => 'confirmed',
        ]);

        $expense = Expense::first();
        $this->assertTrue(in_array('subscription:'.$subscription->id, $expense->tags ?? []));
    }

    public function test_job_is_idempotent_on_same_day(): void
    {
        $user = User::factory()->create();

        $subscription = Subscription::factory()->create([
            'user_id' => $user->id,
            'status' => 'active',
            'auto_renewal' => true,
            'next_billing_date' => now()->toDateString(),
            'cost' => 120.00,
            'currency' => 'MKD',
            'service_name' => 'Idempotent Service',
            'category' => 'Software',
        ]);

        (new CreateSubscriptionAutoRenewExpenses)->handle();
        (new CreateSubscriptionAutoRenewExpenses)->handle();

        $this->assertEquals(1, Expense::whereJsonContains('tags', 'subscription:'.$subscription->id)
            ->whereDate('expense_date', now()->toDateString())
            ->count());
    }
}
