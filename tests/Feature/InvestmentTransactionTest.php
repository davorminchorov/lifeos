<?php

namespace Tests\Feature;

use App\Models\Investment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvestmentTransactionTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Investment $investment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->investment = Investment::factory()->create([
            'user_id' => $this->user->id,
            'quantity' => 100.00,
            'total_fees_paid' => 0.00,
        ]);
    }

    public function test_user_can_record_buy_transaction()
    {
        $transactionData = [
            'investment_id' => $this->investment->id,
            'transaction_type' => 'buy',
            'quantity' => 50.00,
            'price_per_share' => 25.50,
            'total_amount' => 1275.00,
            'fees' => 9.99,
            'taxes' => 0.00,
            'transaction_date' => '2025-08-17',
            'currency' => 'USD',
        ];

        $response = $this->actingAs($this->user)
            ->post(route('investments.record-transaction', $this->investment), $transactionData);

        $response->assertRedirect(route('investments.show', $this->investment));
        $response->assertSessionHas('success', 'Transaction recorded successfully!');

        $this->assertDatabaseHas('investment_transactions', [
            'investment_id' => $this->investment->id,
            'transaction_type' => 'buy',
            'quantity' => 50.00,
            'price_per_share' => 25.50,
        ]);

        // Check that investment quantity and fees were updated
        $this->investment->refresh();
        $this->assertEquals(150.00, $this->investment->quantity);
        $this->assertEquals(9.99, $this->investment->total_fees_paid);
    }

    public function test_user_can_record_sell_transaction()
    {
        $transactionData = [
            'investment_id' => $this->investment->id,
            'transaction_type' => 'sell',
            'quantity' => 25.00,
            'price_per_share' => 30.00,
            'total_amount' => 750.00,
            'fees' => 9.99,
            'taxes' => 0.00,
            'transaction_date' => '2025-08-17',
            'currency' => 'USD',
        ];

        $response = $this->actingAs($this->user)
            ->post(route('investments.record-transaction', $this->investment), $transactionData);

        $response->assertRedirect(route('investments.show', $this->investment));

        $this->assertDatabaseHas('investment_transactions', [
            'investment_id' => $this->investment->id,
            'transaction_type' => 'sell',
            'quantity' => 25.00,
        ]);

        // Check that investment quantity was reduced
        $this->investment->refresh();
        $this->assertEquals(75.00, $this->investment->quantity);
        $this->assertEquals(9.99, $this->investment->total_fees_paid);
    }

    public function test_selling_all_shares_updates_investment_status()
    {
        $transactionData = [
            'investment_id' => $this->investment->id,
            'transaction_type' => 'sell',
            'quantity' => 100.00, // Sell all shares
            'price_per_share' => 30.00,
            'total_amount' => 3000.00,
            'fees' => 9.99,
            'taxes' => 0.00,
            'transaction_date' => '2025-08-17',
            'currency' => 'USD',
        ];

        $this->actingAs($this->user)
            ->post(route('investments.record-transaction', $this->investment), $transactionData);

        $this->investment->refresh();
        $this->assertEquals(0.00, $this->investment->quantity);
        $this->assertEquals('sold', $this->investment->status);
    }

    public function test_transaction_validation_rules_are_enforced()
    {
        $response = $this->actingAs($this->user)
            ->post(route('investments.record-transaction', $this->investment), []);

        $response->assertSessionHasErrors([
            'investment_id',
            'transaction_type',
            'quantity',
            'price_per_share',
            'total_amount',
            'transaction_date',
            'currency',
        ]);
    }

    public function test_transaction_amount_calculation_validation()
    {
        $transactionData = [
            'investment_id' => $this->investment->id,
            'transaction_type' => 'buy',
            'quantity' => 10.00,
            'price_per_share' => 25.00,
            'total_amount' => 300.00, // Should be 10 * 25 = 250.00
            'fees' => 0.00,
            'taxes' => 0.00,
            'transaction_date' => '2025-08-17',
            'currency' => 'USD',
        ];

        $response = $this->actingAs($this->user)
            ->post(route('investments.record-transaction', $this->investment), $transactionData);

        $response->assertSessionHasErrors(['total_amount']);
    }

    public function test_limit_order_requires_limit_price()
    {
        $transactionData = [
            'investment_id' => $this->investment->id,
            'transaction_type' => 'buy',
            'quantity' => 10.00,
            'price_per_share' => 25.00,
            'total_amount' => 250.00,
            'order_type' => 'limit',
            'transaction_date' => '2025-08-17',
            'currency' => 'USD',
            // Missing limit_price
        ];

        $response = $this->actingAs($this->user)
            ->post(route('investments.record-transaction', $this->investment), $transactionData);

        $response->assertSessionHasErrors(['limit_price']);
    }

    public function test_stop_order_requires_stop_price()
    {
        $transactionData = [
            'investment_id' => $this->investment->id,
            'transaction_type' => 'sell',
            'quantity' => 10.00,
            'price_per_share' => 25.00,
            'total_amount' => 250.00,
            'order_type' => 'stop',
            'transaction_date' => '2025-08-17',
            'currency' => 'USD',
            // Missing stop_price
        ];

        $response = $this->actingAs($this->user)
            ->post(route('investments.record-transaction', $this->investment), $transactionData);

        $response->assertSessionHasErrors(['stop_price']);
    }

    public function test_user_cannot_record_transaction_for_other_users_investment()
    {
        $otherUser = User::factory()->create();
        $otherInvestment = Investment::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $transactionData = [
            'investment_id' => $otherInvestment->id,
            'transaction_type' => 'buy',
            'quantity' => 10.00,
            'price_per_share' => 25.00,
            'total_amount' => 250.00,
            'transaction_date' => '2025-08-17',
            'currency' => 'USD',
        ];

        $response = $this->actingAs($this->user)
            ->post(route('investments.record-transaction', $otherInvestment), $transactionData);

        $response->assertStatus(403);
    }

    public function test_api_transaction_recording_returns_json()
    {
        $transactionData = [
            'investment_id' => $this->investment->id,
            'transaction_type' => 'buy',
            'quantity' => 10.00,
            'price_per_share' => 25.00,
            'total_amount' => 250.00,
            'transaction_date' => '2025-08-17',
            'currency' => 'USD',
        ];

        $response = $this->actingAs($this->user)
            ->postJson(route('investments.record-transaction', $this->investment), $transactionData);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'message',
            'transaction',
            'investment',
        ]);
    }

    public function test_legacy_record_buy_still_works()
    {
        $buyData = [
            'quantity' => 25.00,
            'price_per_unit' => 20.00,
            'fees' => 5.99,
            'transaction_date' => '2025-08-17',
        ];

        $response = $this->actingAs($this->user)
            ->post(route('investments.record-buy', $this->investment), $buyData);

        $response->assertRedirect(route('investments.show', $this->investment));

        $this->assertDatabaseHas('investment_transactions', [
            'investment_id' => $this->investment->id,
            'transaction_type' => 'buy',
            'quantity' => 25.00,
            'price_per_share' => 20.00,
        ]);

        $this->investment->refresh();
        $this->assertEquals(125.00, $this->investment->quantity);
    }

    public function test_legacy_record_sell_still_works()
    {
        $sellData = [
            'quantity' => 25.00,
            'price_per_unit' => 30.00,
            'fees' => 5.99,
            'transaction_date' => '2025-08-17',
        ];

        $response = $this->actingAs($this->user)
            ->post(route('investments.record-sell', $this->investment), $sellData);

        $response->assertRedirect(route('investments.show', $this->investment));

        $this->assertDatabaseHas('investment_transactions', [
            'investment_id' => $this->investment->id,
            'transaction_type' => 'sell',
            'quantity' => 25.00,
            'price_per_share' => 30.00,
        ]);

        $this->investment->refresh();
        $this->assertEquals(75.00, $this->investment->quantity);
    }
}
