<?php

namespace Tests\Feature;

use App\Models\Budget;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BudgetControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    public function test_index_displays_budgets_for_authenticated_user(): void
    {
        $budget = Budget::factory()->create(['user_id' => $this->user->id]);
        $otherUserBudget = Budget::factory()->create();

        $response = $this->actingAs($this->user)->get(route('budgets.index'));

        $response->assertStatus(200);
        $response->assertViewHas('budgets');
        $response->assertSee($budget->category);

        // Verify only user's budgets are in the collection
        $budgets = $response->viewData('budgets');
        $this->assertTrue($budgets->contains($budget));
        $this->assertFalse($budgets->contains($otherUserBudget));
    }

    public function test_index_filters_by_active_status(): void
    {
        $activeBudget = Budget::factory()->create([
            'user_id' => $this->user->id,
            'is_active' => true,
            'category' => 'active_category',
        ]);

        $inactiveBudget = Budget::factory()->create([
            'user_id' => $this->user->id,
            'is_active' => false,
            'category' => 'inactive_category',
        ]);

        $response = $this->actingAs($this->user)->get(route('budgets.index', ['status' => 'active']));

        $response->assertStatus(200);
        $response->assertSee('active_category');
        $response->assertDontSee('inactive_category');
    }

    public function test_index_filters_by_period(): void
    {
        $monthlyBudget = Budget::factory()->create([
            'user_id' => $this->user->id,
            'budget_period' => 'monthly',
            'category' => 'monthly_category',
        ]);

        $quarterlyBudget = Budget::factory()->create([
            'user_id' => $this->user->id,
            'budget_period' => 'quarterly',
            'category' => 'quarterly_category',
        ]);

        $response = $this->actingAs($this->user)->get(route('budgets.index', ['period' => 'monthly']));

        $response->assertStatus(200);
        $response->assertSee('monthly_category');
        $response->assertDontSee('quarterly_category');
    }

    public function test_index_filters_by_category(): void
    {
        $groceryBudget = Budget::factory()->create([
            'user_id' => $this->user->id,
            'category' => 'groceries',
        ]);

        $entertainmentBudget = Budget::factory()->create([
            'user_id' => $this->user->id,
            'category' => 'entertainment',
        ]);

        $response = $this->actingAs($this->user)->get(route('budgets.index', ['category' => 'groceries']));

        $response->assertStatus(200);
        $response->assertSee('groceries');
        $response->assertDontSee('entertainment');
    }

    public function test_index_requires_authentication(): void
    {
        $response = $this->get(route('budgets.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_create_displays_form(): void
    {
        $response = $this->actingAs($this->user)->get(route('budgets.create'));

        $response->assertStatus(200);
        $response->assertViewHas('categories');
        $response->assertViewHas('currencies');
    }

    public function test_create_requires_authentication(): void
    {
        $response = $this->get(route('budgets.create'));

        $response->assertRedirect(route('login'));
    }

    public function test_store_creates_new_budget(): void
    {
        $data = [
            'category' => 'groceries',
            'budget_period' => 'monthly',
            'amount' => 500.00,
            'currency' => 'USD',
            'is_active' => true,
            'rollover_unused' => false,
            'alert_threshold' => 80,
            'notes' => 'Monthly grocery budget',
        ];

        $response = $this->actingAs($this->user)->post(route('budgets.store'), $data);

        $response->assertRedirect(route('budgets.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('budgets', [
            'user_id' => $this->user->id,
            'category' => 'groceries',
            'amount' => 500.00,
        ]);
    }

    public function test_store_sets_dates_for_non_custom_period(): void
    {
        $data = [
            'category' => 'groceries',
            'budget_period' => 'monthly',
            'amount' => 500.00,
            'currency' => 'USD',
            'is_active' => true,
            'rollover_unused' => false,
            'alert_threshold' => 80,
        ];

        $response = $this->actingAs($this->user)->post(route('budgets.store'), $data);

        $response->assertRedirect(route('budgets.index'));

        $budget = Budget::where('user_id', $this->user->id)->first();
        $this->assertNotNull($budget->start_date);
        $this->assertNotNull($budget->end_date);
    }

    public function test_store_requires_authentication(): void
    {
        $response = $this->post(route('budgets.store'), []);

        $response->assertRedirect(route('login'));
    }

    public function test_show_displays_budget_details(): void
    {
        $budget = Budget::factory()->create([
            'user_id' => $this->user->id,
            'category' => 'groceries',
        ]);

        $response = $this->actingAs($this->user)->get(route('budgets.show', $budget));

        $response->assertStatus(200);
        $response->assertViewHas('budget');
        $response->assertSee('groceries');
    }

    public function test_show_cannot_view_other_users_budget(): void
    {
        $otherUserBudget = Budget::factory()->create();

        $response = $this->actingAs($this->user)->get(route('budgets.show', $otherUserBudget));

        $response->assertStatus(403);
    }

    public function test_show_requires_authentication(): void
    {
        $budget = Budget::factory()->create();

        $response = $this->get(route('budgets.show', $budget));

        $response->assertRedirect(route('login'));
    }

    public function test_edit_displays_form_with_budget_data(): void
    {
        $budget = Budget::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->get(route('budgets.edit', $budget));

        $response->assertStatus(200);
        $response->assertViewHas('budget');
        $response->assertViewHas('categories');
        $response->assertViewHas('currencies');
    }

    public function test_edit_cannot_edit_other_users_budget(): void
    {
        $otherUserBudget = Budget::factory()->create();

        $response = $this->actingAs($this->user)->get(route('budgets.edit', $otherUserBudget));

        $response->assertStatus(403);
    }

    public function test_edit_requires_authentication(): void
    {
        $budget = Budget::factory()->create();

        $response = $this->get(route('budgets.edit', $budget));

        $response->assertRedirect(route('login'));
    }

    public function test_update_modifies_budget(): void
    {
        $budget = Budget::factory()->create([
            'user_id' => $this->user->id,
            'category' => 'groceries',
            'amount' => 500.00,
        ]);

        $data = [
            'category' => 'groceries',
            'budget_period' => 'monthly',
            'amount' => 600.00,
            'currency' => 'USD',
            'is_active' => true,
            'rollover_unused' => false,
            'alert_threshold' => 85,
        ];

        $response = $this->actingAs($this->user)->put(route('budgets.update', $budget), $data);

        $response->assertRedirect(route('budgets.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('budgets', [
            'id' => $budget->id,
            'amount' => 600.00,
            'alert_threshold' => 85,
        ]);
    }

    public function test_update_cannot_modify_other_users_budget(): void
    {
        $otherUserBudget = Budget::factory()->create();

        $response = $this->actingAs($this->user)->put(route('budgets.update', $otherUserBudget), []);

        $response->assertStatus(403);
    }

    public function test_update_requires_authentication(): void
    {
        $budget = Budget::factory()->create();

        $response = $this->put(route('budgets.update', $budget), []);

        $response->assertRedirect(route('login'));
    }

    public function test_destroy_deletes_budget(): void
    {
        $budget = Budget::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->delete(route('budgets.destroy', $budget));

        $response->assertRedirect(route('budgets.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('budgets', ['id' => $budget->id]);
    }

    public function test_destroy_cannot_delete_other_users_budget(): void
    {
        $otherUserBudget = Budget::factory()->create();

        $response = $this->actingAs($this->user)->delete(route('budgets.destroy', $otherUserBudget));

        $response->assertStatus(403);
        $this->assertDatabaseHas('budgets', ['id' => $otherUserBudget->id]);
    }

    public function test_destroy_requires_authentication(): void
    {
        $budget = Budget::factory()->create();

        $response = $this->delete(route('budgets.destroy', $budget));

        $response->assertRedirect(route('login'));
    }

    public function test_analytics_displays_budget_analytics(): void
    {
        Budget::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->get(route('budgets.analytics'));

        $response->assertStatus(200);
        $response->assertViewHas('analytics');
    }

    public function test_analytics_filters_by_period(): void
    {
        Budget::factory()->create([
            'user_id' => $this->user->id,
            'start_date' => now()->startOfMonth(),
            'end_date' => now()->endOfMonth(),
        ]);

        $response = $this->actingAs($this->user)->get(route('budgets.analytics', ['period' => 'monthly']));

        $response->assertStatus(200);
    }

    public function test_analytics_requires_authentication(): void
    {
        $response = $this->get(route('budgets.analytics'));

        $response->assertRedirect(route('login'));
    }
}
