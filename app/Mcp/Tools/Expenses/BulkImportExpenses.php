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

class BulkImportExpenses extends AbstractTool
{
    protected string $name = 'expenses.bulkImport';

    protected string $description = 'Queue a bulk import of expenses for the authenticated tenant as a single pending action.';

    public function schema(JsonSchema $schema): array
    {
        return [
            'items' => $schema->array()->description('Array of expense payloads. Each item uses the same shape as expenses.create.'),
        ];
    }

    public function handle(Request $request, PendingActionApplier $applier): Response|ResponseFactory
    {
        if ($error = $this->authorize()) {
            return $error;
        }

        $token = $this->agentToken();
        $items = (array) $request->get('items', []);

        if ($items === []) {
            return Response::error('items must contain at least one expense.');
        }

        $normalized = array_map(static function ($item): array {
            $item = (array) $item;
            $item['currency'] ??= 'MKD';
            $item['description'] ??= $item['merchant'] ?? null;

            return array_filter($item, static fn ($v) => $v !== null);
        }, $items);

        try {
            $action = $applier->record(
                token: $token,
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
