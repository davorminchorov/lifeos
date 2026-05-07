<?php

declare(strict_types=1);

namespace App\Mcp\Tools\Bank;

use App\Mcp\Tools\AbstractTool;
use App\Models\PendingAction;
use App\Services\Agents\PendingActionApplier;
use App\Services\Bank\BankReconciliationService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;

class RecordLines extends AbstractTool
{
    protected string $name = 'bank.recordLines';

    protected string $description = 'Submit parsed lines from a bank/card statement. The server queues a single pending action; on approval, each line is stored (idempotent on fingerprint) and reconciled against existing expenses. High-confidence matches are linked automatically; the rest are saved as unmatched for human review.';

    public function schema(JsonSchema $schema): array
    {
        return [
            'lines' => $schema->array()->description(
                'Array of parsed lines. Each item: { account: string, posted_at: YYYY-MM-DD, amount_cents: int (negative for debits), currency: ISO 4217, merchant_raw?: string, description?: string, balance_after_cents?: int, statement_id?: string, statement_row?: int }. Fingerprints are computed server-side; if the agent supplies one it is overwritten unless explicitly stable.'
            ),
        ];
    }

    public function handle(
        Request $request,
        PendingActionApplier $applier,
        BankReconciliationService $reconciler,
    ): Response|ResponseFactory {
        if ($error = $this->authorize()) {
            return $error;
        }

        $lines = (array) $request->get('lines', []);

        if ($lines === []) {
            return Response::error('lines must contain at least one entry.');
        }

        $tenantId = (int) $this->agentToken()->tenant_id;
        $normalized = [];

        foreach ($lines as $i => $line) {
            $row = (array) $line;

            foreach (['account', 'posted_at', 'amount_cents', 'currency'] as $required) {
                if (! isset($row[$required])) {
                    return Response::error("lines.{$i}.{$required} is required.");
                }
            }

            $row['fingerprint'] = $reconciler->fingerprint($tenantId, $row);
            $normalized[] = $row;
        }

        try {
            $action = $applier->record(
                token: $this->agentToken(),
                tool: $this->name(),
                action: PendingAction::ACTION_BULK_CREATE,
                payload: ['lines' => $normalized],
            );
        } catch (\Throwable $e) {
            return Response::error($e->getMessage());
        }

        return Response::structured([
            'pending_action_id' => $action->id,
            'status' => $action->status,
            'idempotency_key' => $action->idempotency_key,
            'line_count' => count($normalized),
            'auto_applied' => $action->status === PendingAction::STATUS_APPLIED,
        ]);
    }
}
