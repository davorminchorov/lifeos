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
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function it_can_display_investments_index_page()
    {
        Investment::factory()->count(3)->create(['user_id' => $this->user->id]);

        $response = $this->get(route('investments.index'));

        $response->assertStatus(200);
        $response->assertViewIs('investments.index');
        $response->assertViewHas('investments');
    }

    public function it_can_display_create_investment_form()
    {
        $response = $this->get(route('investments.create'));

        $response->assertStatus(200);
        $response->assertViewIs('investments.create');
    }

    public function it_can_store_a_new_investment()
    {
        $investmentData = [
            'name' => 'Apple Inc.',
            'investment_type' => 'stock',
            'symbol_identifier' => 'AAPL',
            'quantity' => '100',
            'purchase_date' => '2024-01-01',
            'purchase_price' => '150.50',
            'total_fees_paid' => '9.95',
            'risk_tolerance' => 'medium',
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
            'investment_type' => 'stock',
        ]);
    }

    public function it_validates_required_fields_when_storing_investment()
    {
        $response = $this->post(route('investments.store'), []);

        $response->assertSessionHasErrors([
            'name',
            'investment_type',
            'quantity',
            'purchase_date',
            'purchase_price',
            'risk_tolerance',
            'status',
        ]);
    }

    public function it_can_display_investment_details()
    {
        $investment = Investment::factory()->create(['user_id' => $this->user->id]);

        $response = $this->get(route('investments.show', $investment));

        $response->assertStatus(200);
        $response->assertViewIs('investments.show');
        $response->assertViewHas('investment', $investment);
    }

    public function it_can_display_edit_investment_form()
    {
        $investment = Investment::factory()->create(['user_id' => $this->user->id]);

        $response = $this->get(route('investments.edit', $investment));

        $response->assertStatus(200);
        $response->assertViewIs('investments.edit');
        $response->assertViewHas('investment', $investment);
    }

    public function it_can_update_an_investment()
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
            'risk_tolerance' => 'low',
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

    public function it_can_delete_an_investment()
    {
        $investment = Investment::factory()->create(['user_id' => $this->user->id]);

        $response = $this->delete(route('investments.destroy', $investment));

        $response->assertRedirect();
        $this->assertDatabaseMissing('investments', ['id' => $investment->id]);
    }

    public function it_prevents_unauthorized_access_to_other_users_investments()
    {
        $otherUser = User::factory()->create();
        $investment = Investment::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->get(route('investments.show', $investment));
        $response->assertStatus(403);

        $response = $this->get(route('investments.edit', $investment));
        $response->assertStatus(403);

        $response = $this->put(route('investments.update', $investment), ['name' => 'Hacked']);
        $response->assertStatus(403);

        $response = $this->delete(route('investments.destroy', $investment));
        $response->assertStatus(403);
    }

    public function it_can_filter_investments_by_type()
    {
        Investment::factory()->create([
            'user_id' => $this->user->id,
            'investment_type' => 'stock',
            'name' => 'Stock Investment',
        ]);

        Investment::factory()->create([
            'user_id' => $this->user->id,
            'investment_type' => 'crypto',
            'name' => 'Crypto Investment',
        ]);

        $response = $this->get(route('investments.index', ['type' => 'stock']));

        $response->assertStatus(200);
        $response->assertSee('Stock Investment');
        $response->assertDontSee('Crypto Investment');
    }

    public function it_can_search_investments_by_name()
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
        $response->assertSee('Apple Inc.');
        $response->assertDontSee('Microsoft Corp.');
    }

    public function it_calculates_investment_performance_correctly()
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

    public function it_can_update_investment_price()
    {
        $investment = Investment::factory()->create([
            'user_id' => $this->user->id,
            'current_value' => 100.00,
            'last_price_update' => null,
        ]);

        $response = $this->post(route('investments.update-price', $investment));

        $response->assertStatus(200);
        $investment->refresh();
        $this->assertNotNull($investment->last_price_update);
    }

    public function it_returns_analytics_summary()
    {
        Investment::factory()->count(5)->create(['user_id' => $this->user->id]);

        $response = $this->get(route('investments.analytics.summary'));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'total_investments',
            'total_value',
            'total_cost_basis',
            'total_gain_loss',
            'gain_loss_percentage',
        ]);
    }

    public function it_returns_performance_analytics()
    {
        Investment::factory()->count(3)->create(['user_id' => $this->user->id]);

        $response = $this->get(route('investments.analytics.performance'));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'best_performer',
            'worst_performer',
            'top_performers',
        ]);
    }

    public function it_returns_allocation_analytics()
    {
        Investment::factory()->create([
            'user_id' => $this->user->id,
            'investment_type' => 'stock',
            'current_market_value' => 5000,
        ]);

        Investment::factory()->create([
            'user_id' => $this->user->id,
            'investment_type' => 'bond',
            'current_market_value' => 3000,
        ]);

        $response = $this->get(route('investments.analytics.allocation'));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'allocation_by_type',
            'diversification_score',
        ]);
    }

    public function it_returns_dividend_analytics()
    {
        Investment::factory()->create([
            'user_id' => $this->user->id,
            'total_dividends_received' => 100.00,
        ]);

        $response = $this->get(route('investments.analytics.dividends'));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'total_dividends',
            'monthly_dividend_trend',
        ]);
    }

    public function unauthenticated_users_cannot_access_investments()
    {
        $this->app['auth']->logout();

        $routes = [
            'investments.index',
            'investments.create',
            'investments.analytics.summary',
            'investments.analytics.performance',
            'investments.analytics.allocation',
            'investments.analytics.dividends',
        ];

        foreach ($routes as $route) {
            $response = $this->get(route($route));
            $response->assertRedirect('/login');
        }
    }

    public function it_validates_investment_type_enum()
    {
        $invalidData = [
            'name' => 'Test Investment',
            'investment_type' => 'invalid_type',
            'quantity' => '100',
            'purchase_date' => '2024-01-01',
            'purchase_price' => '50.00',
            'risk_tolerance' => 'medium',
            'status' => 'active',
        ];

        $response = $this->post(route('investments.store'), $invalidData);

        $response->assertSessionHasErrors('investment_type');
    }

    public function it_validates_numeric_fields()
    {
        $invalidData = [
            'name' => 'Test Investment',
            'investment_type' => 'stock',
            'quantity' => 'not_numeric',
            'purchase_date' => '2024-01-01',
            'purchase_price' => 'not_numeric',
            'risk_tolerance' => 'medium',
            'status' => 'active',
        ];

        $response = $this->post(route('investments.store'), $invalidData);

        $response->assertSessionHasErrors(['quantity', 'purchase_price']);
    }

    public function it_validates_date_fields()
    {
        $invalidData = [
            'name' => 'Test Investment',
            'investment_type' => 'stock',
            'quantity' => '100',
            'purchase_date' => 'invalid_date',
            'purchase_price' => '50.00',
            'risk_tolerance' => 'medium',
            'status' => 'active',
        ];

        $response = $this->post(route('investments.store'), $invalidData);

        $response->assertSessionHasErrors('purchase_date');
    }
}
