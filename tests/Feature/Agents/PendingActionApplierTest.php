<?php

declare(strict_types=1);

namespace Tests\Feature\Agents;

use App\Models\AgentToken;
use App\Models\Expense;
use App\Models\PendingAction;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Agents\PendingActionApplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PendingActionApplierTest extends TestCase
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

    private function expensePayload(array $overrides = []): array
    {
        return array_merge([
            'amount' => 12.5,
            'currency' => 'EUR',
            'expense_date' => '2026-05-01',
            'merchant' => 'Lidl',
            'category' => 'groceries',
            'description' => 'Weekly groceries',
        ], $overrides);
    }

    public function test_record_creates_pending_action(): void
    {
        $action = $this->applier->record($this->token, 'expenses.create', PendingAction::ACTION_CREATE, $this->expensePayload());

        $this->assertEquals(PendingAction::STATUS_PENDING, $action->status);
        $this->assertSame($this->tenant->id, $action->tenant_id);
        $this->assertSame($this->user->id, $action->user_id);
        $this->assertNotEmpty($action->idempotency_key);
        $this->assertSame(0, Expense::query()->count(), 'No expense should be created until approval.');
    }

    public function test_record_dedupes_on_idempotency_key(): void
    {
        $first = $this->applier->record($this->token, 'expenses.create', PendingAction::ACTION_CREATE, $this->expensePayload());
        $second = $this->applier->record($this->token, 'expenses.create', PendingAction::ACTION_CREATE, $this->expensePayload());

        $this->assertSame($first->id, $second->id);
        $this->assertSame(1, PendingAction::query()->count());
    }

    public function test_apply_creates_expense_with_agent_attribution(): void
    {
        $action = $this->applier->record($this->token, 'expenses.create', PendingAction::ACTION_CREATE, $this->expensePayload());
        $applied = $this->applier->apply($action, $this->user);

        $this->assertSame(PendingAction::STATUS_APPLIED, $applied->status);
        $this->assertSame(1, Expense::query()->count());

        $expense = Expense::query()->first();
        $this->assertSame('agent', $expense->source);
        $this->assertSame($this->token->id, $expense->created_by_agent_token_id);
        $this->assertSame(Expense::class, $applied->target_type);
        $this->assertSame($expense->id, $applied->target_id);
    }

    public function test_apply_validates_payload_against_form_request(): void
    {
        $action = $this->applier->record(
            $this->token,
            'expenses.create',
            PendingAction::ACTION_CREATE,
            $this->expensePayload(['amount' => 0]), // fails StoreExpenseRequest min:1
        );

        $this->expectException(ValidationException::class);
        $this->applier->apply($action, $this->user);
    }

    public function test_reject_marks_pending_action_with_reason(): void
    {
        $action = $this->applier->record($this->token, 'expenses.create', PendingAction::ACTION_CREATE, $this->expensePayload());
        $rejected = $this->applier->reject($action, $this->user, 'looks like a duplicate');

        $this->assertSame(PendingAction::STATUS_REJECTED, $rejected->status);
        $this->assertSame('looks like a duplicate', $rejected->failure_reason);
        $this->assertSame(0, Expense::query()->count());
    }

    public function test_revert_deletes_created_expense_within_window(): void
    {
        $action = $this->applier->record($this->token, 'expenses.create', PendingAction::ACTION_CREATE, $this->expensePayload());
        $applied = $this->applier->apply($action, $this->user);
        $this->assertSame(1, Expense::query()->count());

        $compensation = $this->applier->revert($applied, $this->user);

        $this->assertSame(PendingAction::ACTION_REVERT, $compensation->action);
        $this->assertSame(0, Expense::query()->count());
        $this->assertSame(PendingAction::STATUS_REVERTED, $applied->refresh()->status);
    }

    public function test_auto_apply_requires_prior_approval_and_tenant_setting(): void
    {
        // First submission: pending, no auto-apply (config not set).
        $first = $this->applier->record($this->token, 'expenses.create', PendingAction::ACTION_CREATE, $this->expensePayload());
        $this->assertSame(PendingAction::STATUS_PENDING, $first->status);

        // Approve it manually so the idempotency key is recorded as previously approved.
        $this->applier->apply($first, $this->user);

        // Flip the per-tenant tool_auto_apply for expenses.create.
        $this->tenant->forceFill([
            'tool_auto_apply' => ['expenses.create' => true],
        ])->save();

        // Submit a different expense (different idempotency key) — must still be pending,
        // because the prior-approval check is keyed on the exact same idempotency key.
        $different = $this->applier->record(
            $this->token,
            'expenses.create',
            PendingAction::ACTION_CREATE,
            $this->expensePayload(['merchant' => 'Konzum']),
        );
        $this->assertSame(PendingAction::STATUS_PENDING, $different->status);

        // But we should never duplicate the original (idempotency dedup), so
        // re-submitting the original key returns the already-applied row.
        $resubmit = $this->applier->record($this->token, 'expenses.create', PendingAction::ACTION_CREATE, $this->expensePayload());
        $this->assertSame($first->id, $resubmit->id);
        $this->assertSame(PendingAction::STATUS_APPLIED, $resubmit->status);
    }

    public function test_writes_disabled_tenant_blocks_apply(): void
    {
        $action = $this->applier->record($this->token, 'expenses.create', PendingAction::ACTION_CREATE, $this->expensePayload());

        $this->tenant->forceFill(['agents_writes_disabled' => true])->save();

        $this->expectException(\RuntimeException::class);
        $this->applier->apply($action->fresh(), $this->user);
    }
}
