<?php

declare(strict_types=1);

namespace App\Mcp\Tools\Receipts;

use App\Mcp\Tools\AbstractTool;
use App\Models\PendingAction;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;

/**
 * Returns the set of `source_file_id` values already attached to pending or
 * applied actions for the authenticated tenant. The receipts-OCR agent uses
 * this to skip re-processing Drive files it has already submitted.
 *
 * Idempotency on the write side guarantees re-submission is safe; this tool
 * exists purely as a *cost* optimization (avoid re-running vision over the
 * same image), not as a correctness guarantee.
 */
class ProcessedFiles extends AbstractTool
{
    protected string $name = 'receipts.processed';

    protected string $description = 'List Drive file ids already submitted via any agent write tool for this tenant. Use to skip re-OCR of files seen in earlier runs.';

    public function schema(JsonSchema $schema): array
    {
        return [
            'within_days' => $schema->integer()->description('Only return file ids attached to actions created in the last N days (default 60).'),
            'limit' => $schema->integer()->description('Max ids to return (default 500, max 2000).'),
        ];
    }

    public function handle(Request $request): Response|ResponseFactory
    {
        if ($error = $this->authorize()) {
            return $error;
        }

        $within = (int) ($request->get('within_days') ?? 60);
        $limit = (int) min(max((int) $request->get('limit', 500), 1), 2000);

        $rows = PendingAction::query()
            ->where('created_at', '>=', now()->subDays($within))
            ->whereIn('status', [
                PendingAction::STATUS_PENDING,
                PendingAction::STATUS_APPROVED,
                PendingAction::STATUS_APPLIED,
            ])
            ->orderByDesc('created_at')
            ->limit($limit * 4) // overshoot before deduping
            ->get(['id', 'tool', 'payload', 'created_at']);

        $seen = [];

        foreach ($rows as $row) {
            $payload = is_array($row->payload) ? $row->payload : [];

            // Single-payload tools.
            if (! empty($payload['source_file_id'])) {
                $fileId = (string) $payload['source_file_id'];
                $seen[$fileId] ??= [
                    'source_file_id' => $fileId,
                    'tool' => $row->tool,
                    'pending_action_id' => $row->id,
                    'first_seen_at' => $row->created_at?->toIso8601String(),
                ];
            }

            // Bulk-style tools (e.g. expenses.bulkImport): walk items.
            foreach ((array) ($payload['items'] ?? []) as $item) {
                $itemFileId = is_array($item) ? ($item['source_file_id'] ?? null) : null;

                if ($itemFileId !== null && $itemFileId !== '') {
                    $fileId = (string) $itemFileId;
                    $seen[$fileId] ??= [
                        'source_file_id' => $fileId,
                        'tool' => $row->tool,
                        'pending_action_id' => $row->id,
                        'first_seen_at' => $row->created_at?->toIso8601String(),
                    ];
                }
            }

            if (count($seen) >= $limit) {
                break;
            }
        }

        return Response::structured([
            'count' => count($seen),
            'within_days' => $within,
            'items' => array_values($seen),
        ]);
    }
}
