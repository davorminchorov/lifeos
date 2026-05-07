<?php

declare(strict_types=1);

namespace Tests\Feature\Dashboard;

use App\Models\AgentToken;
use App\Models\Expense;
use App\Models\PendingAction;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Agents\PendingActionApplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PendingActionsControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Tenant $tenant;

    private AgentToken $token;

    private PendingActionApplier $applier;

    protected function setUp(): void
    {
        parent::setUp();

        ['user' => $this->user, 'tenant' => $this->tenant] = $this->setupTenantContext();
        [$this->token] = AgentToken::issue($this->user, $this->tenant, 'phpunit', ['*']);
        $this->applier = app(PendingActionApplier::class);
    }

    private function record(array $overrides = []): PendingAction
    {
        return $this->applier->record(
            $this->token,
            'expenses.create',
            PendingAction::ACTION_CREATE,
            array_merge([
                'amount' => 12.5,
                'currency' => 'EUR',
                'expense_date' => '2026-05-01',
                'merchant' => 'Lidl',
                'category' => 'groceries',
                'description' => 'Weekly groceries',
            ], $overrides),
        );
    }

    public function test_index_shows_pending_actions_for_current_tenant(): void
    {
        $this->record();

        $response = $this->get(route('dashboard.pending-actions.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('PendingActions/Index')
            ->has('pendingActions.data', 1)
        );
    }

    public function test_index_does_not_leak_other_tenant_actions(): void
    {
        // Foreign tenant + pending action.
        $other = User::factory()->create();
        $otherTenant = Tenant::factory()->create(['owner_id' => $other->id]);
        $other->forceFill(['current_tenant_id' => $otherTenant->id])->save();
        [$otherToken] = AgentToken::issue($other, $otherTenant, 'foreign', ['*']);

        $this->actingAs($other);
        $this->applier->record($otherToken, 'expenses.create', PendingAction::ACTION_CREATE, [
            'amount' => 1, 'currency' => 'EUR', 'expense_date' => '2026-05-01',
            'merchant' => 'Foreign', 'category' => 'x', 'description' => 'x',
        ]);

        $this->actingAs($this->user);

        $this->get(route('dashboard.pending-actions.index'))
            ->assertInertia(fn ($page) => $page->where('pendingActions.total', 0));
    }

    public function test_approve_applies_action_and_creates_expense(): void
    {
        $action = $this->record();

        $this->patch(route('dashboard.pending-actions.approve', $action))
            ->assertRedirect();

        $action->refresh();
        $this->assertSame(PendingAction::STATUS_APPLIED, $action->status);
        $this->assertSame(1, Expense::query()->count());
    }

    public function test_reject_marks_action_with_reason(): void
    {
        $action = $this->record();

        $this->patch(route('dashboard.pending-actions.reject', $action), [
            'reason' => 'duplicate of existing entry',
        ])->assertRedirect();

        $action->refresh();
        $this->assertSame(PendingAction::STATUS_REJECTED, $action->status);
        $this->assertSame('duplicate of existing entry', $action->failure_reason);
    }

    public function test_revert_compensates_an_applied_action(): void
    {
        $action = $this->applier->apply($this->record(), $this->user);
        $this->assertSame(1, Expense::query()->count());

        $this->patch(route('dashboard.pending-actions.revert', $action))
            ->assertRedirect();

        $this->assertSame(0, Expense::query()->count());
        $this->assertSame(PendingAction::STATUS_REVERTED, $action->refresh()->status);
    }

    public function test_bulk_approve_applies_selected_pending_actions(): void
    {
        $a = $this->record(['merchant' => 'Lidl']);
        $b = $this->record(['merchant' => 'Konzum']);

        $this->post(route('dashboard.pending-actions.bulk-approve'), [
            'ids' => [$a->id, $b->id],
        ])->assertRedirect();

        $this->assertSame(2, Expense::query()->count());
        $this->assertSame(PendingAction::STATUS_APPLIED, $a->refresh()->status);
        $this->assertSame(PendingAction::STATUS_APPLIED, $b->refresh()->status);
    }
}
