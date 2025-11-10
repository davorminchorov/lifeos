<?php

namespace Tests\Unit;

use App\Models\Budget;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class BudgetModelTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Budget $budget;

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake();

        $this->user = User::factory()->create();
        $this->budget = Budget::factory()->create([
            'user_id' => $this->user->id,
            'category' => 'groceries',
            'budget_period' => 'monthly',
            'amount' => 500.00,
            'currency' => 'USD',
            'start_date' => now()->startOfMonth(),
            'end_date' => now()->endOfMonth(),
            'is_active' => true,
            'rollover_unused' => false,
            'alert_threshold' => 80,
        ]);
    }

    public function test_budget_belongs_to_user(): void
    {
        $this->assertInstanceOf(User::class, $this->budget->user);
        $this->assertEquals($this->user->id, $this->budget->user->id);
    }

    public function test_get_current_spending_returns_zero_when_no_expenses(): void
    {
        $spending = $this->budget->getCurrentSpending();

        $this->assertEquals(0, $spending);
    }

    public function test_get_current_spending_calculates_category_expenses_within_period(): void
    {
        // Create expenses within budget period
        Expense::factory()->create([
            'user_id' => $this->user->id,
            'category' => 'groceries',
            'amount' => 100.00,
            'expense_date' => now(),
        ]);

        Expense::factory()->create([
            'user_id' => $this->user->id,
            'category' => 'groceries',
            'amount' => 150.00,
            'expense_date' => now(),
        ]);

        // Create expense in different category (should not count)
        Expense::factory()->create([
            'user_id' => $this->user->id,
            'category' => 'entertainment',
            'amount' => 50.00,
            'expense_date' => now(),
        ]);

        // Create expense outside period (should not count)
        Expense::factory()->create([
            'user_id' => $this->user->id,
            'category' => 'groceries',
            'amount' => 75.00,
            'expense_date' => now()->subMonths(2),
        ]);

        $spending = $this->budget->getCurrentSpending();

        $this->assertEquals(250.00, $spending);
    }

    public function test_get_remaining_amount_returns_correct_value(): void
    {
        Expense::factory()->create([
            'user_id' => $this->user->id,
            'category' => 'groceries',
            'amount' => 200.00,
            'expense_date' => now(),
        ]);

        $remaining = $this->budget->getRemainingAmount();

        $this->assertEquals(300.00, $remaining);
    }

    public function test_get_remaining_amount_returns_zero_when_over_budget(): void
    {
        Expense::factory()->create([
            'user_id' => $this->user->id,
            'category' => 'groceries',
            'amount' => 600.00,
            'expense_date' => now(),
        ]);

        $remaining = $this->budget->getRemainingAmount();

        $this->assertEquals(0, $remaining);
    }

    public function test_get_utilization_percentage_returns_correct_value(): void
    {
        Expense::factory()->create([
            'user_id' => $this->user->id,
            'category' => 'groceries',
            'amount' => 250.00,
            'expense_date' => now(),
        ]);

        $utilization = $this->budget->getUtilizationPercentage();

        $this->assertEquals(50.00, $utilization);
    }

    public function test_get_utilization_percentage_returns_zero_when_budget_is_zero(): void
    {
        $this->budget->update(['amount' => 0]);

        $utilization = $this->budget->getUtilizationPercentage();

        $this->assertEquals(0, $utilization);
    }

    public function test_is_over_threshold_returns_false_when_below_threshold(): void
    {
        Expense::factory()->create([
            'user_id' => $this->user->id,
            'category' => 'groceries',
            'amount' => 300.00, // 60% of budget
            'expense_date' => now(),
        ]);

        $this->assertFalse($this->budget->isOverThreshold());
    }

    public function test_is_over_threshold_returns_true_when_above_threshold(): void
    {
        Expense::factory()->create([
            'user_id' => $this->user->id,
            'category' => 'groceries',
            'amount' => 450.00, // 90% of budget
            'expense_date' => now(),
        ]);

        $this->assertTrue($this->budget->isOverThreshold());
    }

    public function test_is_exceeded_returns_false_when_within_budget(): void
    {
        Expense::factory()->create([
            'user_id' => $this->user->id,
            'category' => 'groceries',
            'amount' => 400.00,
            'expense_date' => now(),
        ]);

        $this->assertFalse($this->budget->isExceeded());
    }

    public function test_is_exceeded_returns_true_when_over_budget(): void
    {
        Expense::factory()->create([
            'user_id' => $this->user->id,
            'category' => 'groceries',
            'amount' => 600.00,
            'expense_date' => now(),
        ]);

        $this->assertTrue($this->budget->isExceeded());
    }

    public function test_get_status_returns_on_track_when_below_threshold(): void
    {
        Expense::factory()->create([
            'user_id' => $this->user->id,
            'category' => 'groceries',
            'amount' => 200.00, // 40% of budget
            'expense_date' => now(),
        ]);

        $status = $this->budget->getStatus();

        $this->assertEquals('on_track', $status);
    }

    public function test_get_status_returns_warning_when_over_threshold(): void
    {
        Expense::factory()->create([
            'user_id' => $this->user->id,
            'category' => 'groceries',
            'amount' => 425.00, // 85% of budget
            'expense_date' => now(),
        ]);

        $status = $this->budget->getStatus();

        $this->assertEquals('warning', $status);
    }

    public function test_get_status_returns_exceeded_when_over_budget(): void
    {
        Expense::factory()->create([
            'user_id' => $this->user->id,
            'category' => 'groceries',
            'amount' => 550.00, // 110% of budget
            'expense_date' => now(),
        ]);

        $status = $this->budget->getStatus();

        $this->assertEquals('exceeded', $status);
    }

    public function test_scope_active_filters_active_budgets(): void
    {
        Budget::factory()->create([
            'user_id' => $this->user->id,
            'is_active' => false,
        ]);

        $activeBudgets = Budget::active()->get();

        $this->assertCount(1, $activeBudgets);
        $this->assertTrue($activeBudgets->first()->is_active);
    }

    public function test_scope_current_filters_budgets_in_current_period(): void
    {
        // Past budget
        Budget::factory()->create([
            'user_id' => $this->user->id,
            'start_date' => now()->subMonths(2),
            'end_date' => now()->subMonths(1),
        ]);

        // Future budget
        Budget::factory()->create([
            'user_id' => $this->user->id,
            'start_date' => now()->addMonths(1),
            'end_date' => now()->addMonths(2),
        ]);

        $currentBudgets = Budget::current()->get();

        $this->assertCount(1, $currentBudgets);
        $this->assertEquals($this->budget->id, $currentBudgets->first()->id);
    }

    public function test_scope_for_category_filters_by_category(): void
    {
        Budget::factory()->create([
            'user_id' => $this->user->id,
            'category' => 'entertainment',
        ]);

        Budget::factory()->create([
            'user_id' => $this->user->id,
            'category' => 'utilities',
        ]);

        $groceryBudgets = Budget::forCategory('groceries')->get();

        $this->assertCount(1, $groceryBudgets);
        $this->assertEquals('groceries', $groceryBudgets->first()->category);
    }

    public function test_budget_casts_attributes_correctly(): void
    {
        $this->assertIsString($this->budget->amount);
        $this->assertIsInt($this->budget->alert_threshold);
        $this->assertIsBool($this->budget->is_active);
        $this->assertIsBool($this->budget->rollover_unused);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $this->budget->start_date);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $this->budget->end_date);
    }
}
