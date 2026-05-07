<?php

declare(strict_types=1);

namespace App\Services\Agents;

use App\Http\Requests\StoreExpenseRequest;
use App\Models\AgentToken;
use App\Models\Expense;
use App\Models\PendingAction;
use App\Models\User;
use App\Services\Expenses\ExpenseService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

/**
 * Applies a pending action to live data. Validation runs through the same
 * FormRequest used by the corresponding controller, so agent writes are
 * indistinguishable from user writes once approved.
 */
class PendingActionApplier
{
    public function __construct(
        protected ExpenseService $expenses,
        protected IdempotencyKey $keys,
    ) {}

    /**
     * Record a write request as a pending_action. If a previously approved row
     * exists for the same idempotency key the older row is returned.
     *
     * If the tool is allowed to auto-apply on this tenant AND the same
     * idempotency key was previously approved, the new row is applied
     * immediately.
     *
     * @param  array<string, mixed>  $payload
     */
    public function record(
        AgentToken $token,
        string $tool,
        string $action,
        array $payload,
        ?string $sessionId = null,
    ): PendingAction {
        $tenantId = $token->tenant_id;
        $idempotencyKey = $this->keys->for($tool, $tenantId, $payload);

        return DB::transaction(function () use (
            $token,
            $tool,
            $action,
            $payload,
            $sessionId,
            $tenantId,
            $idempotencyKey,
        ): PendingAction {
            $existing = PendingAction::query()
                ->where('tenant_id', $tenantId)
                ->where('tool', $tool)
                ->where('idempotency_key', $idempotencyKey)
                ->first();

            if ($existing !== null) {
                return $existing;
            }

            $action = PendingAction::create([
                'tenant_id' => $tenantId,
                'user_id' => $token->user_id,
                'agent_token_id' => $token->id,
                'agent_slug' => $token->agent_slug,
                'session_id' => $sessionId,
                'tool' => $tool,
                'action' => $action,
                'payload' => $payload,
                'preview' => $this->buildPreview($tool, $payload),
                'idempotency_key' => $idempotencyKey,
                'status' => PendingAction::STATUS_PENDING,
            ]);

            if ($this->shouldAutoApply($token, $tool, $idempotencyKey, $action->id)) {
                $this->apply($action, reviewer: null, autoApplied: true);
            }

            return $action->refresh();
        });
    }

    /**
     * Approve and apply the pending action. Throws ValidationException if the
     * payload no longer satisfies the controller's FormRequest.
     */
    public function apply(
        PendingAction $action,
        ?User $reviewer,
        bool $autoApplied = false,
    ): PendingAction {
        if (! in_array($action->status, [PendingAction::STATUS_PENDING, PendingAction::STATUS_APPROVED], true)) {
            throw new RuntimeException("Cannot apply pending action in status [{$action->status}].");
        }

        $this->guardTenantWrites($action);
        $this->validatePayload($action);

        return DB::transaction(function () use ($action, $reviewer, $autoApplied): PendingAction {
            $token = $action->agentToken;
            $user = $action->user;

            try {
                $diff = $this->execute($action, $token, $user);
            } catch (Throwable $e) {
                $action->forceFill([
                    'status' => PendingAction::STATUS_FAILED,
                    'failure_reason' => $e->getMessage(),
                ])->save();

                throw $e;
            }

            $action->forceFill([
                'status' => PendingAction::STATUS_APPLIED,
                'applied_diff' => $diff,
                'applied_at' => now(),
                'reviewed_by' => $reviewer?->id,
                'reviewed_at' => $autoApplied ? null : now(),
                'failure_reason' => null,
            ])->save();

            return $action->refresh();
        });
    }

    /**
     * Reject the pending action. Optional reason is stored in failure_reason
     * (the column is reused for both failure messages and rejection rationale).
     */
    public function reject(PendingAction $action, User $reviewer, ?string $reason = null): PendingAction
    {
        if ($action->status !== PendingAction::STATUS_PENDING) {
            throw new RuntimeException("Cannot reject pending action in status [{$action->status}].");
        }

        $action->forceFill([
            'status' => PendingAction::STATUS_REJECTED,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
            'failure_reason' => $reason,
        ])->save();

        return $action->refresh();
    }

