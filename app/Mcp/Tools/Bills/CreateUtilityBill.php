<?php

declare(strict_types=1);

namespace App\Mcp\Tools\Bills;

use App\Mcp\Tools\AbstractTool;
use App\Models\PendingAction;
use App\Services\Agents\PendingActionApplier;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;

class CreateUtilityBill extends AbstractTool
{
    protected string $name = 'utilityBills.create';

    protected string $description = 'Record an incoming utility bill for the authenticated tenant. Queued as a pending action awaiting human approval.';

    public function schema(JsonSchema $schema): array
    {
        return [
            'utility_type' => $schema->string()->description('e.g. "electricity", "water", "internet". Required.'),
            'service_provider' => $schema->string()->description('Provider name. Required.'),
            'account_number' => $schema->string()->description('Account number on the bill.'),
            'service_address' => $schema->string()->description('Service address.'),
            'bill_amount' => $schema->number()->description('Bill amount. Required.'),
            'currency' => $schema->string()->description('ISO 4217, defaults to MKD.'),
            'usage_amount' => $schema->number()->description('Usage quantity (e.g. kWh).'),
            'usage_unit' => $schema->string()->description('Unit of usage_amount.'),
            'bill_period_start' => $schema->string()->description('YYYY-MM-DD.'),
            'bill_period_end' => $schema->string()->description('YYYY-MM-DD.'),
            'due_date' => $schema->string()->description('YYYY-MM-DD. Required.'),
            'source_email_id' => $schema->string()->description('Optional Gmail message id for idempotency.'),
        ];
    }

    public function handle(Request $request, PendingActionApplier $applier): Response|ResponseFactory
    {
        if ($error = $this->authorize()) {
            return $error;
        }

        $payload = array_filter([
            'utility_type' => $request->get('utility_type'),
            'service_provider' => $request->get('service_provider'),
            'account_number' => $request->get('account_number'),
            'service_address' => $request->get('service_address'),
            'bill_amount' => $request->get('bill_amount'),
            'currency' => $request->get('currency', 'MKD'),
            'usage_amount' => $request->get('usage_amount'),
            'usage_unit' => $request->get('usage_unit'),
            'bill_period_start' => $request->get('bill_period_start'),
            'bill_period_end' => $request->get('bill_period_end'),
            'due_date' => $request->get('due_date'),
            'payment_status' => 'pending',
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
