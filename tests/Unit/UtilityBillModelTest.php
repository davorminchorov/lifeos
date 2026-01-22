<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\UtilityBill;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UtilityBillModelTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $tenant;

    protected function setUp(): void
    {
        parent::setUp();
        ['user' => $this->user, 'tenant' => $this->tenant] = $this->setupTenantContext();
    }

    public function test_utility_bill_has_fillable_attributes(): void
    {
        $fillable = [
            'tenant_id', 'user_id', 'utility_type', 'service_provider', 'account_number', 'service_address',
            'bill_amount', 'currency', 'usage_amount', 'usage_unit', 'rate_per_unit', 'bill_period_start',
            'bill_period_end', 'due_date', 'payment_status', 'payment_date', 'meter_readings',
            'bill_attachments', 'service_plan', 'contract_terms', 'auto_pay_enabled',
            'usage_history', 'budget_alert_threshold', 'notes',
        ];
        $bill = new UtilityBill;

        $this->assertEquals($fillable, $bill->getFillable());
    }

    public function test_utility_bill_casts_attributes_correctly(): void
    {
        $bill = new UtilityBill;
        $casts = $bill->getCasts();

        $this->assertArrayHasKey('bill_amount', $casts);
        $this->assertArrayHasKey('usage_amount', $casts);
        $this->assertArrayHasKey('rate_per_unit', $casts);
        $this->assertArrayHasKey('bill_period_start', $casts);
        $this->assertArrayHasKey('bill_period_end', $casts);
        $this->assertArrayHasKey('due_date', $casts);
        $this->assertArrayHasKey('payment_date', $casts);
        $this->assertArrayHasKey('meter_readings', $casts);
        $this->assertArrayHasKey('bill_attachments', $casts);
        $this->assertArrayHasKey('auto_pay_enabled', $casts);
        $this->assertArrayHasKey('usage_history', $casts);
        $this->assertArrayHasKey('budget_alert_threshold', $casts);

        $this->assertEquals('decimal:2', $casts['bill_amount']);
        $this->assertEquals('decimal:4', $casts['usage_amount']);
        $this->assertEquals('decimal:6', $casts['rate_per_unit']);
        $this->assertEquals('date', $casts['bill_period_start']);
        $this->assertEquals('date', $casts['bill_period_end']);
        $this->assertEquals('date', $casts['due_date']);
        $this->assertEquals('date', $casts['payment_date']);
        $this->assertEquals('array', $casts['meter_readings']);
        $this->assertEquals('array', $casts['bill_attachments']);
        $this->assertEquals('boolean', $casts['auto_pay_enabled']);
        $this->assertEquals('array', $casts['usage_history']);
        $this->assertEquals('decimal:2', $casts['budget_alert_threshold']);
    }

    public function test_utility_bill_belongs_to_user(): void
    {
        $bill = new UtilityBill;
        $relationship = $bill->user();

        $this->assertInstanceOf(BelongsTo::class, $relationship);
        $this->assertEquals('user_id', $relationship->getForeignKeyName());
    }

    public function test_scope_by_type(): void
    {
        UtilityBill::factory()->create(['user_id' => $this->user->id, 'tenant_id' => $this->tenant->id, 'utility_type' => 'electricity']);
        UtilityBill::factory()->create(['user_id' => $this->user->id, 'tenant_id' => $this->tenant->id, 'utility_type' => 'gas']);
        UtilityBill::factory()->create(['user_id' => $this->user->id, 'tenant_id' => $this->tenant->id, 'utility_type' => 'electricity']);

        $electricityBills = UtilityBill::byType('electricity')->get();

        $this->assertCount(2, $electricityBills);
        $electricityBills->each(function ($bill) {
            $this->assertEquals('electricity', $bill->utility_type);
        });
    }

    public function test_scope_pending(): void
    {
        UtilityBill::factory()->create(['user_id' => $this->user->id, 'tenant_id' => $this->tenant->id, 'payment_status' => 'pending']);
        UtilityBill::factory()->create(['user_id' => $this->user->id, 'tenant_id' => $this->tenant->id, 'payment_status' => 'paid']);
        UtilityBill::factory()->create(['user_id' => $this->user->id, 'tenant_id' => $this->tenant->id, 'payment_status' => 'pending']);

        $pendingBills = UtilityBill::pending()->get();

        $this->assertCount(2, $pendingBills);
        $pendingBills->each(function ($bill) {
            $this->assertEquals('pending', $bill->payment_status);
        });
    }

    public function test_scope_paid(): void
    {
        UtilityBill::factory()->create(['user_id' => $this->user->id, 'tenant_id' => $this->tenant->id, 'payment_status' => 'paid']);
        UtilityBill::factory()->create(['user_id' => $this->user->id, 'tenant_id' => $this->tenant->id, 'payment_status' => 'pending']);

        $paidBills = UtilityBill::paid()->get();

        $this->assertCount(1, $paidBills);
        $this->assertEquals('paid', $paidBills->first()->payment_status);
    }

    public function test_scope_overdue(): void
    {
        UtilityBill::factory()->create([
            'user_id' => $this->user->id,
            'tenant_id' => $this->tenant->id,
            'payment_status' => 'pending',
            'due_date' => now()->subDays(5),
        ]);
        UtilityBill::factory()->create([
            'user_id' => $this->user->id,
            'tenant_id' => $this->tenant->id,
            'payment_status' => 'overdue',
            'due_date' => now()->addDays(5),
        ]);
        UtilityBill::factory()->create([
            'user_id' => $this->user->id,
            'tenant_id' => $this->tenant->id,
            'payment_status' => 'pending',
            'due_date' => now()->addDays(5),
        ]);

        $overdueBills = UtilityBill::overdue()->get();

        $this->assertCount(2, $overdueBills);
    }

    public function test_scope_due_soon(): void
    {
        UtilityBill::factory()->create([
            'user_id' => $this->user->id,
            'tenant_id' => $this->tenant->id,
            'payment_status' => 'pending',
            'due_date' => now()->addDays(3),
        ]);
        UtilityBill::factory()->create([
            'user_id' => $this->user->id,
            'tenant_id' => $this->tenant->id,
            'payment_status' => 'pending',
            'due_date' => now()->addDays(10),
        ]);
        UtilityBill::factory()->create([
            'user_id' => $this->user->id,
            'tenant_id' => $this->tenant->id,
            'payment_status' => 'paid',
            'due_date' => now()->addDays(3),
        ]);

        $dueSoonBills = UtilityBill::dueSoon()->get();

        $this->assertCount(1, $dueSoonBills);
        $this->assertTrue($dueSoonBills->first()->due_date->lte(now()->addDays(7)));
        $this->assertEquals('pending', $dueSoonBills->first()->payment_status);
    }

    public function test_scope_current_month(): void
    {
        UtilityBill::factory()->create([
            'user_id' => $this->user->id,
            'tenant_id' => $this->tenant->id,
            'bill_period_start' => now(),
        ]);
        UtilityBill::factory()->create([
            'user_id' => $this->user->id,
            'tenant_id' => $this->tenant->id,
            'bill_period_start' => now()->subMonth(),
        ]);

        $currentMonthBills = UtilityBill::currentMonth()->get();

        $this->assertCount(1, $currentMonthBills);
    }

    public function test_is_overdue_attribute(): void
    {
        $overdueBill = UtilityBill::factory()->create([
            'user_id' => $this->user->id,
            'tenant_id' => $this->tenant->id,
            'payment_status' => 'pending',
            'due_date' => now()->subDays(1),
        ]);
        $notOverdueBill = UtilityBill::factory()->create([
            'user_id' => $this->user->id,
            'tenant_id' => $this->tenant->id,
            'payment_status' => 'pending',
            'due_date' => now()->addDays(1),
        ]);
        $paidBill = UtilityBill::factory()->create([
            'user_id' => $this->user->id,
            'tenant_id' => $this->tenant->id,
            'payment_status' => 'paid',
            'due_date' => now()->subDays(1),
        ]);

        $this->assertTrue($overdueBill->is_overdue);
        $this->assertFalse($notOverdueBill->is_overdue);
        $this->assertFalse($paidBill->is_overdue);
    }

    public function test_days_until_due_attribute(): void
    {
        $bill = UtilityBill::factory()->create([
            'user_id' => $this->user->id,
            'tenant_id' => $this->tenant->id,
            'due_date' => now()->addDays(5),
        ]);

        $this->assertEquals(5, $bill->days_until_due);
    }

    public function test_is_over_budget_attribute(): void
    {
        $overBudgetBill = UtilityBill::factory()->create([
            'user_id' => $this->user->id,
            'tenant_id' => $this->tenant->id,
            'bill_amount' => 150.00,
            'budget_alert_threshold' => 100.00,
        ]);
        $underBudgetBill = UtilityBill::factory()->create([
            'user_id' => $this->user->id,
            'tenant_id' => $this->tenant->id,
            'bill_amount' => 80.00,
            'budget_alert_threshold' => 100.00,
        ]);
        $noBudgetBill = UtilityBill::factory()->create([
            'user_id' => $this->user->id,
            'tenant_id' => $this->tenant->id,
            'bill_amount' => 50.00,
            'budget_alert_threshold' => null,
        ]);

        $this->assertTrue($overBudgetBill->is_over_budget);
        $this->assertFalse($underBudgetBill->is_over_budget);
        $this->assertFalse($noBudgetBill->is_over_budget);
    }

    public function test_cost_per_day_attribute(): void
    {
        $bill = UtilityBill::factory()->create([
            'user_id' => $this->user->id,
            'tenant_id' => $this->tenant->id,
            'bill_amount' => 100.00,
            'bill_period_start' => now()->subDays(30),
            'bill_period_end' => now(),
        ]);

        $expectedCostPerDay = 100.00 / 30;
        $this->assertEquals($expectedCostPerDay, $bill->cost_per_day);
    }

    public function test_usage_efficiency_attribute(): void
    {
        $bill = UtilityBill::factory()->create([
            'user_id' => $this->user->id,
            'tenant_id' => $this->tenant->id,
            'bill_amount' => 120.00,
            'usage_amount' => 1000.00,
        ]);
        $zeroUsageBill = UtilityBill::factory()->create([
            'user_id' => $this->user->id,
            'tenant_id' => $this->tenant->id,
            'bill_amount' => 120.00,
            'usage_amount' => 0,
        ]);

        $this->assertEquals(0.12, $bill->usage_efficiency);
        $this->assertNull($zeroUsageBill->usage_efficiency);
    }

    public function test_usage_comparison_attribute(): void
    {
        $billWithHistory = UtilityBill::factory()->create([
            'user_id' => $this->user->id,
            'tenant_id' => $this->tenant->id,
            'usage_amount' => 1100.00,
            'usage_history' => [1000.00, 900.00],
        ]);
        $billWithoutHistory = UtilityBill::factory()->create([
            'user_id' => $this->user->id,
            'tenant_id' => $this->tenant->id,
            'usage_amount' => 1100.00,
            'usage_history' => [],
        ]);

        // Should compare with the last entry (900.00)
        // (1100 - 900) / 900 * 100 = 22.22%
        $this->assertEquals(22.22, round($billWithHistory->usage_comparison, 2));
        $this->assertNull($billWithoutHistory->usage_comparison);
    }

    public function test_billing_period_days_attribute(): void
    {
        $bill = UtilityBill::factory()->create([
            'user_id' => $this->user->id,
            'bill_period_start' => now()->subDays(30),
            'bill_period_end' => now(),
            'tenant_id' => $this->tenant->id,
        ]);

        $this->assertEquals(30, $bill->billing_period_days);
    }

    public function test_utility_bill_factory_creates_valid_bill(): void
    {
        $bill = UtilityBill::factory()->create(['tenant_id' => $this->tenant->id]);

        $this->assertInstanceOf(UtilityBill::class, $bill);
        $this->assertNotNull($bill->user_id);
        $this->assertNotNull($bill->utility_type);
        $this->assertNotNull($bill->bill_amount);
        $this->assertNotNull($bill->due_date);
        $this->assertNotNull($bill->created_at);
        $this->assertNotNull($bill->updated_at);
    }
}
