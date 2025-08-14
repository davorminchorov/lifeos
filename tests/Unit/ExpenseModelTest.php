<?php

namespace Tests\Unit;

use App\Models\Expense;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpenseModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_expense_has_fillable_attributes(): void
    {
        $fillable = [
            'user_id', 'amount', 'currency', 'category', 'subcategory', 'expense_date',
            'description', 'merchant', 'payment_method', 'receipt_attachments', 'tags',
            'location', 'is_tax_deductible', 'expense_type', 'is_recurring',
            'recurring_schedule', 'budget_allocated', 'notes', 'status'
        ];
        $expense = new Expense();

        $this->assertEquals($fillable, $expense->getFillable());
    }

    public function test_expense_casts_attributes_correctly(): void
    {
        $expense = new Expense();
        $casts = $expense->getCasts();

        $this->assertArrayHasKey('amount', $casts);
        $this->assertArrayHasKey('expense_date', $casts);
        $this->assertArrayHasKey('receipt_attachments', $casts);
        $this->assertArrayHasKey('tags', $casts);
        $this->assertArrayHasKey('is_tax_deductible', $casts);
        $this->assertArrayHasKey('is_recurring', $casts);
        $this->assertArrayHasKey('budget_allocated', $casts);

        $this->assertEquals('decimal:2', $casts['amount']);
        $this->assertEquals('date', $casts['expense_date']);
        $this->assertEquals('array', $casts['receipt_attachments']);
        $this->assertEquals('array', $casts['tags']);
        $this->assertEquals('boolean', $casts['is_tax_deductible']);
        $this->assertEquals('boolean', $casts['is_recurring']);
        $this->assertEquals('decimal:2', $casts['budget_allocated']);
    }

    public function test_expense_belongs_to_user(): void
    {
        $expense = new Expense();
        $relationship = $expense->user();

        $this->assertInstanceOf(BelongsTo::class, $relationship);
        $this->assertEquals('user_id', $relationship->getForeignKeyName());
    }

    public function test_scope_in_date_range(): void
    {
        $user = User::factory()->create();
        $startDate = now()->subDays(10);
        $endDate = now()->subDays(1);

        Expense::factory()->create(['user_id' => $user->id, 'expense_date' => $startDate->addDays(2)]);
        Expense::factory()->create(['user_id' => $user->id, 'expense_date' => now()->subDays(20)]);
        Expense::factory()->create(['user_id' => $user->id, 'expense_date' => now()]);

        $expenses = Expense::inDateRange($startDate, $endDate)->get();

        $this->assertCount(1, $expenses);
    }

    public function test_scope_by_category(): void
    {
        $user = User::factory()->create();
        Expense::factory()->create(['user_id' => $user->id, 'category' => 'food']);
        Expense::factory()->create(['user_id' => $user->id, 'category' => 'transport']);
        Expense::factory()->create(['user_id' => $user->id, 'category' => 'food']);

        $expenses = Expense::byCategory('food')->get();

        $this->assertCount(2, $expenses);
        $expenses->each(function ($expense) {
            $this->assertEquals('food', $expense->category);
        });
    }

    public function test_scope_business(): void
    {
        $user = User::factory()->create();
        Expense::factory()->create(['user_id' => $user->id, 'expense_type' => 'business']);
        Expense::factory()->create(['user_id' => $user->id, 'expense_type' => 'personal']);

        $expenses = Expense::business()->get();

        $this->assertCount(1, $expenses);
        $this->assertEquals('business', $expenses->first()->expense_type);
    }

    public function test_scope_personal(): void
    {
        $user = User::factory()->create();
        Expense::factory()->create(['user_id' => $user->id, 'expense_type' => 'business']);
        Expense::factory()->create(['user_id' => $user->id, 'expense_type' => 'personal']);

        $expenses = Expense::personal()->get();

        $this->assertCount(1, $expenses);
        $this->assertEquals('personal', $expenses->first()->expense_type);
    }

    public function test_scope_tax_deductible(): void
    {
        $user = User::factory()->create();
        Expense::factory()->create(['user_id' => $user->id, 'is_tax_deductible' => true]);
        Expense::factory()->create(['user_id' => $user->id, 'is_tax_deductible' => false]);

        $expenses = Expense::taxDeductible()->get();

        $this->assertCount(1, $expenses);
        $this->assertTrue($expenses->first()->is_tax_deductible);
    }

    public function test_scope_recurring(): void
    {
        $user = User::factory()->create();
        Expense::factory()->create(['user_id' => $user->id, 'is_recurring' => true]);
        Expense::factory()->create(['user_id' => $user->id, 'is_recurring' => false]);

        $expenses = Expense::recurring()->get();

        $this->assertCount(1, $expenses);
        $this->assertTrue($expenses->first()->is_recurring);
    }

    public function test_scope_current_month(): void
    {
        $user = User::factory()->create();
        Expense::factory()->create(['user_id' => $user->id, 'expense_date' => now()]);
        Expense::factory()->create(['user_id' => $user->id, 'expense_date' => now()->subMonth()]);

        $expenses = Expense::currentMonth()->get();

        $this->assertCount(1, $expenses);
    }

    public function test_scope_current_year(): void
    {
        $user = User::factory()->create();
        Expense::factory()->create(['user_id' => $user->id, 'expense_date' => now()]);
        Expense::factory()->create(['user_id' => $user->id, 'expense_date' => now()->subYear()]);

        $expenses = Expense::currentYear()->get();

        $this->assertCount(1, $expenses);
    }

    public function test_has_receipts_attribute(): void
    {
        $user = User::factory()->create();
        $expenseWithReceipts = Expense::factory()->create([
            'user_id' => $user->id,
            'receipt_attachments' => ['receipt1.jpg', 'receipt2.pdf']
        ]);
        $expenseWithoutReceipts = Expense::factory()->create([
            'user_id' => $user->id,
            'receipt_attachments' => []
        ]);

        $this->assertTrue($expenseWithReceipts->has_receipts);
        $this->assertFalse($expenseWithoutReceipts->has_receipts);
    }

    public function test_formatted_amount_attribute(): void
    {
        $user = User::factory()->create();
        $expense = Expense::factory()->create([
            'user_id' => $user->id,
            'amount' => 123.45,
            'currency' => 'USD'
        ]);

        $this->assertEquals('USD 123.45', $expense->formatted_amount);
    }

    public function test_is_over_budget_attribute(): void
    {
        $user = User::factory()->create();
        $overBudgetExpense = Expense::factory()->create([
            'user_id' => $user->id,
            'amount' => 150.00,
            'budget_allocated' => 100.00
        ]);
        $underBudgetExpense = Expense::factory()->create([
            'user_id' => $user->id,
            'amount' => 80.00,
            'budget_allocated' => 100.00
        ]);
        $noBudgetExpense = Expense::factory()->create([
            'user_id' => $user->id,
            'amount' => 50.00,
            'budget_allocated' => null
        ]);

        $this->assertTrue($overBudgetExpense->is_over_budget);
        $this->assertFalse($underBudgetExpense->is_over_budget);
        $this->assertFalse($noBudgetExpense->is_over_budget);
    }

    public function test_budget_variance_attribute(): void
    {
        $user = User::factory()->create();
        $expense = Expense::factory()->create([
            'user_id' => $user->id,
            'amount' => 120.00,
            'budget_allocated' => 100.00
        ]);
        $noBudgetExpense = Expense::factory()->create([
            'user_id' => $user->id,
            'amount' => 50.00,
            'budget_allocated' => null
        ]);

        $this->assertEquals(20.00, $expense->budget_variance);
        $this->assertNull($noBudgetExpense->budget_variance);
    }

    public function test_age_days_attribute(): void
    {
        $user = User::factory()->create();
        $expense = Expense::factory()->create([
            'user_id' => $user->id,
            'expense_date' => now()->subDays(5)
        ]);

        $this->assertEquals(5, $expense->age_days);
    }

    public function test_expense_factory_creates_valid_expense(): void
    {
        $expense = Expense::factory()->create();

        $this->assertInstanceOf(Expense::class, $expense);
        $this->assertNotNull($expense->user_id);
        $this->assertNotNull($expense->amount);
        $this->assertNotNull($expense->expense_date);
        $this->assertNotNull($expense->created_at);
        $this->assertNotNull($expense->updated_at);
    }
}
