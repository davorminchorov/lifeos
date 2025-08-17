<?php

namespace Tests\Feature;

use App\Models\Investment;
use App\Models\InvestmentDividend;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvestmentDividendTest extends TestCase
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
        ]);
    }

    public function test_user_can_record_dividend()
    {
        $dividendData = [
            'investment_id' => $this->investment->id,
            'amount' => 100.50,
            'record_date' => '2025-08-15',
            'payment_date' => '2025-08-17',
            'dividend_type' => 'qualified',
            'frequency' => 'quarterly',
            'dividend_per_share' => 2.50,
            'shares_held' => 40.20,
            'currency' => 'USD',
            'reinvested' => false,
        ];

        $response = $this->actingAs($this->user)
            ->post(route('investments.record-dividend', $this->investment), $dividendData);

        $response->assertRedirect(route('investments.show', $this->investment));
        $response->assertSessionHas('success', 'Dividend recorded successfully!');

        $this->assertDatabaseHas('investment_dividends', [
            'investment_id' => $this->investment->id,
            'amount' => 100.50,
            'dividend_type' => 'qualified',
        ]);

        // Check that investment total dividends was updated
        $this->investment->refresh();
        $this->assertEquals(100.50, $this->investment->total_dividends_received);
    }

    public function test_dividend_validation_rules_are_enforced()
    {
        $response = $this->actingAs($this->user)
            ->post(route('investments.record-dividend', $this->investment), []);

        $response->assertSessionHasErrors([
            'investment_id',
            'amount',
            'record_date',
            'payment_date',
            'dividend_type',
            'frequency',
            'dividend_per_share',
            'shares_held',
            'currency',
        ]);
    }

    public function test_dividend_amount_calculation_validation()
    {
        $dividendData = [
            'investment_id' => $this->investment->id,
            'amount' => 100.00, // This should be 2.50 * 10 = 25.00
            'record_date' => '2025-08-15',
            'payment_date' => '2025-08-17',
            'dividend_type' => 'qualified',
            'frequency' => 'quarterly',
            'dividend_per_share' => 2.50,
            'shares_held' => 10.00,
            'currency' => 'USD',
        ];

        $response = $this->actingAs($this->user)
            ->post(route('investments.record-dividend', $this->investment), $dividendData);

        $response->assertSessionHasErrors(['amount']);
    }

    public function test_user_cannot_record_dividend_for_other_users_investment()
    {
        $otherUser = User::factory()->create();
        $otherInvestment = Investment::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $dividendData = [
            'investment_id' => $otherInvestment->id,
            'amount' => 100.50,
            'record_date' => '2025-08-15',
            'payment_date' => '2025-08-17',
            'dividend_type' => 'qualified',
            'frequency' => 'quarterly',
            'dividend_per_share' => 2.50,
            'shares_held' => 40.20,
            'currency' => 'USD',
        ];

        $response = $this->actingAs($this->user)
            ->post(route('investments.record-dividend', $otherInvestment), $dividendData);

        $response->assertStatus(403);
    }

    public function test_api_dividend_recording_returns_json()
    {
        $dividendData = [
            'investment_id' => $this->investment->id,
            'amount' => 100.50,
            'record_date' => '2025-08-15',
            'payment_date' => '2025-08-17',
            'dividend_type' => 'qualified',
            'frequency' => 'quarterly',
            'dividend_per_share' => 2.50,
            'shares_held' => 40.20,
            'currency' => 'USD',
        ];

        $response = $this->actingAs($this->user)
            ->postJson(route('investments.record-dividend', $this->investment), $dividendData);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'message',
            'dividend',
            'investment',
        ]);
    }

    public function test_multiple_dividends_update_investment_total()
    {
        // Record first dividend
        InvestmentDividend::create([
            'investment_id' => $this->investment->id,
            'amount' => 50.00,
            'record_date' => '2025-06-15',
            'payment_date' => '2025-06-17',
            'dividend_type' => 'qualified',
            'frequency' => 'quarterly',
            'dividend_per_share' => 1.25,
            'shares_held' => 40.00,
            'currency' => 'USD',
        ]);

        // Record second dividend via controller
        $dividendData = [
            'investment_id' => $this->investment->id,
            'amount' => 75.00,
            'record_date' => '2025-08-15',
            'payment_date' => '2025-08-17',
            'dividend_type' => 'qualified',
            'frequency' => 'quarterly',
            'dividend_per_share' => 1.875,
            'shares_held' => 40.00,
            'currency' => 'USD',
        ];

        $this->actingAs($this->user)
            ->post(route('investments.record-dividend', $this->investment), $dividendData);

        $this->investment->refresh();
        $this->assertEquals(125.00, $this->investment->total_dividends_received);
    }
}