    /**
     * Revert a previously applied action. Creates an inverse PendingAction
     * (action='revert') and immediately marks both the original and the
     * compensation as reverted.
     */
    public function revert(PendingAction $action, User $reverter, int $windowMinutes = 10): PendingAction
    {
        if (! $action->isRevertable($windowMinutes)) {
            throw new RuntimeException('This action is no longer revertable.');
        }

        return DB::transaction(function () use ($action, $reverter): PendingAction {
            $compensation = PendingAction::create([
                'tenant_id' => $action->tenant_id,
                'user_id' => $reverter->id,
                'agent_token_id' => null,
                'agent_slug' => $action->agent_slug,
                'session_id' => $action->session_id,
                'tool' => $action->tool,
                'action' => PendingAction::ACTION_REVERT,
                'payload' => ['original_pending_action_id' => $action->id],
                'idempotency_key' => hash('sha256', "revert|{$action->id}"),
                'status' => PendingAction::STATUS_APPLIED,
                'applied_at' => now(),
                'reviewed_by' => $reverter->id,
                'reviewed_at' => now(),
                'reverted_pending_action_id' => $action->id,
            ]);

            $this->executeRevert($action);

            $action->forceFill([
                'status' => PendingAction::STATUS_REVERTED,
                'reverted_by' => $reverter->id,
                'reverted_at' => now(),
            ])->save();

            return $compensation->refresh();
        });
    }

    private function guardTenantWrites(PendingAction $action): void
    {
        $tenant = $action->tenant;

        if ($tenant !== null && $tenant->agents_writes_disabled) {
            throw new RuntimeException('Agent writes are disabled for this tenant.');
        }
    }

