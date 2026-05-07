<?php

declare(strict_types=1);

namespace App\Mcp\Tools\Contracts;

use App\Mcp\Tools\AbstractTool;
use App\Models\PendingAction;
use App\Services\Agents\PendingActionApplier;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;

class CreateContract extends AbstractTool
{
    protected string $name = 'contracts.create';

    protected string $description = 'Create a contract record for the authenticated tenant. Queued as a pending action awaiting human approval.';

    public function schema(JsonSchema $schema): array
    {
        return [
            'title' => $schema->string()->description('Contract title. Required.'),
            'counterparty' => $schema->string()->description('Counterparty name. Required.'),
            'contract_type' => $schema->string()->description('Type (lease, employment, service, etc.).'),
            'start_date' => $schema->string()->description('YYYY-MM-DD. Required.'),
            'end_date' => $schema->string()->description('YYYY-MM-DD.'),
            'notice_period_days' => $schema->integer()->description('Notice required before non-renewal.'),
            'auto_renewal' => $schema->boolean()->description('Whether contract auto-renews.'),
            'contract_value' => $schema->number()->description('Total contract value (numeric).'),
            'payment_terms' => $schema->string()->description('Free-text payment terms.'),
            'notes' => $schema->string()->description('Free-text notes.'),
            'source_email_id' => $schema->string()->description('Optional Gmail message id for idempotency.'),
        ];
    }

    public function handle(Request $request, PendingActionApplier $applier): Response|ResponseFactory
    {
        if ($error = $this->authorize()) {
            return $error;
        }

        $payload = array_filter([
            'title' => $request->get('title'),
            'counterparty' => $request->get('counterparty'),
            'contract_type' => $request->get('contract_type'),
            'start_date' => $request->get('start_date'),
            'end_date' => $request->get('end_date'),
            'notice_period_days' => $request->get('notice_period_days'),
            'auto_renewal' => $request->get('auto_renewal'),
            'contract_value' => $request->get('contract_value'),
            'payment_terms' => $request->get('payment_terms'),
            'notes' => $request->get('notes'),
            'status' => 'active',
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
