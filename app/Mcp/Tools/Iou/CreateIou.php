<?php

declare(strict_types=1);

namespace App\Mcp\Tools\Iou;

use App\Mcp\Tools\AbstractTool;
use App\Models\PendingAction;
use App\Services\Agents\PendingActionApplier;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;

class CreateIou extends AbstractTool
{
    protected string $name = 'iou.create';

    protected string $description = 'Record a money-owed entry, in either direction, for the authenticated tenant. Queued as a pending action.';

    public function schema(JsonSchema $schema): array
    {
        return [
            'type' => $schema->string()->description('"owe" (you owe) or "owed" (someone owes you). Required.'),
            'person_name' => $schema->string()->description('Counterparty name. Required.'),
            'amount' => $schema->number()->description('Total amount. Required.'),
            'currency' => $schema->string()->description('ISO 4217 3-letter code, defaults to MKD.'),
            'transaction_date' => $schema->string()->description('YYYY-MM-DD. Required.'),
            'due_date' => $schema->string()->description('YYYY-MM-DD.'),
            'description' => $schema->string()->description('Free-text description. Required.'),
            'category' => $schema->string()->description('Category tag.'),
            'source_email_id' => $schema->string()->description('Optional Gmail message id for idempotency.'),
        ];
    }

    public function handle(Request $request, PendingActionApplier $applier): Response|ResponseFactory
    {
        if ($error = $this->authorize()) {
            return $error;
        }

        $payload = array_filter([
            'type' => $request->get('type'),
            'person_name' => $request->get('person_name'),
            'amount' => $request->get('amount'),
            'currency' => $request->get('currency', 'MKD'),
            'transaction_date' => $request->get('transaction_date'),
            'due_date' => $request->get('due_date'),
            'description' => $request->get('description'),
            'category' => $request->get('category'),
            'status' => 'pending',
            'source_email_id' => $request->get('source_email_id'),
        ], static fn ($v) => $v !== null);

        try {
            $action = $applier->record(
                token: $this->agentToken(),
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