    private function validatePayload(PendingAction $action): void
    {
        match ($action->tool) {
            'expenses.create' => $this->validateExpenseCreate($action->payload),
            'expenses.bulkImport' => $this->validateExpenseBulkImport($action->payload),
            'expenses.categorize' => $this->validateExpenseCategorize($action->payload),
            default => throw new RuntimeException("No validator registered for tool [{$action->tool}]."),
        };
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function validateExpenseCreate(array $payload): void
    {
        $rules = (new StoreExpenseRequest)->rules();
        $validator = Validator::make($payload, $rules);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function validateExpenseBulkImport(array $payload): void
    {
        $items = (array) ($payload['items'] ?? []);

        if ($items === []) {
            throw ValidationException::withMessages(['items' => 'At least one item is required.']);
        }

        foreach ($items as $i => $item) {
            try {
                $this->validateExpenseCreate((array) $item);
            } catch (ValidationException $e) {
                throw ValidationException::withMessages([
                    "items.{$i}" => $e->errors(),
                ]);
            }
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function validateExpenseCategorize(array $payload): void
    {
        $validator = Validator::make($payload, [
            'expense_id' => 'required|integer|exists:expenses,id',
            'category' => 'required|string|max:255',
            'subcategory' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function execute(PendingAction $action, ?AgentToken $token, User $user): array
    {
        $attribution = [
            'source' => 'agent',
            'agent_token_id' => $token?->id,
        ];

        return match ($action->tool) {
            'expenses.create' => $this->executeExpenseCreate($action, $user, $attribution),
            'expenses.bulkImport' => $this->executeExpenseBulkImport($action, $user, $attribution),
            'expenses.categorize' => $this->executeExpenseCategorize($action),
            default => throw new RuntimeException("No executor registered for tool [{$action->tool}]."),
        };
    }

    /**
     * @param  array<string, mixed>  $attribution
     * @return array<string, mixed>
     */
    private function executeExpenseCreate(PendingAction $action, User $user, array $attribution): array
    {
        $expense = $this->expenses->create($user, $action->payload, $attribution);

        $action->forceFill([
            'target_type' => Expense::class,
            'target_id' => $expense->id,
        ])->save();

        return [
            'before' => null,
            'after' => $expense->only(['id', 'amount', 'currency', 'merchant', 'category', 'expense_date']),
        ];
    }

    /**
     * @param  array<string, mixed>  $attribution
     * @return array<string, mixed>
     */
    private function executeExpenseBulkImport(PendingAction $action, User $user, array $attribution): array
    {
        $rows = (array) ($action->payload['items'] ?? []);
        $created = $this->expenses->bulkCreate($user, $rows, $attribution);

        return [
            'before' => null,
            'after' => array_map(fn (Expense $e): array => $e->only(['id', 'amount', 'currency', 'merchant']), $created),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function executeExpenseCategorize(PendingAction $action): array
    {
        $expense = Expense::query()->findOrFail((int) $action->payload['expense_id']);
        $before = $expense->only(['id', 'category', 'subcategory']);

        $this->expenses->categorize(
            $expense,
            (string) $action->payload['category'],
            isset($action->payload['subcategory']) ? (string) $action->payload['subcategory'] : null,
        );

        $action->forceFill([
            'target_type' => Expense::class,
            'target_id' => $expense->id,
        ])->save();

        return [
            'before' => $before,
            'after' => $expense->refresh()->only(['id', 'category', 'subcategory']),
        ];
    }

    private function executeRevert(PendingAction $action): void
    {
        switch ($action->tool) {
            case 'expenses.create':
            case 'expenses.bulkImport':
                $this->revertExpenseCreate($action);

                return;
            case 'expenses.categorize':
                $this->revertExpenseCategorize($action);

                return;
        }

        throw new RuntimeException("No revert executor for tool [{$action->tool}].");
    }

    private function revertExpenseCreate(PendingAction $action): void
    {
        $diff = $action->applied_diff ?? [];
        $after = $diff['after'] ?? null;

        $ids = match (true) {
            is_array($after) && isset($after['id']) => [(int) $after['id']],
            is_array($after) => array_values(array_filter(array_map(
                static fn ($row) => is_array($row) && isset($row['id']) ? (int) $row['id'] : null,
                $after,
            ))),
            default => [],
        };

        if ($ids === []) {
            return;
        }

        Expense::query()->whereIn('id', $ids)->delete();
    }

    private function revertExpenseCategorize(PendingAction $action): void
    {
        $diff = $action->applied_diff ?? [];
        $before = $diff['before'] ?? null;

        if (! is_array($before) || ! isset($before['id'])) {
            return;
        }

        $expense = Expense::query()->find((int) $before['id']);

        if ($expense === null) {
            return;
        }

        $this->expenses->categorize(
            $expense,
            (string) ($before['category'] ?? ''),
            isset($before['subcategory']) ? (string) $before['subcategory'] : null,
        );
    }

    private function shouldAutoApply(
        AgentToken $token,
        string $tool,
        string $idempotencyKey,
        int $newActionId,
    ): bool {
        $tenant = $token->tenant;

        if ($tenant === null || ! $tenant->autoAppliesTool($tool)) {
            return false;
        }

        return PendingAction::query()
            ->where('tenant_id', $token->tenant_id)
            ->where('tool', $tool)
            ->where('idempotency_key', $idempotencyKey)
            ->where('id', '!=', $newActionId)
            ->where('status', PendingAction::STATUS_APPLIED)
            ->exists();
    }

    /**
     * Render a small preview map for the dashboard. Kept simple: tool-specific
     * presenters are introduced when the UI needs richer rendering.
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function buildPreview(string $tool, array $payload): array
    {
        return match ($tool) {
            'expenses.create' => [
                'summary' => sprintf(
                    '%s %s at %s on %s',
                    number_format((float) ($payload['amount'] ?? 0), 2),
                    (string) ($payload['currency'] ?? ''),
                    (string) ($payload['merchant'] ?? '—'),
                    (string) ($payload['expense_date'] ?? ''),
                ),
                'category' => (string) ($payload['category'] ?? ''),
            ],
            'expenses.bulkImport' => [
                'summary' => sprintf('%d expense rows', count((array) ($payload['items'] ?? []))),
            ],
            'expenses.categorize' => [
                'summary' => sprintf(
                    'Categorize expense #%d as %s',
                    (int) ($payload['expense_id'] ?? 0),
                    (string) ($payload['category'] ?? ''),
                ),
            ],
            default => [],
        };
    }
}
