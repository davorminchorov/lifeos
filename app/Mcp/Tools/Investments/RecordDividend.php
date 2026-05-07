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

class RecordDividend extends AbstractTool
{
    protected string $name = 'investments.recordDividend';

    protected string $description = 'Record a dividend payment against an existing investment. Queued as a pending action. One dividend per (investment, payment_date, amount).';

    public function schema(JsonSchema $schema): array
    {
        return [
            'investment_id' => $schema->integer()->description('Investment id. Required.'),
            'amount' => $schema->number()->description('Total dividend amount. Required.'),
            'payment_date' => $schema->string()->description('YYYY-MM-DD. Required.'),
            'record_date' => $schema->string()->description('YYYY-MM-DD.'),
            'ex_dividend_date' => $schema->string()->description('YYYY-MM-DD.'),
            'dividend_type' => $schema->string()->description('"ordinary", "qualified", "special", etc.'),
            'frequency' => $schema->string()->description('"quarterly", "annual", "monthly", etc.'),
            'dividend_per_share' => $schema->number()->description('Per-share rate.'),
            'shares_held' => $schema->number()->description('Shares held at record date.'),
            'tax_withheld' => $schema->number()->description('Tax withheld at source.'),
            'currency' => $schema->string()->description('ISO 4217. Defaults to investment currency.'),
            'reinvested' => $schema->boolean()->description('True if dividend was reinvested.'),
            'notes' => $schema->string()->description('Free-text notes.'),
            'source_email_id' => $schema->string()->description('Optional Gmail message id.'),
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
            'amount' => $request->get('amount'),
            'payment_date' => $request->get('payment_date'),
            'record_date' => $request->get('record_date'),
            'ex_dividend_date' => $request->get('ex_dividend_date'),
            'dividend_type' => $request->get('dividend_type'),
            'frequency' => $request->get('frequency'),
            'dividend_per_share' => $request->get('dividend_per_share'),
            'shares_held' => $request->get('shares_held'),
            'tax_withheld' => $request->get('tax_withheld'),
            'currency' => $request->get('currency') ?? $investment->currency,
            'reinvested' => $request->get('reinvested'),
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
