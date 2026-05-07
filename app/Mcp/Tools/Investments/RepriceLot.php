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

class RepriceLot extends AbstractTool
{
    protected string $name = 'investments.repriceLot';

    protected string $description = 'Mark-to-market: update an investment\'s current per-share value. One pending action per (investment, as_of date).';

    public function schema(JsonSchema $schema): array
    {
        return [
            'investment_id' => $schema->integer()->description('Investment id. Required.'),
            'current_value' => $schema->number()->description('New per-share value. Required.'),
            'as_of' => $schema->string()->description('YYYY-MM-DD. Defaults to today.'),
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
            'current_value' => $request->get('current_value'),
            'as_of' => $request->get('as_of') ?? date('Y-m-d'),
        ], static fn ($v) => $v !== null);

        try {
            $action = $applier->record(
                token: $this->agentToken(),
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
