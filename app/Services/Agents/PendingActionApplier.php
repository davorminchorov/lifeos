<?php

declare(strict_types=1);

namespace App\Services\Agents;

use App\Http\Requests\StoreContractRequest;
use App\Http\Requests\StoreExpenseRequest;
use App\Http\Requests\StoreIouRequest;
use App\Http\Requests\StoreSubscriptionRequest;
use App\Http\Requests\StoreUtilityBillRequest;
use App\Http\Requests\StoreWarrantyRequest;
use App\Mail\WeeklyDigestMail;
use App\Models\AgentToken;
use App\Models\BankLine;
use App\Models\Contract;
use App\Models\CycleMenu;
use App\Models\CycleMenuItem;
use App\Models\DigestLog;
use App\Models\Expense;
use App\Models\Investment;
use App\Models\InvestmentDividend;
use App\Models\InvestmentTransaction;
use App\Models\Iou;
use App\Models\JobApplication;
use App\Models\JobApplicationInterview;
use App\Models\PendingAction;
use App\Models\Subscription;
use App\Models\User;
use App\Models\UtilityBill;
use App\Models\Warranty;
use App\Services\Bank\BankReconciliationService;
use App\Services\Contracts\ContractService;
use App\Services\CycleMenu\CycleMenuService;
use App\Services\Expenses\ExpenseService;
use App\Services\Investments\InvestmentService;
use App\Services\Iou\IouService;
use App\Services\Jobs\JobApplicationService;
use App\Services\Subscriptions\SubscriptionService;
use App\Services\UtilityBills\UtilityBillService;
use App\Services\Warranties\WarrantyService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
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
        protected InvestmentService $investments,
        protected BankReconciliationService $bank,
        protected CycleMenuService $cycleMenus,
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
            'jobs.createApplication' => $this->validateJobsCreateApplication($action->payload),
            'investments.recordTransaction' => $this->validateInvestmentsRecordTransaction($action->payload),
            'investments.recordDividend' => $this->validateInvestmentsRecordDividend($action->payload),
            'investments.repriceLot' => $this->validateInvestmentsRepriceLot($action->payload),
            'investments.bulkImportTransactions' => $this->validateInvestmentsBulkImportTransactions($action->payload),
            'bank.recordLines' => $this->validateBankRecordLines($action->payload),
            'bank.linkExpense' => $this->validateBankLinkExpense($action->payload),
            'cycleMenu.addItem' => $this->validateCycleMenuAddItem($action->payload),
            'cycleMenu.setWeek' => $this->validateCycleMenuSetWeek($action->payload),
            'digest.send' => $this->validateDigestSend($action->payload),
            default => throw new RuntimeException("No validator registered for tool [{$action->tool}]."),
        };
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function validateBankRecordLines(array $payload): void
    {
        $lines = (array) ($payload['lines'] ?? []);

        if ($lines === []) {
            throw ValidationException::withMessages(['lines' => 'At least one line is required.']);
        }

        foreach ($lines as $i => $line) {
            $validator = Validator::make((array) $line, [
                'account' => 'required|string|max:128',
                'posted_at' => 'required|date',
                'amount_cents' => 'required|integer|not_in:0',
                'currency' => 'required|string|size:3',
                'merchant_raw' => 'nullable|string|max:255',
                'description' => 'nullable|string|max:1024',
                'balance_after_cents' => 'nullable|integer',
                'statement_id' => 'nullable|string|max:128',
                'statement_row' => 'nullable|integer|min:0',
                'fingerprint' => 'required|string|size:64',
            ]);

            if ($validator->fails()) {
                throw ValidationException::withMessages(["lines.{$i}" => $validator->errors()->all()]);
            }
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function validateCycleMenuAddItem(array $payload): void
    {
        $validator = Validator::make($payload, [
            'cycle_menu_id' => 'required|integer|exists:cycle_menus,id',
            'day_index' => 'required|integer|min:0',
            'title' => 'required|string|max:255',
            'meal_type' => 'required|string|max:32',
            'time_of_day' => 'nullable|string|max:10',
            'quantity' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function validateCycleMenuSetWeek(array $payload): void
    {
        $validator = Validator::make($payload, [
            'cycle_menu_id' => 'required|integer|exists:cycle_menus,id',
            'items_by_day_index' => 'required|array|min:1',
            'items_by_day_index.*' => 'array',
            'items_by_day_index.*.*.title' => 'required|string|max:255',
            'items_by_day_index.*.*.meal_type' => 'required|string|max:32',
            'items_by_day_index.*.*.time_of_day' => 'nullable|string|max:10',
            'items_by_day_index.*.*.quantity' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function validateDigestSend(array $payload): void
    {
        $validator = Validator::make($payload, [
            'week_starts_on' => 'required|date',
            'subject' => 'required|string|max:255',
            'body_text' => 'required|string|max:65535',
            'body_html' => 'nullable|string|max:200000',
            'recipient_email' => 'nullable|email|max:255',
            'structured_summary' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function validateBankLinkExpense(array $payload): void
    {
        $validator = Validator::make($payload, [
            'bank_line_id' => 'required|integer|exists:bank_lines,id',
            'expense_id' => 'required|integer|exists:expenses,id',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function validateInvestmentsRecordTransaction(array $payload): void
    {
        $validator = Validator::make($payload, [
            'investment_id' => 'required|integer|exists:investments,id',
            'transaction_type' => 'required|string|in:buy,sell,dividend_reinvestment,transfer_in,transfer_out,stock_split,stock_dividend',
            'quantity' => 'required|numeric|min:0.00000001',
            'price_per_share' => 'required|numeric|min:0',
            'total_amount' => 'nullable|numeric',
            'fees' => 'nullable|numeric|min:0',
            'taxes' => 'nullable|numeric|min:0',
            'transaction_date' => 'required|date',
            'settlement_date' => 'nullable|date',
            'order_id' => 'nullable|string|max:128',
            'confirmation_number' => 'nullable|string|max:128',
            'broker' => 'nullable|string|max:128',
            'currency' => 'nullable|string|size:3',
            'notes' => 'nullable|string|max:65535',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function validateInvestmentsRecordDividend(array $payload): void
    {
        $validator = Validator::make($payload, [
            'investment_id' => 'required|integer|exists:investments,id',
            'amount' => 'required|numeric|min:0',
            'payment_date' => 'required|date',
            'record_date' => 'nullable|date',
            'ex_dividend_date' => 'nullable|date',
            'dividend_type' => 'nullable|string|max:64',
            'frequency' => 'nullable|string|max:64',
            'dividend_per_share' => 'nullable|numeric|min:0',
            'shares_held' => 'nullable|numeric|min:0',
            'tax_withheld' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|size:3',
            'reinvested' => 'nullable|boolean',
            'notes' => 'nullable|string|max:65535',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function validateInvestmentsRepriceLot(array $payload): void
    {
        $validator = Validator::make($payload, [
            'investment_id' => 'required|integer|exists:investments,id',
            'current_value' => 'required|numeric|min:0',
            'as_of' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function validateInvestmentsBulkImportTransactions(array $payload): void
    {
        $items = (array) ($payload['items'] ?? []);

        if ($items === []) {
            throw ValidationException::withMessages(['items' => 'At least one item is required.']);
        }

        foreach ($items as $i => $item) {
            try {
                $this->validateInvestmentsRecordTransaction((array) $item);
            } catch (ValidationException $e) {
                throw ValidationException::withMessages(["items.{$i}" => $e->errors()]);
            }
        }
    }

    /**
     * @param  class-string<FormRequest>  $formRequestClass
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
    private function validateJobsCreateApplication(array $payload): void
    {
        $validator = Validator::make($payload, [
            'company_name' => 'required|string|max:255',
            'job_title' => 'required|string|max:255',
            'job_description' => 'nullable|string|max:65535',
            'job_url' => 'nullable|url|max:2048',
            'location' => 'nullable|string|max:255',
            'remote' => 'nullable|boolean',
            'salary_min' => 'nullable|numeric|min:0',
            'salary_max' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|size:3',
            'status' => 'nullable|string|max:64',
            'source' => 'nullable|string|max:64',
            'priority' => 'nullable|integer|between:1,5',
            'contact_name' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'notes' => 'nullable|string|max:65535',
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
            'jobs.createApplication' => $this->executeJobsCreateApplication($action, $user),
            'investments.recordTransaction' => $this->executeInvestmentsRecordTransaction($action, $attribution),
            'investments.recordDividend' => $this->executeInvestmentsRecordDividend($action, $attribution),
            'investments.repriceLot' => $this->executeInvestmentsRepriceLot($action),
            'investments.bulkImportTransactions' => $this->executeInvestmentsBulkImportTransactions($action, $attribution),
            'bank.recordLines' => $this->executeBankRecordLines($action, $user, $attribution),
            'bank.linkExpense' => $this->executeBankLinkExpense($action),
            'cycleMenu.addItem' => $this->executeCycleMenuAddItem($action),
            'cycleMenu.setWeek' => $this->executeCycleMenuSetWeek($action),
            'digest.send' => $this->executeDigestSend($action, $user),
            default => throw new RuntimeException("No executor registered for tool [{$action->tool}]."),
        };
    }

    /**
     * @param  array<string, mixed>  $attribution
     * @return array<string, mixed>
     */
    private function executeBankRecordLines(PendingAction $action, User $user, array $attribution): array
    {
        $lines = (array) ($action->payload['lines'] ?? []);

        $createdIds = [];
        $matchedCount = 0;
        $unmatchedCount = 0;
        $skippedCount = 0;

        foreach ($lines as $line) {
            $row = (array) $line;
            $bankLine = $this->bank->ingest($user, $action->tenant_id, $row, $attribution);

            // ingest() is idempotent on fingerprint — check whether it created a
            // brand-new row or returned an existing one. If existing and it's
            // matched, treat it as "skipped".
            $isExisting = ! $bankLine->wasRecentlyCreated;
            if ($isExisting) {
                $skippedCount++;

                continue;
            }

            $createdIds[] = $bankLine->id;

            if ($bankLine->match_status === BankLine::STATUS_MATCHED) {
                $matchedCount++;
            } else {
                $unmatchedCount++;
            }
        }

        return [
            'before' => null,
            'after' => [
                'bank_line_ids' => $createdIds,
                'matched' => $matchedCount,
                'unmatched' => $unmatchedCount,
                'skipped_existing' => $skippedCount,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function executeCycleMenuAddItem(PendingAction $action): array
    {
        $menu = CycleMenu::query()->findOrFail((int) $action->payload['cycle_menu_id']);

        $item = $this->cycleMenus->addItem(
            $menu,
            (int) $action->payload['day_index'],
            [
                'title' => (string) $action->payload['title'],
                'meal_type' => (string) $action->payload['meal_type'],
                'time_of_day' => $action->payload['time_of_day'] ?? null,
                'quantity' => $action->payload['quantity'] ?? null,
            ],
        );

        $action->forceFill([
            'target_type' => CycleMenuItem::class,
            'target_id' => $item->id,
        ])->save();

        return [
            'before' => null,
            'after' => $item->only(['id', 'cycle_menu_day_id', 'title', 'meal_type', 'time_of_day', 'quantity', 'position']),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function executeCycleMenuSetWeek(PendingAction $action): array
    {
        $menu = CycleMenu::query()->findOrFail((int) $action->payload['cycle_menu_id']);

        // Capture the prior state so revert can fully restore the affected
        // day rows to what they looked like before approval.
        $itemsByDayIndex = (array) $action->payload['items_by_day_index'];
        $before = [];

        foreach (array_keys($itemsByDayIndex) as $dayIndex) {
            $day = $menu->days()->where('day_index', (int) $dayIndex)->with('items')->first();
            $before[(int) $dayIndex] = $day === null ? [] : $day->items->map(fn (CycleMenuItem $i): array => [
                'title' => $i->title,
                'meal_type' => is_object($i->meal_type) ? $i->meal_type?->value : $i->meal_type,
                'time_of_day' => $i->time_of_day,
                'quantity' => $i->quantity,
                'position' => $i->position,
            ])->all();
        }

        $created = $this->cycleMenus->replaceWeek($menu, $itemsByDayIndex);

        $action->forceFill([
            'target_type' => CycleMenu::class,
            'target_id' => $menu->id,
        ])->save();

        $afterIds = [];
        foreach ($created as $dayIndex => $items) {
            $afterIds[$dayIndex] = array_map(fn (CycleMenuItem $i) => $i->id, $items);
        }

        return [
            'before' => [
                'menu_id' => $menu->id,
                'items_by_day_index' => $before,
            ],
            'after' => [
                'menu_id' => $menu->id,
                'item_ids_by_day_index' => $afterIds,
            ],
        ];
    }

    /**
     * Send the weekly digest email and record it in digest_logs. Idempotent on
     * the unique (tenant_id, week_starts_on) constraint, so concurrent
     * approvals (or auto-apply re-runs) can't double-send.
     *
     * @return array<string, mixed>
     */
    private function executeDigestSend(PendingAction $action, User $user): array
    {
        $weekStart = (string) $action->payload['week_starts_on'];
        $subject = (string) $action->payload['subject'];
        $bodyText = (string) $action->payload['body_text'];
        $bodyHtml = isset($action->payload['body_html']) ? (string) $action->payload['body_html'] : null;
        $structured = isset($action->payload['structured_summary']) && is_array($action->payload['structured_summary'])
            ? $action->payload['structured_summary']
            : null;
        $recipient = (string) ($action->payload['recipient_email'] ?? $user->email);

        $existing = DigestLog::query()
            ->where('tenant_id', $action->tenant_id)
            ->whereDate('week_starts_on', $weekStart)
            ->first();

        if ($existing !== null) {
            // Already sent this week — short-circuit, return the existing log.
            $action->forceFill([
                'target_type' => DigestLog::class,
                'target_id' => $existing->id,
            ])->save();

            return [
                'before' => null,
                'after' => $existing->only(['id', 'recipient_email', 'subject', 'sent_at']) + ['skipped' => 'already-sent'],
            ];
        }

        Mail::to($recipient)->send(new WeeklyDigestMail(
            weekStartsOn: $weekStart,
            bodyText: $bodyText,
            bodyHtml: $bodyHtml,
            structuredSummary: $structured,
        ));

        $log = DigestLog::create([
            'tenant_id' => $action->tenant_id,
            'user_id' => $user->id,
            'pending_action_id' => $action->id,
            'agent_run_id' => null,
            'week_starts_on' => $weekStart,
            'recipient_email' => $recipient,
            'subject' => $subject,
            'body_text' => $bodyText,
            'body_html' => $bodyHtml,
            'structured_summary' => $structured,
            'sent_at' => now(),
        ]);

        $action->forceFill([
            'target_type' => DigestLog::class,
            'target_id' => $log->id,
        ])->save();

        return [
            'before' => null,
            'after' => $log->only(['id', 'recipient_email', 'subject', 'sent_at']),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function executeBankLinkExpense(PendingAction $action): array
    {
        $bankLine = BankLine::query()->findOrFail((int) $action->payload['bank_line_id']);
        $expense = Expense::query()->findOrFail((int) $action->payload['expense_id']);

        $before = $bankLine->only(['id', 'matched_expense_id', 'match_status', 'match_confidence']);

        $this->bank->linkExpense($bankLine, $expense);

        $action->forceFill([
            'target_type' => BankLine::class,
            'target_id' => $bankLine->id,
        ])->save();

        return [
            'before' => $before,
            'after' => $bankLine->refresh()->only(['id', 'matched_expense_id', 'match_status', 'match_confidence']),
        ];
    }

    /**
     * @param  array<string, mixed>  $attribution
     * @return array<string, mixed>
     */
    private function executeInvestmentsRecordTransaction(PendingAction $action, array $attribution): array
    {
        $investment = Investment::query()->findOrFail((int) $action->payload['investment_id']);

        $transaction = $this->investments->recordTransaction(
            $investment,
            collect($action->payload)->except(['investment_id', 'source_email_id'])->all(),
            $attribution,
        );

        $action->forceFill([
            'target_type' => InvestmentTransaction::class,
            'target_id' => $transaction->id,
        ])->save();

        return [
            'before' => null,
            'after' => $transaction->only(['id', 'investment_id', 'transaction_type', 'quantity', 'price_per_share', 'total_amount', 'transaction_date', 'currency']),
        ];
    }

    /**
     * @param  array<string, mixed>  $attribution
     * @return array<string, mixed>
     */
    private function executeInvestmentsRecordDividend(PendingAction $action, array $attribution): array
    {
        $investment = Investment::query()->findOrFail((int) $action->payload['investment_id']);

        $dividend = $this->investments->recordDividend(
            $investment,
            collect($action->payload)->except(['investment_id', 'source_email_id'])->all(),
            $attribution,
        );

        $action->forceFill([
            'target_type' => InvestmentDividend::class,
            'target_id' => $dividend->id,
        ])->save();

        return [
            'before' => null,
            'after' => $dividend->only(['id', 'investment_id', 'amount', 'payment_date', 'currency']),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function executeInvestmentsRepriceLot(PendingAction $action): array
    {
        $investment = Investment::query()->findOrFail((int) $action->payload['investment_id']);
        $before = $investment->only(['id', 'current_value', 'last_price_update']);

        $asOf = isset($action->payload['as_of'])
            ? new \DateTimeImmutable((string) $action->payload['as_of'])
            : null;

        $this->investments->repriceLot(
            $investment,
            (float) $action->payload['current_value'],
            $asOf,
        );

        $action->forceFill([
            'target_type' => Investment::class,
            'target_id' => $investment->id,
        ])->save();

        return [
            'before' => $before,
            'after' => $investment->refresh()->only(['id', 'current_value', 'last_price_update']),
        ];
    }

    /**
     * @param  array<string, mixed>  $attribution
     * @return array<string, mixed>
     */
    private function executeInvestmentsBulkImportTransactions(PendingAction $action, array $attribution): array
    {
        $rows = (array) ($action->payload['items'] ?? []);
        $created = $this->investments->bulkRecordTransactions($rows, $attribution);

        return [
            'before' => null,
            'after' => array_map(
                fn (InvestmentTransaction $t): array => $t->only(['id', 'investment_id', 'transaction_type', 'quantity', 'price_per_share']),
                $created,
            ),
        ];
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
    private function executeJobsCreateApplication(PendingAction $action, User $user): array
    {
        $payload = collect($action->payload)
            ->except(['source_email_id', 'source_file_id'])
            ->all();

        $application = $this->jobs->create($user, $payload);

        $action->forceFill([
            'target_type' => JobApplication::class,
            'target_id' => $application->id,
        ])->save();

        return [
            'before' => null,
            'after' => $application->only(['id', 'company_name', 'job_title', 'status', 'job_url']),
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
            case 'jobs.createApplication':
                $this->revertCreateById($action, JobApplication::class);

                return;
            case 'investments.recordTransaction':
                $this->revertCreateById($action, InvestmentTransaction::class);

                return;
            case 'investments.recordDividend':
                $this->revertCreateById($action, InvestmentDividend::class);

                return;
            case 'investments.repriceLot':
                $this->revertInvestmentsRepriceLot($action);

                return;
            case 'investments.bulkImportTransactions':
                $this->revertInvestmentsBulkImport($action);

                return;
            case 'bank.recordLines':
                $this->revertBankRecordLines($action);

                return;
            case 'bank.linkExpense':
                $this->revertBankLinkExpense($action);

                return;
            case 'cycleMenu.addItem':
                $this->revertCreateById($action, CycleMenuItem::class);

                return;
            case 'cycleMenu.setWeek':
                $this->revertCycleMenuSetWeek($action);

                return;
            case 'digest.send':
                // Email can't be unsent. Mark the log row reverted so a fresh
                // pending_action can re-send if needed; the unique constraint
                // on (tenant, week) will prevent a duplicate to the same week.
                $diff = $action->applied_diff ?? [];
                $after = $diff['after'] ?? null;
                if (is_array($after) && isset($after['id'])) {
                    DigestLog::query()->whereKey((int) $after['id'])->delete();
                }

                return;
        }

        throw new RuntimeException("No revert executor for tool [{$action->tool}].");
    }

    private function revertBankRecordLines(PendingAction $action): void
    {
        $diff = $action->applied_diff ?? [];
        $after = $diff['after'] ?? null;

        if (! is_array($after) || ! isset($after['bank_line_ids'])) {
            return;
        }

        BankLine::query()->whereIn('id', (array) $after['bank_line_ids'])->delete();
    }

    private function revertCycleMenuSetWeek(PendingAction $action): void
    {
        $diff = $action->applied_diff ?? [];
        $before = $diff['before'] ?? null;

        if (! is_array($before) || ! isset($before['menu_id'], $before['items_by_day_index'])) {
            return;
        }

        $menu = CycleMenu::query()->find((int) $before['menu_id']);

        if ($menu === null) {
            return;
        }

        $this->cycleMenus->replaceWeek($menu, (array) $before['items_by_day_index']);
    }

    private function revertBankLinkExpense(PendingAction $action): void
    {
        $diff = $action->applied_diff ?? [];
        $before = $diff['before'] ?? null;

        if (! is_array($before) || ! isset($before['id'])) {
            return;
        }

        $line = BankLine::query()->find((int) $before['id']);

        if ($line === null) {
            return;
        }

        $line->forceFill([
            'matched_expense_id' => $before['matched_expense_id'] ?? null,
            'match_status' => $before['match_status'] ?? BankLine::STATUS_UNMATCHED,
            'match_confidence' => $before['match_confidence'] ?? null,
        ])->save();
    }

    private function revertInvestmentsRepriceLot(PendingAction $action): void
    {
        $diff = $action->applied_diff ?? [];
        $before = $diff['before'] ?? null;

        if (! is_array($before) || ! isset($before['id'])) {
            return;
        }

        $investment = Investment::query()->find((int) $before['id']);

        if ($investment === null) {
            return;
        }

        $investment->update([
            'current_value' => $before['current_value'] ?? null,
            'last_price_update' => $before['last_price_update'] ?? null,
        ]);
    }

    private function revertInvestmentsBulkImport(PendingAction $action): void
    {
        $diff = $action->applied_diff ?? [];
        $after = $diff['after'] ?? null;

        if (! is_array($after)) {
            return;
        }

        $ids = array_values(array_filter(array_map(
            static fn ($row) => is_array($row) && isset($row['id']) ? (int) $row['id'] : null,
            $after,
        )));

        if ($ids === []) {
            return;
        }

        InvestmentTransaction::query()->whereIn('id', $ids)->delete();
    }

    /**
     * @param  class-string<Model>  $modelClass
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

        JobApplicationInterview::query()->whereKey((int) $after['interview_id'])->delete();
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

        // The default gate requires a previously-approved write with the
        // *same* idempotency key. That works for tools where the user
        // approves a stable-shaped write once and then trusts repetitions
        // (e.g. expenses.create from a recurring receipt).
        //
        // For notification-only tools whose key naturally changes per run
        // (digest.send keys on week_starts_on), the rule is relaxed: any
        // prior applied write of the same tool, by the same tenant, in the
        // last 90 days counts as the user having opted in. This is a
        // narrow, documented per-tool exception.
        if ($this->autoApplyAllowsAnyPriorApproval($tool)) {
            return PendingAction::query()
                ->where('tenant_id', $token->tenant_id)
                ->where('tool', $tool)
                ->where('id', '!=', $newActionId)
                ->where('status', PendingAction::STATUS_APPLIED)
                ->where('applied_at', '>=', now()->subDays(90))
                ->exists();
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
     * Tools where every run uses a fresh idempotency key by design (e.g.
     * digest.send, where the key encodes the ISO week). Listed here so the
     * auto-apply gate accepts "user has approved any prior digest in the
     * last 90 days" as opt-in.
     */
    private function autoApplyAllowsAnyPriorApproval(string $tool): bool
    {
        return in_array($tool, ['digest.send'], true);
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
            'jobs.createApplication' => [
                'summary' => sprintf(
                    '%s — %s%s%s',
                    (string) ($payload['company_name'] ?? '?'),
                    (string) ($payload['job_title'] ?? '?'),
                    isset($payload['location']) ? ' · '.$payload['location'] : '',
                    ($payload['remote'] ?? false) ? ' · remote' : '',
                ),
                'status' => (string) ($payload['status'] ?? 'discovered'),
            ],
            'investments.recordTransaction' => [
                'summary' => sprintf(
                    '%s %s @ %s on investment #%d (%s)',
                    (string) ($payload['transaction_type'] ?? '?'),
                    number_format((float) ($payload['quantity'] ?? 0), 4),
                    number_format((float) ($payload['price_per_share'] ?? 0), 4),
                    (int) ($payload['investment_id'] ?? 0),
                    (string) ($payload['transaction_date'] ?? ''),
                ),
            ],
            'investments.recordDividend' => [
                'summary' => sprintf(
                    'Dividend %s %s on investment #%d (%s)',
                    number_format((float) ($payload['amount'] ?? 0), 2),
                    (string) ($payload['currency'] ?? ''),
                    (int) ($payload['investment_id'] ?? 0),
                    (string) ($payload['payment_date'] ?? ''),
                ),
            ],
            'investments.repriceLot' => [
                'summary' => sprintf(
                    'Reprice investment #%d → %s as of %s',
                    (int) ($payload['investment_id'] ?? 0),
                    number_format((float) ($payload['current_value'] ?? 0), 4),
                    (string) ($payload['as_of'] ?? date('Y-m-d')),
                ),
            ],
            'investments.bulkImportTransactions' => [
                'summary' => sprintf('%d investment transactions', count((array) ($payload['items'] ?? []))),
            ],
            'bank.recordLines' => [
                'summary' => sprintf('Import %d bank line(s) and reconcile against existing expenses', count((array) ($payload['lines'] ?? []))),
            ],
            'bank.linkExpense' => [
                'summary' => sprintf(
                    'Link bank line #%d → expense #%d',
                    (int) ($payload['bank_line_id'] ?? 0),
                    (int) ($payload['expense_id'] ?? 0),
                ),
            ],
            'cycleMenu.addItem' => [
                'summary' => sprintf(
                    'Menu #%d day %d: %s (%s)',
                    (int) ($payload['cycle_menu_id'] ?? 0),
                    (int) ($payload['day_index'] ?? 0),
                    (string) ($payload['title'] ?? '?'),
                    (string) ($payload['meal_type'] ?? ''),
                ),
            ],
            'cycleMenu.setWeek' => [
                'summary' => sprintf(
                    'Plan menu #%d across %d day(s)',
                    (int) ($payload['cycle_menu_id'] ?? 0),
                    count((array) ($payload['items_by_day_index'] ?? [])),
                ),
            ],
            'digest.send' => [
                'summary' => sprintf(
                    'Weekly digest — week of %s · %s',
                    (string) ($payload['week_starts_on'] ?? '?'),
                    (string) ($payload['subject'] ?? ''),
                ),
            ],
            default => [],
        };
    }
}
