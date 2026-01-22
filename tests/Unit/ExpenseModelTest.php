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

    protected $user;
    protected $tenant;

    protected function setUp(): void
    {
        parent::setUp();
        ['user' => $this->user, 'tenant' => $this->tenant] = $this->setupTenantContext();
    }

    public function test_expense_has_fillable_attributes(): void
    {
        $fillable = [
            'tenant_id', 'user_id', 'amount', 'currency', 'category', 'subcategory', 'expense_date',
            'description', 'merchant', 'payment_method', 'receipt_attachments', 'tags',
            'location', 'is_tax_deductible', 'expense_type', 'is_recurring',
            'recurring_schedule', 'budget_allocated', 'notes', 'status', 'unique_key',
        ];
        $expense = new Expense;

        $this->assertEquals($fillable, $expense->getFillable());
    }

    public function test_expense_casts_attributes_correctly(): void
    {
        $expense = new Expense;
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
        $expense = new Expense;
        $relationship = $expense->user();

        $this->assertInstanceOf(BelongsTo::class, $relationship);
        $this->assertEquals('user_id', $relationship->getForeignKeyName());
    }

    public function test_scope_in_date_range(): void
    {
        $startDate = now()->subDays(10);
        $endDate = now()->subDays(1);

        Expense::factory()->create(['user_id' => $this->user->id, 'expense_date' => now()->subDays(8), 'tenant_id' => $this->tenant->id]);
        Expense::factory()->create(['user_id' => $this->user->id, 'expense_date' => now()->subDays(20), 'tenant_id' => $this->tenant->id]);
        Expense::factory()->create(['user_id' => $this->user->id, 'expense_date' => now(), 'tenant_id' => $this->tenant->id]);

        $expenses = Expense::inDateRange($startDate, $endDate)->get();

        $this->assertCount(1, $expenses);
    }

    public function test_scope_by_category(): void
    {
        Expense::factory()->create(['user_id' => $this->user->id, 'category' => 'food', 'tenant_id' => $this->tenant->id]);
        Expense::factory()->create(['user_id' => $this->user->id, 'category' => 'transport', 'tenant_id' => $this->tenant->id]);
        Expense::factory()->create(['user_id' => $this->user->id, 'category' => 'food', 'tenant_id' => $this->tenant->id]);

        $expenses = Expense::byCategory('food')->get();

        $this->assertCount(2, $expenses);
        $expenses->each(function ($expense) {
            $this->assertEquals('food', $expense->category);
        });
    }

    public function test_scope_business(): void
    {
        Expense::factory()->create(['user_id' => $this->user->id, 'expense_type' => 'business', 'tenant_id' => $this->tenant->id]);
        Expense::factory()->create(['user_id' => $this->user->id, 'expense_type' => 'personal', 'tenant_id' => $this->tenant->id]);

        $expenses = Expense::business()->get();

        $this->assertCount(1, $expenses);
        $this->assertEquals('business', $expenses->first()->expense_type);
    }

    public function test_scope_personal(): void
    {
        Expense::factory()->create(['user_id' => $this->user->id, 'expense_type' => 'business', 'tenant_id' => $this->tenant->id]);
        Expense::factory()->create(['user_id' => $this->user->id, 'expense_type' => 'personal', 'tenant_id' => $this->tenant->id]);

        $expenses = Expense::personal()->get();

        $this->assertCount(1, $expenses);
        $this->assertEquals('personal', $expenses->first()->expense_type);
    }

    public function test_scope_tax_deductible(): void
    {
        Expense::factory()->create(['user_id' => $this->user->id, 'is_tax_deductible' => true, 'tenant_id' => $this->tenant->id]);
        Expense::factory()->create(['user_id' => $this->user->id, 'is_tax_deductible' => false, 'tenant_id' => $this->tenant->id]);

        $expenses = Expense::taxDeductible()->get();

        $this->assertCount(1, $expenses);
        $this->assertTrue($expenses->first()->is_tax_deductible);
    }

    public function test_scope_recurring(): void
    {
        Expense::factory()->create(['user_id' => $this->user->id, 'is_recurring' => true, 'tenant_id' => $this->tenant->id]);
        Expense::factory()->create(['user_id' => $this->user->id, 'is_recurring' => false, 'tenant_id' => $this->tenant->id]);

        $expenses = Expense::recurring()->get();

        $this->assertCount(1, $expenses);
        $this->assertTrue($expenses->first()->is_recurring);
    }

    public function test_scope_current_month(): void
    {
        Expense::factory()->create(['user_id' => $this->user->id, 'expense_date' => now(), 'tenant_id' => $this->tenant->id]);
        Expense::factory()->create(['user_id' => $this->user->id, 'expense_date' => now()->subMonth(), 'tenant_id' => $this->tenant->id]);

        $expenses = Expense::currentMonth()->get();

        $this->assertCount(1, $expenses);
    }

    public function test_scope_current_year(): void
    {
        Expense::factory()->create(['user_id' => $this->user->id, 'expense_date' => now(), 'tenant_id' => $this->tenant->id]);
        Expense::factory()->create(['user_id' => $this->user->id, 'expense_date' => now()->subYear(), 'tenant_id' => $this->tenant->id]);

        $expenses = Expense::currentYear()->get();

        $this->assertCount(1, $expenses);
    }

    public function test_has_receipts_attribute(): void
    {
        $expenseWithReceipts = Expense::factory()->create([
            'user_id' => $this->user->id,
            'receipt_attachments' => ['receipt1.jpg', 'receipt2.pdf'],
            'tenant_id' => $this->tenant->id,
        ]);
        $expenseWithoutReceipts = Expense::factory()->create([
            'user_id' => $this->user->id,
            'receipt_attachments' => [],
            'tenant_id' => $this->tenant->id,
        ]);

        $this->assertTrue($expenseWithReceipts->has_receipts);
        $this->assertFalse($expenseWithoutReceipts->has_receipts);
    }

    public function test_formatted_amount_attribute(): void
    {
        $expense = Expense::factory()->create([
            'user_id' => $this->user->id,
            'amount' => 123.45,
            'currency' => 'USD',
            'tenant_id' => $this->tenant->id,
        ]);

        $this->assertEquals('$ 123.45 (USD)', $expense->formatted_amount);
    }

    public function test_is_over_budget_attribute(): void
    {
        $overBudgetExpense = Expense::factory()->create([
            'user_id' => $this->user->id,
            'amount' => 150.00,
            'budget_allocated' => 100.00,
            'tenant_id' => $this->tenant->id,
        ]);
        $underBudgetExpense = Expense::factory()->create([
            'user_id' => $this->user->id,
            'amount' => 80.00,
            'budget_allocated' => 100.00,
            'tenant_id' => $this->tenant->id,
        ]);
        $noBudgetExpense = Expense::factory()->create([
            'user_id' => $this->user->id,
            'amount' => 50.00,
            'budget_allocated' => null,
            'tenant_id' => $this->tenant->id,
        ]);

        $this->assertTrue($overBudgetExpense->is_over_budget);
        $this->assertFalse($underBudgetExpense->is_over_budget);
        $this->assertFalse($noBudgetExpense->is_over_budget);
    }

    public function test_budget_variance_attribute(): void
    {
        $expense = Expense::factory()->create([
            'user_id' => $this->user->id,
            'amount' => 120.00,
            'budget_allocated' => 100.00,
            'tenant_id' => $this->tenant->id,
        ]);
        $noBudgetExpense = Expense::factory()->create([
            'user_id' => $this->user->id,
            'amount' => 50.00,
            'budget_allocated' => null,
            'tenant_id' => $this->tenant->id,
        ]);

        $this->assertEquals(20.00, $expense->budget_variance);
        $this->assertNull($noBudgetExpense->budget_variance);
    }

    public function test_age_days_attribute(): void
    {
        $expense = Expense::factory()->create([
            'user_id' => $this->user->id,
            'expense_date' => now()->subDays(5),
            'tenant_id' => $this->tenant->id,
        ]);

        $this->assertEquals(5, $expense->age_days);
    }

    public function test_expense_factory_creates_valid_expense(): void
    {
        $expense = Expense::factory()->create(['tenant_id' => $this->tenant->id]);

        $this->assertInstanceOf(Expense::class, $expense);
        $this->assertNotNull($expense->user_id);
        $this->assertNotNull($expense->amount);
        $this->assertNotNull($expense->expense_date);
        $this->assertNotNull($expense->created_at);
        $this->assertNotNull($expense->updated_at);
    }
}
