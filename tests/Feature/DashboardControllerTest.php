<?php

namespace Tests\Feature;

use App\Models\Contract;
use App\Models\Expense;
use App\Models\Investment;
use App\Models\Subscription;
use App\Models\User;
use App\Models\UtilityBill;
use App\Models\Warranty;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        ['user' => $this->user] = $this->setupTenantContext();
    }

    public function test_can_display_dashboard_index(): void
    {
        $response = $this->actingAs($this->user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Dashboard/Index')
            ->has('stats')
            ->has('alerts')
            ->has('insights')
            ->has('recent_expenses')
            ->has('upcoming_bills')
            ->has('budget_utilization')
            ->has('top_subscriptions')
            ->has('portfolio_allocation')
        );
    }

    public function test_dashboard_includes_recent_expenses(): void
    {
        Expense::factory()->count(7)->create([
            'user_id' => $this->user->id,
            'expense_date' => now()->subDays(1),
        ]);

        $response = $this->actingAs($this->user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Dashboard/Index')
            ->has('recent_expenses')
        );
    }

    public function test_dashboard_includes_upcoming_bills(): void
    {
        UtilityBill::factory()->count(7)->create([
            'user_id' => $this->user->id,
            'payment_status' => 'pending',
            'due_date' => now()->addWeek(),
        ]);

        $response = $this->actingAs($this->user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Dashboard/Index')
            ->has('upcoming_bills')
        );
    }

    public function test_unauthenticated_users_cannot_access_dashboard(): void
    {
        $this->app['auth']->forgetGuards();

        $response = $this->get(route('dashboard'));

        $response->assertRedirect(route('login'));
    }

    public function test_can_get_chart_data_with_default_period(): void
    {
        $this->createTestData();

        $response = $this->actingAs($this->user)->get('/dashboard/chart-data');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'spendingTrends' => [
                'labels',
                'spending',
                'budget',
            ],
            'categoryBreakdown' => [
                'labels',
                'values',
            ],
            'portfolioPerformance',
            'monthlyComparison',
        ]);
    }

    public function test_can_get_chart_data_with_3months_period(): void
    {
        $this->createTestData();

        $response = $this->actingAs($this->user)->get('/dashboard/chart-data?period=3months');

        $response->assertStatus(200);
        $response->assertJson([]);
        $data = $response->json();
        $this->assertArrayHasKey('spendingTrends', $data);
        $this->assertArrayHasKey('categoryBreakdown', $data);
    }

    public function test_can_get_chart_data_with_1year_period(): void
    {
        $this->createTestData();

        $response = $this->actingAs($this->user)->get('/dashboard/chart-data?period=1year');

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertArrayHasKey('spendingTrends', $data);
        $this->assertArrayHasKey('categoryBreakdown', $data);
    }

    public function test_can_get_chart_data_with_2years_period(): void
    {
        $this->createTestData();

        $response = $this->actingAs($this->user)->get('/dashboard/chart-data?period=2years');

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertArrayHasKey('spendingTrends', $data);
        $this->assertArrayHasKey('categoryBreakdown', $data);
    }

    public function test_spending_trends_data_includes_monthly_breakdown(): void
    {
        // Create expenses from different months
        Expense::factory()->create([
            'user_id' => $this->user->id,
            'amount' => 1000,
            'expense_date' => now()->subMonths(2),
        ]);

        Expense::factory()->create([
            'user_id' => $this->user->id,
            'amount' => 1500,
            'expense_date' => now()->subMonth(),
        ]);

        $response = $this->actingAs($this->user)->get('/dashboard/chart-data');

        $response->assertStatus(200);
        $data = $response->json();

        $this->assertIsArray($data['spendingTrends']['labels']);
        $this->assertIsArray($data['spendingTrends']['spending']);
        $this->assertIsArray($data['spendingTrends']['budget']);
    }

    public function test_category_breakdown_includes_subscriptions_and_utilities(): void
    {
        Subscription::factory()->create([
            'user_id' => $this->user->id,
            'cost' => 500,
            'status' => 'active',
        ]);

        UtilityBill::factory()->create([
            'user_id' => $this->user->id,
            'bill_amount' => 300,
            'due_date' => now()->addWeek(),
        ]);

        $response = $this->actingAs($this->user)->get('/dashboard/chart-data');

        $response->assertStatus(200);
        $data = $response->json();

        $this->assertContains('Subscriptions', $data['categoryBreakdown']['labels']);
        $this->assertContains('Utilities', $data['categoryBreakdown']['labels']);
        $this->assertIsArray($data['categoryBreakdown']['values']);
    }

    public function test_dashboard_stats_are_calculated(): void
    {
        $this->createTestData();

        $response = $this->actingAs($this->user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Dashboard/Index')
            ->has('stats')
            ->where('stats.total_investments', fn ($value) => is_numeric($value))
            ->where('stats.active_subscriptions', fn ($value) => is_numeric($value))
            ->where('stats.pending_bills', fn ($value) => is_numeric($value))
            ->where('stats.active_contracts', fn ($value) => is_numeric($value))
        );
    }

    public function test_dashboard_alerts_are_generated(): void
    {
        // Create data that would generate alerts
        Contract::factory()->create([
            'user_id' => $this->user->id,
            'end_date' => now()->addDays(5), // Contract expiring soon
        ]);

        UtilityBill::factory()->create([
            'user_id' => $this->user->id,
            'due_date' => now()->addDays(2), // Bill due soon
            'payment_status' => 'pending',
        ]);

        $response = $this->actingAs($this->user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Dashboard/Index')
            ->has('alerts')
        );
    }

    private function createTestData(): void
    {
        // Create test data for various models
        Investment::factory()->count(2)->create(['user_id' => $this->user->id]);
        Expense::factory()->count(3)->create(['user_id' => $this->user->id]);
        Subscription::factory()->count(2)->create([
            'user_id' => $this->user->id,
            'status' => 'active',
        ]);
        UtilityBill::factory()->count(2)->create(['user_id' => $this->user->id]);
        Contract::factory()->count(1)->create(['user_id' => $this->user->id]);
        Warranty::factory()->count(1)->create(['user_id' => $this->user->id]);
    }
}
