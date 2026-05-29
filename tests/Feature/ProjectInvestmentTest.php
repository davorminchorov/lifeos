<?php

namespace Tests\Feature;

use App\Models\ProjectInvestment;
use App\Models\ProjectInvestmentTransaction;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ProjectInvestmentTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();
        ['user' => $this->user, 'tenant' => $this->tenant] = $this->setupTenantContext();
    }

    public function test_show_page_exposes_primary_currency_from_transactions(): void
    {
        $projectInvestment = ProjectInvestment::factory()->create(['user_id' => $this->user->id]);

        // The currency with the largest total wins (EUR: 5000 vs USD: 1000).
        ProjectInvestmentTransaction::factory()->create([
            'project_investment_id' => $projectInvestment->id,
            'user_id' => $this->user->id,
            'amount' => 1000,
            'currency' => 'USD',
        ]);
        ProjectInvestmentTransaction::factory()->create([
            'project_investment_id' => $projectInvestment->id,
            'user_id' => $this->user->id,
            'amount' => 5000,
            'currency' => 'EUR',
        ]);

        $response = $this->get(route('project-investments.show', $projectInvestment));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('ProjectInvestments/Show')
            ->where('projectInvestment.primary_currency', 'EUR')
        );
    }

    public function test_show_page_converts_total_invested_into_primary_currency(): void
    {
        config(['currency.conversion.enabled' => true]);
        // Seed the exchange rate so no external API call is made (1 USD = 0.9 EUR).
        Cache::put('exchange_rate_USD_EUR', 0.9);

        $projectInvestment = ProjectInvestment::factory()->create(['user_id' => $this->user->id]);

        // EUR is the primary currency (largest total); the USD amount must be converted into EUR.
        ProjectInvestmentTransaction::factory()->create([
            'project_investment_id' => $projectInvestment->id,
            'user_id' => $this->user->id,
            'amount' => 5000,
            'currency' => 'EUR',
        ]);
        ProjectInvestmentTransaction::factory()->create([
            'project_investment_id' => $projectInvestment->id,
            'user_id' => $this->user->id,
            'amount' => 1000,
            'currency' => 'USD',
        ]);

        $response = $this->get(route('project-investments.show', $projectInvestment));

        $response->assertStatus(200);
        // 5000 EUR + (1000 USD * 0.9) = 5900 EUR
        $response->assertInertia(fn ($page) => $page
            ->component('ProjectInvestments/Show')
            ->where('projectInvestment.primary_currency', 'EUR')
            ->where('projectInvestment.total_invested', fn ($value) => (float) $value === 5900.0)
        );
    }

    public function test_show_page_defaults_primary_currency_when_no_transactions(): void
    {
        $projectInvestment = ProjectInvestment::factory()->create(['user_id' => $this->user->id]);

        $response = $this->get(route('project-investments.show', $projectInvestment));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('ProjectInvestments/Show')
            ->where('projectInvestment.primary_currency', 'USD')
        );
    }

    public function test_index_summary_converts_totals_into_tenant_currency(): void
    {
        config(['currency.conversion.enabled' => true]);
        $this->tenant->update(['default_currency' => 'EUR']);
        Cache::put('exchange_rate_USD_EUR', 0.9);

        $projectA = ProjectInvestment::factory()->create(['user_id' => $this->user->id]);
        ProjectInvestmentTransaction::factory()->create([
            'project_investment_id' => $projectA->id,
            'user_id' => $this->user->id,
            'amount' => 1000,
            'currency' => 'USD',
        ]);

        $projectB = ProjectInvestment::factory()->create(['user_id' => $this->user->id]);
        ProjectInvestmentTransaction::factory()->create([
            'project_investment_id' => $projectB->id,
            'user_id' => $this->user->id,
            'amount' => 2000,
            'currency' => 'EUR',
        ]);

        $response = $this->get(route('project-investments.index'));

        $response->assertStatus(200);
        // (1000 USD * 0.9) + 2000 EUR = 2900 EUR
        $response->assertInertia(fn ($page) => $page
            ->component('ProjectInvestments/Index')
            ->where('summary.currency', 'EUR')
            ->where('summary.total_invested', fn ($value) => (float) $value === 2900.0)
        );
    }

    public function test_analytics_converts_totals_into_tenant_currency(): void
    {
        config(['currency.conversion.enabled' => true]);
        $this->tenant->update(['default_currency' => 'EUR']);
        Cache::put('exchange_rate_USD_EUR', 0.9);

        $project = ProjectInvestment::factory()->create(['user_id' => $this->user->id]);
        ProjectInvestmentTransaction::factory()->create([
            'project_investment_id' => $project->id,
            'user_id' => $this->user->id,
            'amount' => 1000,
            'currency' => 'USD',
        ]);

        $response = $this->get(route('project-investments.analytics'));

        $response->assertStatus(200);
        // 1000 USD * 0.9 = 900 EUR
        $response->assertInertia(fn ($page) => $page
            ->component('ProjectInvestments/Analytics')
            ->where('analytics.currency', 'EUR')
            ->where('analytics.total_invested', fn ($value) => (float) $value === 900.0)
        );
    }
}
