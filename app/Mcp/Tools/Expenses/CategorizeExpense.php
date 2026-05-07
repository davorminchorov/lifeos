<?php

declare(strict_types=1);

namespace App\Mcp\Tools\Expenses;

use App\Mcp\Tools\AbstractTool;
use App\Models\Expense;
use App\Models\PendingAction;
use App\Services\Agents\PendingActionApplier;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;

class CategorizeExpense extends AbstractTool
{
    protected string $name = 'expenses.categorize';

    protected string $description = 'Re-categorize an existing expense. Queued as a pending action awaiting human approval.';

    public function schema(JsonSchema $schema): array
    {
        return [
            'expense_id' => $schema->integer()->description('Expense id (must belong to the authenticated tenant). Required.'),
            'category' => $schema->string()->description('New category. Required.'),
            'subcategory' => $schema->string()->description('Optional subcategory.'),
            'confidence' => $schema->number()->description('Optional 0-1 confidence score the agent attaches to its choice.'),
        ];
    }

    public function handle(Request $request, PendingActionApplier $applier): Response|ResponseFactory
    {
        if ($error = $this->authorize()) {
            return $error;
        }

        $token = $this->agentToken();
        $expenseId = (int) $request->get('expense_id', 0);

        if ($expenseId <= 0) {
            return Response::error('expense_id is required.');
        }

        // Tenant scoping is implicit via the BelongsToTenant global scope —
        // referencing an expense in a different tenant returns null.
        $expense = Expense::query()->find($expenseId);

        if ($expense === null) {
            return Response::error("Expense [{$expenseId}] not found in this tenant.");
        }

        $payload = array_filter([
            'expense_id' => $expense->id,
            'category' => $request->get('category'),
            'subcategory' => $request->get('subcategory'),
            'confidence' => $request->get('confidence'),
        ], static fn ($v) => $v !== null);

        try {
            $action = $applier->record(
                token: $token,
                tool: $this->name(),
                action: PendingAction::ACTION_UPDATE,
                payload: $payload,
            );
        } catch (\Throwable $e) {
            return Response::error($e->getMessage());
        }

        return Response::structured([
            'pending_action_id' => $action->id,
            'status' => $action->status,
            'idempotency_key' => $action->idempotency_key,
            'auto_applied' => $action->status === PendingAction::STATUS_APPLIED,
        ]);
    }
}
