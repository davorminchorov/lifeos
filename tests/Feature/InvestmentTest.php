<?php

namespace Tests\Feature;

use App\Models\Investment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class InvestmentTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        ['user' => $this->user] = $this->setupTenantContext();
    }

    public function test_can_display_investments_index_page()
    {
        Investment::factory()->count(3)->create(['user_id' => $this->user->id]);

        $response = $this->get(route('investments.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Investments/Index')
            ->has('investments')
        );
    }

    public function test_can_display_create_investment_form()
    {
        $response = $this->get(route('investments.create'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Investments/Create'));
    }

    public function test_can_store_a_new_investment()
    {
        $investmentData = [
            'name' => 'Apple Inc.',
            'investment_type' => 'stocks',
            'symbol_identifier' => 'AAPL',
            'quantity' => '100',
            'purchase_date' => '2024-01-01',
            'purchase_price' => '150.50',
            'total_fees_paid' => '9.95',
            'risk_tolerance' => 'moderate',
            'investment_goals' => ['growth', 'income'],
            'account_broker' => 'Fidelity',
            'target_allocation_percentage' => '25.00',
            'notes' => 'Long-term growth investment',
            'status' => 'active',
        ];

        $response = $this->post(route('investments.store'), $investmentData);

        $response->assertRedirect();
        $this->assertDatabaseHas('investments', [
            'user_id' => $this->user->id,
            'name' => 'Apple Inc.',
            'symbol_identifier' => 'AAPL',
            'investment_type' => 'stocks',
        ]);
    }

    public function test_validates_required_fields_when_storing_investment()
    {
        $response = $this->post(route('investments.store'), []);

        $response->assertSessionHasErrors([
            'name',
            'investment_type',
        ]);
    }

    public function test_can_display_investment_details()
    {
        $investment = Investment::factory()->create(['user_id' => $this->user->id]);

        $response = $this->get(route('investments.show', $investment));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Investments/Show'));
    }

    public function test_can_display_edit_investment_form()
    {
        $investment = Investment::factory()->create(['user_id' => $this->user->id]);

        $response = $this->get(route('investments.edit', $investment));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Investments/Edit'));
    }

    public function test_can_update_an_investment()
    {
        $investment = Investment::factory()->create(['user_id' => $this->user->id]);

        $updateData = [
            'name' => 'Updated Investment Name',
            'investment_type' => 'etf',
            'symbol_identifier' => 'SPY',
            'quantity' => '50',
            'purchase_date' => '2024-02-01',
            'purchase_price' => '400.00',
            'current_value' => '425.00',
            'total_fees_paid' => '0.00',
            'total_dividends_received' => '15.50',
            'risk_tolerance' => 'conservative',
            'investment_goals' => ['retirement'],
            'account_broker' => 'Vanguard',
            'target_allocation_percentage' => '40.00',
            'notes' => 'Updated investment notes',
            'status' => 'active',
        ];

        $response = $this->put(route('investments.update', $investment), $updateData);

        $response->assertRedirect();
        $this->assertDatabaseHas('investments', [
            'id' => $investment->id,
            'name' => 'Updated Investment Name',
            'symbol_identifier' => 'SPY',
            'investment_type' => 'etf',
        ]);
    }

    public function test_can_delete_an_investment()
    {
        $investment = Investment::factory()->create(['user_id' => $this->user->id]);

        $response = $this->delete(route('investments.destroy', $investment));

        $response->assertRedirect();
        $this->assertDatabaseMissing('investments', ['id' => $investment->id]);
    }

    public function test_can_filter_investments_by_type()
    {
        Investment::factory()->create([
            'user_id' => $this->user->id,
            'investment_type' => 'stocks',
            'name' => 'Stock Investment',
        ]);

        Investment::factory()->create([
            'user_id' => $this->user->id,
            'investment_type' => 'crypto',
            'name' => 'Crypto Investment',
        ]);

        $response = $this->get(route('investments.index', ['investment_type' => 'stocks']));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Investments/Index'));
    }

    public function test_can_search_investments_by_name()
    {
        Investment::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Apple Inc.',
            'symbol_identifier' => 'AAPL',
        ]);

        Investment::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Microsoft Corp.',
            'symbol_identifier' => 'MSFT',
        ]);

        $response = $this->get(route('investments.index', ['search' => 'Apple']));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Investments/Index'));
    }

    public function test_calculates_investment_performance_correctly()
    {
        $investment = Investment::factory()->create([
            'user_id' => $this->user->id,
            'quantity' => 100,
            'purchase_price' => 50.00,
            'current_value' => 60.00,
            'total_fees_paid' => 10.00,
            'total_dividends_received' => 50.00,
        ]);

        // Total cost basis: (100 * 50.00) + 10.00 = 5010.00
        $this->assertEquals(5010.00, $investment->total_cost_basis);

        // Current market value: 100 * 60.00 = 6000.00
        $this->assertEquals(6000.00, $investment->current_market_value);

        // Unrealized gain/loss: 6000.00 - 5010.00 = 990.00
        $this->assertEquals(990.00, $investment->unrealized_gain_loss);

        // Total return: 990.00 + 50.00 = 1040.00
        $this->assertEquals(1040.00, $investment->total_return);

        // Unrealized gain/loss percentage: (990.00 / 5010.00) * 100 = ~19.76%
        $this->assertEqualsWithDelta(19.76, $investment->unrealized_gain_loss_percentage, 0.01);

        // Total return percentage: (1040.00 / 5010.00) * 100 = ~20.76%
        $this->assertEqualsWithDelta(20.76, $investment->total_return_percentage, 0.01);
    }

    public function test_can_update_investment_price()
    {
        $investment = Investment::factory()->create([
            'user_id' => $this->user->id,
            'current_value' => 100.00,
            'last_price_update' => null,
        ]);

        $response = $this->post(route('investments.update-price', $investment), [
            'current_value' => 120.00,
        ]);

        $response->assertRedirect();
        $investment->refresh();
        $this->assertNotNull($investment->last_price_update);
    }

    public function test_unauthenticated_users_cannot_access_investments()
    {
        $this->app['auth']->logout();

        $response = $this->get(route('investments.index'));
        $response->assertRedirect('/login');

        $response = $this->get(route('investments.create'));
        $response->assertRedirect('/login');
    }

    public function test_validates_investment_type_enum()
    {
        $invalidData = [
            'name' => 'Test Investment',
            'investment_type' => 'invalid_type',
            'quantity' => '100',
            'purchase_date' => '2024-01-01',
            'purchase_price' => '50.00',
            'risk_tolerance' => 'moderate',
            'status' => 'active',
        ];

        $response = $this->post(route('investments.store'), $invalidData);

        $response->assertSessionHasErrors('investment_type');
    }

    public function test_validates_numeric_fields()
    {
        $invalidData = [
            'name' => 'Test Investment',
            'investment_type' => 'stocks',
            'quantity' => 'not_numeric',
            'purchase_date' => '2024-01-01',
            'purchase_price' => 'not_numeric',
            'risk_tolerance' => 'moderate',
            'status' => 'active',
        ];

        $response = $this->post(route('investments.store'), $invalidData);

        $response->assertSessionHasErrors(['quantity', 'purchase_price']);
    }

    public function test_validates_date_fields()
    {
        $invalidData = [
            'name' => 'Test Investment',
            'investment_type' => 'stocks',
            'quantity' => '100',
            'purchase_date' => 'invalid_date',
            'purchase_price' => '50.00',
            'risk_tolerance' => 'moderate',
            'status' => 'active',
        ];

        $response = $this->post(route('investments.store'), $invalidData);

        $response->assertSessionHasErrors('purchase_date');
    }
}
