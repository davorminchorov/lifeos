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

class BulkImportTransactions extends AbstractTool
{
    protected string $name = 'investments.bulkImportTransactions';

    protected string $description = 'Queue a batch of investment transactions parsed from a brokerage statement as a single pending action. Each item validates the same way as investments.recordTransaction.';

    public function schema(JsonSchema $schema): array
    {
        return [
            'items' => $schema->array()->description('Array of transaction payloads. Each item uses the same shape as investments.recordTransaction.'),
        ];
    }

    public function handle(Request $request, PendingActionApplier $applier): Response|ResponseFactory
    {
        if ($error = $this->authorize()) {
            return $error;
        }

        $items = (array) $request->get('items', []);

        if ($items === []) {
            return Response::error('items must contain at least one transaction.');
        }

        $normalized = [];

        foreach ($items as $i => $item) {
            $row = (array) $item;
            $invId = (int) ($row['investment_id'] ?? 0);

            if ($invId <= 0 || Investment::query()->find($invId) === null) {
                return Response::error("items.{$i}: investment [{$invId}] not found in this tenant.");
            }

            $normalized[] = array_filter($row, static fn ($v) => $v !== null);
        }

        try {
            $action = $applier->record(
                token: $this->agentToken(),
                tool: $this->name(),
                action: PendingAction::ACTION_BULK_CREATE,
                payload: ['items' => $normalized],
            );
        } catch (\Throwable $e) {
            return Response::error($e->getMessage());
        }

        return Response::structured([
            'pending_action_id' => $action->id,
            'status' => $action->status,
            'idempotency_key' => $action->idempotency_key,
            'item_count' => count($normalized),
            'auto_applied' => $action->status === PendingAction::STATUS_APPLIED,
        ]);
    }
}
