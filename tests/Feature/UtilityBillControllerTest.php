<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UtilityBill;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UtilityBillControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_can_display_utility_bills_index(): void
    {
        UtilityBill::factory()->count(3)->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->get(route('utility-bills.index'));

        $response->assertStatus(200);
        $response->assertViewIs('utility-bills.index');
    }

    public function test_can_search_utility_bills(): void
    {
        UtilityBill::factory()->create([
            'user_id' => $this->user->id,
            'service_provider' => 'Electric Company'
        ]);

        UtilityBill::factory()->create([
            'user_id' => $this->user->id,
            'service_provider' => 'Gas Company'
        ]);

        $response = $this->actingAs($this->user)->get(route('utility-bills.index', ['search' => 'Electric']));

        $response->assertStatus(200);
        $response->assertSee('Electric Company');
        $response->assertDontSee('Gas Company');
    }

    public function test_can_filter_bills_by_payment_status(): void
    {
        UtilityBill::factory()->create([
            'user_id' => $this->user->id,
            'payment_status' => 'paid'
        ]);

        UtilityBill::factory()->create([
            'user_id' => $this->user->id,
            'payment_status' => 'pending'
        ]);

        $response = $this->actingAs($this->user)->get(route('utility-bills.index', ['status' => 'paid']));

        $response->assertStatus(200);
    }

    public function test_can_display_create_utility_bill_form(): void
    {
        $response = $this->actingAs($this->user)->get(route('utility-bills.create'));

        $response->assertStatus(200);
        $response->assertViewIs('utility-bills.create');
    }

    public function test_can_store_new_utility_bill(): void
    {
        $billData = [
            'service_provider' => $this->faker->company,
            'utility_type' => 'electricity',
            'bill_amount' => $this->faker->randomFloat(2, 50, 500),
            'currency' => 'MKD',
            'bill_period_start' => now()->subMonth()->format('Y-m-d'),
            'bill_period_end' => now()->format('Y-m-d'),
            'due_date' => now()->addWeeks(2)->format('Y-m-d'),
            'payment_status' => 'pending',
            'account_number' => $this->faker->numerify('##########'),
            'service_address' => $this->faker->address(),
        ];

        $response = $this->actingAs($this->user)->post(route('utility-bills.store'), $billData);

        $response->assertRedirect(route('utility-bills.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('utility_bills', [
            'user_id' => $this->user->id,
            'service_provider' => $billData['service_provider'],
            'bill_amount' => $billData['bill_amount']
        ]);
    }

    public function test_store_utility_bill_validates_required_fields(): void
    {
        $response = $this->actingAs($this->user)->post(route('utility-bills.store'), []);

        $response->assertSessionHasErrors([
            'service_provider',
            'utility_type',
            'bill_amount',
            'due_date'
        ]);
    }

    public function test_can_display_utility_bill_details(): void
    {
        $bill = UtilityBill::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->get(route('utility-bills.show', $bill));

        $response->assertStatus(200);
        $response->assertViewIs('utility-bills.show');
        $response->assertSee($bill->service_provider);
    }

    public function test_can_display_edit_utility_bill_form(): void
    {
        $bill = UtilityBill::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->get(route('utility-bills.edit', $bill));

        $response->assertStatus(200);
        $response->assertViewIs('utility-bills.edit');
        $response->assertSee($bill->service_provider);
    }

    public function test_can_update_utility_bill(): void
    {
        $bill = UtilityBill::factory()->create(['user_id' => $this->user->id]);

        $updateData = [
            'service_provider' => 'Updated Provider',
            'utility_type' => $bill->utility_type,
            'bill_amount' => $bill->bill_amount,
            'bill_period_start' => $bill->bill_period_start->format('Y-m-d'),
            'bill_period_end' => $bill->bill_period_end->format('Y-m-d'),
            'due_date' => $bill->due_date->format('Y-m-d'),
            'account_number' => $bill->account_number,
            'service_address' => $bill->service_address,
            'payment_status' => 'paid'
        ];

        $response = $this->actingAs($this->user)->put(route('utility-bills.update', $bill), $updateData);

        $response->assertRedirect(route('utility-bills.show', $bill));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('utility_bills', [
            'id' => $bill->id,
            'service_provider' => 'Updated Provider',
            'payment_status' => 'paid'
        ]);
    }

    public function test_can_delete_utility_bill(): void
    {
        $bill = UtilityBill::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->delete(route('utility-bills.destroy', $bill));

        $response->assertRedirect(route('utility-bills.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('utility_bills', ['id' => $bill->id]);
    }

    public function test_can_mark_bill_as_paid(): void
    {
        $bill = UtilityBill::factory()->create([
            'user_id' => $this->user->id,
            'payment_status' => 'pending'
        ]);

        $response = $this->actingAs($this->user)->patch(route('utility-bills.mark-paid', $bill));

        $response->assertRedirect(route('utility-bills.show', $bill));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('utility_bills', [
            'id' => $bill->id,
            'payment_status' => 'paid'
        ]);
    }

    public function test_can_get_analytics_summary(): void
    {
        UtilityBill::factory()->count(5)->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->get(route('utility-bills.analytics-summary'));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'total_bills',
                'pending_bills',
                'overdue_bills',
                'overdue_amount',
                'average_monthly_cost'
            ]
        ]);
    }

    public function test_can_get_spending_analytics(): void
    {
        UtilityBill::factory()->create([
            'user_id' => $this->user->id,
            'utility_type' => 'electricity',
            'bill_amount' => 150.00
        ]);

        $response = $this->actingAs($this->user)->get(route('utility-bills.spending-analytics'));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'monthly_spending',
            'service_breakdown',
            'year_over_year_comparison'
        ]);
    }

    public function test_can_get_due_date_analytics(): void
    {
        UtilityBill::factory()->create([
            'user_id' => $this->user->id,
            'due_date' => now()->addDays(5)
        ]);

        $response = $this->actingAs($this->user)->get(route('utility-bills.due-date-analytics'));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'due_this_week',
            'due_next_week',
            'overdue_bills'
        ]);
    }

    public function test_prevents_unauthorized_access_to_other_users_bills(): void
    {
        $otherUser = User::factory()->create();
        $bill = UtilityBill::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($this->user)->get(route('utility-bills.show', $bill));

        $response->assertStatus(403);
    }

    public function test_unauthenticated_users_cannot_access_utility_bills(): void
    {
        $response = $this->get(route('utility-bills.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_validates_bill_amount_is_numeric(): void
    {
        $response = $this->actingAs($this->user)->post(route('utility-bills.store'), [
            'service_provider' => 'Test Provider',
            'utility_type' => 'electricity',
            'bill_amount' => 'not-a-number',
            'bill_period_start' => now()->subMonth()->format('Y-m-d'),
            'bill_period_end' => now()->format('Y-m-d'),
            'due_date' => now()->addWeeks(2)->format('Y-m-d'),
            'account_number' => '1234567890',
            'service_address' => 'Test Address'
        ]);

        $response->assertSessionHasErrors('bill_amount');
    }

    public function test_validates_due_date_format(): void
    {
        $response = $this->actingAs($this->user)->post(route('utility-bills.store'), [
            'service_provider' => 'Test Provider',
            'utility_type' => 'electricity',
            'bill_amount' => 100.00,
            'bill_period_start' => now()->subMonth()->format('Y-m-d'),
            'bill_period_end' => now()->format('Y-m-d'),
            'due_date' => 'invalid-date',
            'account_number' => '1234567890',
            'service_address' => 'Test Address'
        ]);

        $response->assertSessionHasErrors('due_date');
    }

    public function test_can_set_auto_pay(): void
    {
        $bill = UtilityBill::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->patch(route('utility-bills.set-auto-pay', $bill), [
            'auto_pay_enabled' => true
        ]);

        $response->assertRedirect(route('utility-bills.show', $bill));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('utility_bills', [
            'id' => $bill->id,
            'auto_pay_enabled' => true
        ]);
    }
}
