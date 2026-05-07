<?php

declare(strict_types=1);

namespace App\Mcp\Tools\Investments;

use App\Mcp\Tools\AbstractTool;
use App\Models\Investment;
use App\Models\PendingAction;
use App\Services\Agents\PendingActionApplier;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;

class RecordTransaction extends AbstractTool
{
    protected string $name = 'investments.recordTransaction';

    protected string $description = 'Record a buy / sell / transfer transaction against an existing investment. Queued as a pending action. Idempotency anchors on broker order_id when present.';

    public function schema(JsonSchema $schema): array
    {
        return [
            'investment_id' => $schema->integer()->description('Investment id (must belong to the authenticated tenant). Required.'),
            'transaction_type' => $schema->string()->description('"buy", "sell", "dividend_reinvestment", "transfer_in", "transfer_out", "stock_split", "stock_dividend". Required.'),
            'quantity' => $schema->number()->description('Shares / units transacted. Required.'),
            'price_per_share' => $schema->number()->description('Price per share. Required.'),
            'total_amount' => $schema->number()->description('Computed if not provided.'),
            'fees' => $schema->number()->description('Transaction fees.'),
            'taxes' => $schema->number()->description('Transaction taxes.'),
            'transaction_date' => $schema->string()->description('YYYY-MM-DD. Required.'),
            'settlement_date' => $schema->string()->description('YYYY-MM-DD.'),
            'order_id' => $schema->string()->description('Broker order id (used for idempotency).'),
            'confirmation_number' => $schema->string()->description('Broker confirmation number (fallback idempotency anchor).'),
            'broker' => $schema->string()->description('Broker name.'),
            'currency' => $schema->string()->description('ISO 4217. Defaults to investment currency.'),
            'notes' => $schema->string()->description('Free-text notes.'),
            'source_email_id' => $schema->string()->description('Optional Gmail message id when extracted from a confirm.'),
        ];
    }

    public function handle(Request $request, PendingActionApplier $applier): Response|ResponseFactory
    {
        if ($error = $this->authorize()) {
            return $error;
        }

        $investmentId = (int) $request->get('investment_id', 0);

        if ($investmentId <= 0) {
            return Response::error('investment_id is required.');
        }

        $investment = Investment::query()->find($investmentId);

        if ($investment === null) {
            return Response::error("Investment [{$investmentId}] not found in this tenant.");
        }

        $payload = array_filter([
            'investment_id' => $investment->id,
            'transaction_type' => $request->get('transaction_type'),
            'quantity' => $request->get('quantity'),
            'price_per_share' => $request->get('price_per_share'),
            'total_amount' => $request->get('total_amount'),
            'fees' => $request->get('fees'),
            'taxes' => $request->get('taxes'),
            'transaction_date' => $request->get('transaction_date'),
            'settlement_date' => $request->get('settlement_date'),
            'order_id' => $request->get('order_id'),
            'confirmation_number' => $request->get('confirmation_number'),
            'broker' => $request->get('broker'),
            'currency' => $request->get('currency') ?? $investment->currency,
            'notes' => $request->get('notes'),
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
