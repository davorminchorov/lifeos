<?php

declare(strict_types=1);

namespace App\Services\Agents;

use App\Http\Requests\StoreContractRequest;
use App\Http\Requests\StoreExpenseRequest;
use App\Http\Requests\StoreIouRequest;
use App\Http\Requests\StoreJobApplicationRequest;
use App\Http\Requests\StoreSubscriptionRequest;
use App\Http\Requests\StoreUtilityBillRequest;
use App\Http\Requests\StoreWarrantyRequest;
use App\Models\AgentToken;
use App\Models\Contract;
use App\Models\Expense;
use App\Models\Iou;
use App\Models\JobApplication;
use App\Models\PendingAction;
use App\Models\Subscription;
use App\Models\User;
use App\Models\UtilityBill;
use App\Models\Warranty;
use App\Services\Contracts\ContractService;
use App\Services\Expenses\ExpenseService;
use App\Services\Iou\IouService;
use App\Services\Jobs\JobApplicationService;
use App\Services\Subscriptions\SubscriptionService;
use App\Services\UtilityBills\UtilityBillService;
use App\Services\Warranties\WarrantyService;
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
        protected SubscriptionService $subscriptions,
        protected ContractService $contracts,
        protected WarrantyService $warranties,
        protected IouService $ious,
        protected UtilityBillService $bills,
        protected JobApplicationService $jobs,
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
            'subscriptions.create' => $this->validateWithFormRequest(StoreSubscriptionRequest::class, $action->payload),
            'contracts.create' => $this->validateWithFormRequest(StoreContractRequest::class, $action->payload),
            'warranties.create' => $this->validateWithFormRequest(StoreWarrantyRequest::class, $action->payload),
            'iou.create' => $this->validateWithFormRequest(StoreIouRequest::class, $action->payload),
            'utilityBills.create' => $this->validateWithFormRequest(StoreUtilityBillRequest::class, $action->payload),
            'jobs.updateStatus' => $this->validateJobsUpdateStatus($action->payload),
            'jobs.addInterview' => $this->validateJobsAddInterview($action->payload),
            default => throw new RuntimeException("No validator registered for tool [{$action->tool}]."),
        };
    }

    /**
     * @param  class-string<\Illuminate\Foundation\Http\FormRequest>  $formRequestClass
     * @param  array<string, mixed>  $payload
     */
    private function validateWithFormRequest(string $formRequestClass, array $payload): void
    {
        $rules = (new $formRequestClass)->rules();
        $validator = Validator::make($payload, $rules);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function validateJobsUpdateStatus(array $payload): void
    {
        $validator = Validator::make($payload, [
            'job_application_id' => 'required|integer|exists:job_applications,id',
            'status' => 'required|string|max:64',
            'next_action_at' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function validateJobsAddInterview(array $payload): void
    {
        $validator = Validator::make($payload, [
            'job_application_id' => 'required|integer|exists:job_applications,id',
            'scheduled_at' => 'required|date',
            'interview_type' => 'nullable|string|max:64',
            'interviewer_name' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
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
            'subscriptions.create' => $this->executeSubscriptionCreate($action, $user, $attribution),
            'contracts.create' => $this->executeContractCreate($action, $user, $attribution),
            'warranties.create' => $this->executeWarrantyCreate($action, $user, $attribution),
            'iou.create' => $this->executeIouCreate($action, $user, $attribution),
            'utilityBills.create' => $this->executeUtilityBillCreate($action, $user, $attribution),
            'jobs.updateStatus' => $this->executeJobsUpdateStatus($action),
            'jobs.addInterview' => $this->executeJobsAddInterview($action),
            default => throw new RuntimeException("No executor registered for tool [{$action->tool}]."),
        };
    }

    /**
     * @param  array<string, mixed>  $attribution
     * @return array<string, mixed>
     */
    private function executeSubscriptionCreate(PendingAction $action, User $user, array $attribution): array
    {
        $sub = $this->subscriptions->create($user, $action->payload, $attribution);

        $action->forceFill([
            'target_type' => Subscription::class,
            'target_id' => $sub->id,
        ])->save();

        return [
            'before' => null,
            'after' => $sub->only(['id', 'service_name', 'cost', 'currency', 'billing_cycle', 'next_billing_date', 'status']),
        ];
    }

    /**
     * @param  array<string, mixed>  $attribution
     * @return array<string, mixed>
     */
    private function executeContractCreate(PendingAction $action, User $user, array $attribution): array
    {
        $contract = $this->contracts->create($user, $action->payload, $attribution);

        $action->forceFill([
            'target_type' => Contract::class,
            'target_id' => $contract->id,
        ])->save();

        return [
            'before' => null,
            'after' => $contract->only(['id', 'title', 'counterparty', 'contract_type', 'start_date', 'end_date', 'status']),
        ];
    }

    /**
     * @param  array<string, mixed>  $attribution
     * @return array<string, mixed>
     */
    private function executeWarrantyCreate(PendingAction $action, User $user, array $attribution): array
    {
        $warranty = $this->warranties->create($user, $action->payload, $attribution);

        $action->forceFill([
            'target_type' => Warranty::class,
            'target_id' => $warranty->id,
        ])->save();

        return [
            'before' => null,
            'after' => $warranty->only(['id', 'product_name', 'brand', 'purchase_date', 'warranty_expiration_date', 'current_status']),
        ];
    }

    /**
     * @param  array<string, mixed>  $attribution
     * @return array<string, mixed>
     */
    private function executeIouCreate(PendingAction $action, User $user, array $attribution): array
    {
        $iou = $this->ious->create($user, $action->payload, $attribution);

        $action->forceFill([
            'target_type' => Iou::class,
            'target_id' => $iou->id,
        ])->save();

        return [
            'before' => null,
            'after' => $iou->only(['id', 'type', 'person_name', 'amount', 'currency', 'transaction_date', 'due_date', 'status']),
        ];
    }

    /**
     * @param  array<string, mixed>  $attribution
     * @return array<string, mixed>
     */
    private function executeUtilityBillCreate(PendingAction $action, User $user, array $attribution): array
    {
        $bill = $this->bills->create($user, $action->payload, $attribution);

        $action->forceFill([
            'target_type' => UtilityBill::class,
            'target_id' => $bill->id,
        ])->save();

        return [
            'before' => null,
            'after' => $bill->only(['id', 'utility_type', 'service_provider', 'bill_amount', 'currency', 'due_date', 'payment_status']),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function executeJobsUpdateStatus(PendingAction $action): array
    {
        $application = JobApplication::query()->findOrFail((int) $action->payload['job_application_id']);
        $before = $application->only(['id', 'status', 'next_action_at']);

        $nextAt = isset($action->payload['next_action_at'])
            ? new \DateTimeImmutable((string) $action->payload['next_action_at'])
            : null;

        $this->jobs->updateStatus($application, (string) $action->payload['status'], $nextAt);

        $action->forceFill([
            'target_type' => JobApplication::class,
            'target_id' => $application->id,
        ])->save();

        return [
            'before' => $before,
            'after' => $application->refresh()->only(['id', 'status', 'next_action_at']),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function executeJobsAddInterview(PendingAction $action): array
    {
        $application = JobApplication::query()->findOrFail((int) $action->payload['job_application_id']);

        $interview = $this->jobs->addInterview($application, [
            'scheduled_at' => $action->payload['scheduled_at'],
            'interview_type' => $action->payload['interview_type'] ?? null,
            'interviewer_name' => $action->payload['interviewer_name'] ?? null,
            'location' => $action->payload['location'] ?? null,
            'notes' => $action->payload['notes'] ?? null,
        ]);

        $action->forceFill([
            'target_type' => JobApplication::class,
            'target_id' => $application->id,
        ])->save();

        return [
            'before' => null,
            'after' => [
                'job_application_id' => $application->id,
                'interview_id' => $interview->id,
                'scheduled_at' => $interview->scheduled_at?->toIso8601String(),
            ],
        ];
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
            case 'subscriptions.create':
                $this->revertCreateById($action, Subscription::class);

                return;
            case 'contracts.create':
                $this->revertCreateById($action, Contract::class);

                return;
            case 'warranties.create':
                $this->revertCreateById($action, Warranty::class);

                return;
            case 'iou.create':
                $this->revertCreateById($action, Iou::class);

                return;
            case 'utilityBills.create':
                $this->revertCreateById($action, UtilityBill::class);

                return;
            case 'jobs.updateStatus':
                $this->revertJobsUpdateStatus($action);

                return;
            case 'jobs.addInterview':
                $this->revertJobsAddInterview($action);

                return;
        }

        throw new RuntimeException("No revert executor for tool [{$action->tool}].");
    }

    /**
     * @param  class-string<\Illuminate\Database\Eloquent\Model>  $modelClass
     */
    private function revertCreateById(PendingAction $action, string $modelClass): void
    {
        $diff = $action->applied_diff ?? [];
        $after = $diff['after'] ?? null;

        if (! is_array($after) || ! isset($after['id'])) {
            return;
        }

        $modelClass::query()->whereKey((int) $after['id'])->delete();
    }

    private function revertJobsUpdateStatus(PendingAction $action): void
    {
        $diff = $action->applied_diff ?? [];
        $before = $diff['before'] ?? null;

        if (! is_array($before) || ! isset($before['id'])) {
            return;
        }

        $application = JobApplication::query()->find((int) $before['id']);

        if ($application === null) {
            return;
        }

        $payload = ['status' => $before['status']];

        if (array_key_exists('next_action_at', $before)) {
            $payload['next_action_at'] = $before['next_action_at'];
        }

        $this->jobs->update($application, $payload);
    }

    private function revertJobsAddInterview(PendingAction $action): void
    {
        $diff = $action->applied_diff ?? [];
        $after = $diff['after'] ?? null;

        if (! is_array($after) || ! isset($after['interview_id'])) {
            return;
        }

        \App\Models\JobApplicationInterview::query()->whereKey((int) $after['interview_id'])->delete();
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
            'subscriptions.create' => [
                'summary' => sprintf(
                    'Subscribe to %s — %s %s / %s',
                    (string) ($payload['service_name'] ?? '—'),
                    number_format((float) ($payload['cost'] ?? 0), 2),
                    (string) ($payload['currency'] ?? ''),
                    (string) ($payload['billing_cycle'] ?? ''),
                ),
            ],
            'contracts.create' => [
                'summary' => sprintf(
                    '%s with %s (%s → %s)',
                    (string) ($payload['title'] ?? '—'),
                    (string) ($payload['counterparty'] ?? '—'),
                    (string) ($payload['start_date'] ?? '?'),
                    (string) ($payload['end_date'] ?? '?'),
                ),
            ],
            'warranties.create' => [
                'summary' => sprintf(
                    '%s %s (purchased %s, warranty until %s)',
                    (string) ($payload['brand'] ?? ''),
                    (string) ($payload['product_name'] ?? '—'),
                    (string) ($payload['purchase_date'] ?? '?'),
                    (string) ($payload['warranty_expiration_date'] ?? '?'),
                ),
            ],
            'iou.create' => [
                'summary' => sprintf(
                    '%s %s %s %s',
                    ($payload['type'] ?? '') === 'owe' ? 'I owe' : 'Owed by',
                    (string) ($payload['person_name'] ?? '—'),
                    number_format((float) ($payload['amount'] ?? 0), 2),
                    (string) ($payload['currency'] ?? ''),
                ),
            ],
            'utilityBills.create' => [
                'summary' => sprintf(
                    '%s %s — %s %s due %s',
                    (string) ($payload['service_provider'] ?? '—'),
                    (string) ($payload['utility_type'] ?? ''),
                    number_format((float) ($payload['bill_amount'] ?? 0), 2),
                    (string) ($payload['currency'] ?? ''),
                    (string) ($payload['due_date'] ?? ''),
                ),
            ],
            'jobs.updateStatus' => [
                'summary' => sprintf(
                    'Set application #%d → %s',
                    (int) ($payload['job_application_id'] ?? 0),
                    (string) ($payload['status'] ?? ''),
                ),
            ],
            'jobs.addInterview' => [
                'summary' => sprintf(
                    'Schedule interview for #%d at %s',
                    (int) ($payload['job_application_id'] ?? 0),
                    (string) ($payload['scheduled_at'] ?? ''),
                ),
            ],
            default => [],
        };
    }
}
