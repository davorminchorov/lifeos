<?php

namespace Tests\Unit;

use App\Models\Iou;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IouModelTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $tenant;

    protected function setUp(): void
    {
        parent::setUp();
        ['user' => $this->user, 'tenant' => $this->tenant] = $this->setupTenantContext();
    }

    public function test_iou_has_fillable_attributes(): void
    {
        $fillable = [
            'tenant_id', 'user_id', 'type', 'person_name', 'amount', 'currency',
            'transaction_date', 'due_date', 'description', 'notes', 'status',
            'amount_paid', 'payment_method', 'category', 'attachments',
            'is_recurring', 'recurring_schedule',
        ];
        $iou = new Iou;

        $this->assertEquals($fillable, $iou->getFillable());
    }

    public function test_iou_belongs_to_user(): void
    {
        $iou = Iou::factory()->create(['user_id' => $this->user->id]);

        $this->assertInstanceOf(User::class, $iou->user);
        $this->assertEquals($this->user->id, $iou->user->id);
    }

    public function test_scope_owe_filters_owe_type(): void
    {
        Iou::factory()->create(['user_id' => $this->user->id, 'tenant_id' => $this->tenant->id, 'type' => 'owe']);
        Iou::factory()->create(['user_id' => $this->user->id, 'tenant_id' => $this->tenant->id, 'type' => 'owed']);

        $oweIous = Iou::owe()->get();

        $this->assertCount(1, $oweIous);
        $this->assertEquals('owe', $oweIous->first()->type);
    }

    public function test_scope_owed_filters_owed_type(): void
    {
        Iou::factory()->create(['user_id' => $this->user->id, 'tenant_id' => $this->tenant->id, 'type' => 'owe']);
        Iou::factory()->create(['user_id' => $this->user->id, 'tenant_id' => $this->tenant->id, 'type' => 'owed']);

        $owedIous = Iou::owed()->get();

        $this->assertCount(1, $owedIous);
        $this->assertEquals('owed', $owedIous->first()->type);
    }

    public function test_scope_pending_filters_pending_status(): void
    {
        Iou::factory()->create(['user_id' => $this->user->id, 'tenant_id' => $this->tenant->id, 'status' => 'pending']);
        Iou::factory()->create(['user_id' => $this->user->id, 'tenant_id' => $this->tenant->id, 'status' => 'paid']);

        $pendingIous = Iou::pending()->get();

        $this->assertCount(1, $pendingIous);
        $this->assertEquals('pending', $pendingIous->first()->status);
    }

    public function test_scope_paid_filters_paid_status(): void
    {
        Iou::factory()->create(['user_id' => $this->user->id, 'tenant_id' => $this->tenant->id, 'status' => 'pending']);
        Iou::factory()->create(['user_id' => $this->user->id, 'tenant_id' => $this->tenant->id, 'status' => 'paid']);

        $paidIous = Iou::paid()->get();

        $this->assertCount(1, $paidIous);
        $this->assertEquals('paid', $paidIous->first()->status);
    }

    public function test_scope_overdue_filters_overdue_ious(): void
    {
        // Overdue pending
        Iou::factory()->create([
            'user_id' => $this->user->id,
            'due_date' => now()->subDays(5),
            'status' => 'pending',
        ]);

        // Overdue partially paid
        Iou::factory()->create([
            'user_id' => $this->user->id,
            'due_date' => now()->subDays(3),
            'status' => 'partially_paid',
        ]);

        // Not overdue (future date)
        Iou::factory()->create([
            'user_id' => $this->user->id,
            'due_date' => now()->addDays(5),
            'status' => 'pending',
        ]);

        // Paid (should not be included even if past due)
        Iou::factory()->create([
            'user_id' => $this->user->id,
            'due_date' => now()->subDays(10),
            'status' => 'paid',
        ]);

        $overdueIous = Iou::overdue()->get();

        $this->assertCount(2, $overdueIous);
    }

    public function test_scope_by_person_filters_by_person_name(): void
    {
        Iou::factory()->create(['user_id' => $this->user->id, 'tenant_id' => $this->tenant->id, 'person_name' => 'John Doe']);
        Iou::factory()->create(['user_id' => $this->user->id, 'tenant_id' => $this->tenant->id, 'person_name' => 'Jane Smith']);

        $johnIous = Iou::byPerson('John Doe')->get();

        $this->assertCount(1, $johnIous);
        $this->assertEquals('John Doe', $johnIous->first()->person_name);
    }

    public function test_remaining_balance_calculates_correctly(): void
    {
        $iou = Iou::factory()->create([
            'user_id' => $this->user->id,
            'amount' => 1000.00,
            'amount_paid' => 400.00,
        ]);

        $this->assertEquals(600.00, $iou->remaining_balance);
    }

    public function test_remaining_balance_handles_zero_amount_paid(): void
    {
        $iou = Iou::factory()->create([
            'user_id' => $this->user->id,
            'amount' => 1000.00,
            'amount_paid' => 0,
        ]);

        $this->assertEquals(1000.00, $iou->remaining_balance);
    }

    public function test_remaining_amount_is_alias_for_remaining_balance(): void
    {
        $iou = Iou::factory()->create([
            'user_id' => $this->user->id,
            'amount' => 1000.00,
            'amount_paid' => 300.00,
        ]);

        $this->assertEquals($iou->remaining_balance, $iou->remaining_amount);
        $this->assertEquals(700.00, $iou->remaining_amount);
    }

    public function test_is_overdue_returns_true_for_past_due_pending(): void
    {
        $iou = Iou::factory()->create([
            'user_id' => $this->user->id,
            'due_date' => now()->subDays(5),
            'status' => 'pending',
        ]);

        $this->assertTrue($iou->is_overdue);
    }

    public function test_is_overdue_returns_true_for_past_due_partially_paid(): void
    {
        $iou = Iou::factory()->create([
            'user_id' => $this->user->id,
            'due_date' => now()->subDays(3),
            'status' => 'partially_paid',
        ]);

        $this->assertTrue($iou->is_overdue);
    }

    public function test_is_overdue_returns_false_for_future_date(): void
    {
        $iou = Iou::factory()->create([
            'user_id' => $this->user->id,
            'due_date' => now()->addDays(5),
            'status' => 'pending',
        ]);

        $this->assertFalse($iou->is_overdue);
    }

    public function test_is_overdue_returns_false_for_paid_status(): void
    {
        $iou = Iou::factory()->create([
            'user_id' => $this->user->id,
            'due_date' => now()->subDays(10),
            'status' => 'paid',
        ]);

        $this->assertFalse($iou->is_overdue);
    }

    public function test_is_fully_paid_returns_true_when_paid_equals_amount(): void
    {
        $iou = Iou::factory()->create([
            'user_id' => $this->user->id,
            'amount' => 1000.00,
            'amount_paid' => 1000.00,
        ]);

        $this->assertTrue($iou->is_fully_paid);
    }

    public function test_is_fully_paid_returns_true_when_paid_exceeds_amount(): void
    {
        $iou = Iou::factory()->create([
            'user_id' => $this->user->id,
            'amount' => 1000.00,
            'amount_paid' => 1100.00,
        ]);

        $this->assertTrue($iou->is_fully_paid);
    }

    public function test_is_fully_paid_returns_false_when_partially_paid(): void
    {
        $iou = Iou::factory()->create([
            'user_id' => $this->user->id,
            'amount' => 1000.00,
            'amount_paid' => 500.00,
        ]);

        $this->assertFalse($iou->is_fully_paid);
    }

    public function test_payment_progress_calculates_percentage_correctly(): void
    {
        $iou = Iou::factory()->create([
            'user_id' => $this->user->id,
            'amount' => 1000.00,
            'amount_paid' => 750.00,
        ]);

        $this->assertEquals(75.00, $iou->payment_progress);
    }

    public function test_payment_progress_returns_zero_when_amount_is_zero(): void
    {
        $iou = Iou::factory()->create([
            'user_id' => $this->user->id,
            'amount' => 0,
            'amount_paid' => 0,
        ]);

        $this->assertEquals(0, $iou->payment_progress);
    }

    public function test_payment_progress_caps_at_100_percent(): void
    {
        $iou = Iou::factory()->create([
            'user_id' => $this->user->id,
            'amount' => 1000.00,
            'amount_paid' => 1500.00,
        ]);

        $this->assertEquals(100, $iou->payment_progress);
    }

    public function test_payment_percentage_is_alias_for_payment_progress(): void
    {
        $iou = Iou::factory()->create([
            'user_id' => $this->user->id,
            'amount' => 1000.00,
            'amount_paid' => 600.00,
        ]);

        $this->assertEquals($iou->payment_progress, $iou->payment_percentage);
        $this->assertEquals(60.00, $iou->payment_percentage);
    }

    public function test_days_until_due_calculates_positive_days_for_future_date(): void
    {
        $iou = Iou::factory()->create([
            'user_id' => $this->user->id,
            'due_date' => now()->addDays(10),
        ]);

        $this->assertGreaterThanOrEqual(9, $iou->days_until_due);
        $this->assertLessThanOrEqual(10, $iou->days_until_due);
    }

    public function test_days_until_due_calculates_negative_days_for_past_date(): void
    {
        $iou = Iou::factory()->create([
            'user_id' => $this->user->id,
            'due_date' => now()->subDays(5),
        ]);

        $this->assertLessThanOrEqual(-4, $iou->days_until_due);
    }

    public function test_days_until_due_returns_null_when_no_due_date(): void
    {
        $iou = Iou::factory()->create([
            'user_id' => $this->user->id,
            'due_date' => null,
        ]);

        $this->assertNull($iou->days_until_due);
    }

    public function test_status_color_returns_correct_color_for_pending(): void
    {
        $iou = Iou::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'pending',
            'due_date' => now()->addDays(5),
        ]);

        $this->assertEquals('yellow', $iou->status_color);
    }

    public function test_status_color_returns_red_for_overdue_pending(): void
    {
        $iou = Iou::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'pending',
            'due_date' => now()->subDays(5),
        ]);

        $this->assertEquals('red', $iou->status_color);
    }

    public function test_status_color_returns_blue_for_partially_paid(): void
    {
        $iou = Iou::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'partially_paid',
        ]);

        $this->assertEquals('blue', $iou->status_color);
    }

    public function test_status_color_returns_green_for_paid(): void
    {
        $iou = Iou::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'paid',
        ]);

        $this->assertEquals('green', $iou->status_color);
    }

    public function test_status_color_returns_gray_for_cancelled(): void
    {
        $iou = Iou::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'cancelled',
        ]);

        $this->assertEquals('gray', $iou->status_color);
    }

    public function test_type_label_returns_correct_label_for_owe(): void
    {
        $iou = Iou::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'owe',
        ]);

        $this->assertEquals('I Owe', $iou->type_label);
    }

    public function test_type_label_returns_correct_label_for_owed(): void
    {
        $iou = Iou::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'owed',
        ]);

        $this->assertEquals('Owed to Me', $iou->type_label);
    }

    public function test_iou_casts_attributes_correctly(): void
    {
        $iou = Iou::factory()->create([
            'user_id' => $this->user->id,
            'amount' => 1000.00,
            'amount_paid' => 500.00,
            'transaction_date' => '2024-01-01',
            'due_date' => '2024-12-31',
            'attachments' => ['file1.pdf', 'file2.jpg'],
            'is_recurring' => true,
        ]);

        $this->assertIsString($iou->amount);
        $this->assertIsString($iou->amount_paid);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $iou->transaction_date);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $iou->due_date);
        $this->assertIsArray($iou->attachments);
        $this->assertIsBool($iou->is_recurring);
    }
}
