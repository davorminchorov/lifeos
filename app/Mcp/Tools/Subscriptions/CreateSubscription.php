<?php

declare(strict_types=1);

namespace App\Mcp\Tools\Subscriptions;

use App\Mcp\Tools\AbstractTool;
use App\Models\PendingAction;
use App\Services\Agents\PendingActionApplier;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;

class CreateSubscription extends AbstractTool
{
    protected string $name = 'subscriptions.create';

    protected string $description = 'Create a subscription for the authenticated tenant. Queued as a pending action awaiting human approval.';

    public function schema(JsonSchema $schema): array
    {
        return [
            'service_name' => $schema->string()->description('Service or app name (e.g. "Netflix"). Required.'),
            'description' => $schema->string()->description('Human-readable description.'),
            'category' => $schema->string()->description('Category (streaming, saas, cloud, etc.).'),
            'cost' => $schema->number()->description('Cost per billing cycle. Required.'),
            'currency' => $schema->string()->description('ISO 4217 3-letter code, defaults to MKD.'),
            'billing_cycle' => $schema->string()->description('"monthly", "yearly", "weekly", "custom". Required.'),
            'billing_cycle_days' => $schema->integer()->description('When billing_cycle = "custom".'),
            'start_date' => $schema->string()->description('YYYY-MM-DD. Required.'),
            'next_billing_date' => $schema->string()->description('YYYY-MM-DD.'),
            'payment_method' => $schema->string()->description('Card last-4, transfer, etc.'),
            'auto_renewal' => $schema->boolean()->description('Default true.'),
            'source_email_id' => $schema->string()->description('Optional Gmail message id used for idempotency.'),
        ];
    }

    public function handle(Request $request, PendingActionApplier $applier): Response|ResponseFactory
    {
        if ($error = $this->authorize()) {
            return $error;
        }

        $payload = array_filter([
            'service_name' => $request->get('service_name'),
            'description' => $request->get('description') ?? $request->get('service_name'),
            'category' => $request->get('category'),
            'cost' => $request->get('cost'),
            'currency' => $request->get('currency', 'MKD'),
            'billing_cycle' => $request->get('billing_cycle'),
            'billing_cycle_days' => $request->get('billing_cycle_days'),
            'start_date' => $request->get('start_date'),
            'next_billing_date' => $request->get('next_billing_date'),
            'payment_method' => $request->get('payment_method'),
            'auto_renewal' => $request->get('auto_renewal'),
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
