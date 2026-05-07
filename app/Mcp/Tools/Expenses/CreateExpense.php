<?php

declare(strict_types=1);

namespace App\Mcp\Tools\Expenses;

use App\Mcp\Tools\AbstractTool;
use App\Models\PendingAction;
use App\Services\Agents\PendingActionApplier;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;

class CreateExpense extends AbstractTool
{
    protected string $name = 'expenses.create';

    protected string $description = 'Create an expense for the authenticated tenant. The write is queued as a pending action awaiting human approval.';

    public function schema(JsonSchema $schema): array
    {
        return [
            'amount' => $schema->number()->description('Amount in expense currency. Required.'),
            'currency' => $schema->string()->description('ISO 4217 3-letter code, defaults to MKD.'),
            'expense_date' => $schema->string()->description('YYYY-MM-DD. Required.'),
            'merchant' => $schema->string()->description('Vendor or store name.'),
            'category' => $schema->string()->description('Expense category. Required.'),
            'subcategory' => $schema->string()->description('Optional subcategory.'),
            'description' => $schema->string()->description('Free-text description. Required.'),
            'payment_method' => $schema->string()->description('Card, cash, transfer, etc.'),
            'expense_type' => $schema->string()->description('"business" or "personal".'),
            'is_tax_deductible' => $schema->boolean()->description('Whether this expense is tax-deductible.'),
            'tags' => $schema->array()->description('Tag strings.'),
            'source_email_id' => $schema->string()->description('Optional Gmail message id used for idempotency disambiguation.'),
            'source_file_id' => $schema->string()->description('Optional Drive file id when this expense was extracted from a receipt scan or PDF.'),
        ];
    }

    public function handle(Request $request, PendingActionApplier $applier): Response|ResponseFactory
    {
        if ($error = $this->authorize()) {
            return $error;
        }

        $token = $this->agentToken();

        $payload = array_filter([
            'amount' => $request->get('amount'),
            'currency' => $request->get('currency', 'MKD'),
            'expense_date' => $request->get('expense_date'),
            'merchant' => $request->get('merchant'),
            'category' => $request->get('category'),
            'subcategory' => $request->get('subcategory'),
            'description' => $request->get('description') ?? $request->get('merchant'),
            'payment_method' => $request->get('payment_method'),
            'expense_type' => $request->get('expense_type'),
            'is_tax_deductible' => $request->get('is_tax_deductible'),
            'tags' => $request->get('tags'),
            'source_email_id' => $request->get('source_email_id'),
            'source_file_id' => $request->get('source_file_id'),
        ], static fn ($v) => $v !== null);

        try {
            $action = $applier->record(
                token: $token,
                tool: $this->name(),
                action: PendingAction::ACTION_CREATE,
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
