<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\User;
use App\Models\UtilityBill;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UtilityBillPaidCreatesExpenseTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_expense_when_bill_marked_paid(): void
    {
        $user = User::factory()->create();

        $bill = UtilityBill::factory()->create([
            'user_id' => $user->id,
            'payment_status' => 'pending',
            'bill_amount' => 345.67,
            'currency' => 'MKD',
            'utility_type' => 'electricity',
            'service_provider' => 'EVN',
            'payment_date' => null,
        ]);

        // Mark as paid -> triggers observer
        $bill->update([
            'payment_status' => 'paid',
            'payment_date' => now()->toDateString(),
        ]);

        $this->assertDatabaseHas('expenses', [
            'user_id' => $user->id,
            'amount' => 345.67,
            'currency' => 'MKD',
            'category' => 'utilities',
            'expense_date' => now()->toDateString(),
            'status' => 'confirmed',
        ]);

        $expense = Expense::first();
        $this->assertTrue(in_array('utility-bill:'.$bill->id, $expense->tags ?? []));
    }

    public function test_observer_is_idempotent_for_repeated_updates(): void
    {
        $user = User::factory()->create();

        $bill = UtilityBill::factory()->create([
            'user_id' => $user->id,
            'payment_status' => 'pending',
            'bill_amount' => 100,
            'currency' => 'MKD',
            'utility_type' => 'water',
            'service_provider' => 'Vodovod',
            'payment_date' => null,
        ]);

        $bill->update(['payment_status' => 'paid', 'payment_date' => now()->toDateString()]);
        $bill->update(['notes' => 'second update']);
        $bill->update(['payment_date' => now()->toDateString()]);

        $this->assertEquals(1, Expense::whereJsonContains('tags', 'utility-bill:'.$bill->id)
            ->whereDate('expense_date', now()->toDateString())
            ->count());
    }
}
