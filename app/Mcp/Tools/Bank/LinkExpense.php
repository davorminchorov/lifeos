<?php

declare(strict_types=1);

namespace App\Mcp\Tools\Bank;

use App\Mcp\Tools\AbstractTool;
use App\Models\BankLine;
use App\Models\Expense;
use App\Models\PendingAction;
use App\Services\Agents\PendingActionApplier;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;

class LinkExpense extends AbstractTool
{
    protected string $name = 'bank.linkExpense';

    protected string $description = 'Manually link a bank line to an existing expense. Use this when the matcher missed a clear match — the agent can call this after reviewing bank.unmatched.';

    public function schema(JsonSchema $schema): array
    {
        return [
            'bank_line_id' => $schema->integer()->description('Bank line id (must belong to the authenticated tenant). Required.'),
            'expense_id' => $schema->integer()->description('Expense id (must belong to the authenticated tenant). Required.'),
        ];
    }

    public function handle(Request $request, PendingActionApplier $applier): Response|ResponseFactory
    {
        if ($error = $this->authorize()) {
            return $error;
        }

        $bankLineId = (int) $request->get('bank_line_id', 0);
        $expenseId = (int) $request->get('expense_id', 0);

        if ($bankLineId <= 0 || $expenseId <= 0) {
            return Response::error('bank_line_id and expense_id are required.');
        }

        if (BankLine::query()->find($bankLineId) === null) {
            return Response::error("Bank line [{$bankLineId}] not found in this tenant.");
        }

        if (Expense::query()->find($expenseId) === null) {
            return Response::error("Expense [{$expenseId}] not found in this tenant.");
        }

        try {
            $action = $applier->record(
                token: $this->agentToken(),
                tool: $this->name(),
                action: PendingAction::ACTION_UPDATE,
                payload: [
                    'bank_line_id' => $bankLineId,
                    'expense_id' => $expenseId,
                ],
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
