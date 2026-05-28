<?php

namespace Tests\Feature;

use App\Models\ProjectInvestment;
use App\Models\ProjectInvestmentTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectInvestmentTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        ['user' => $this->user] = $this->setupTenantContext();
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
}
