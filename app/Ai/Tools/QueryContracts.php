<?php

declare(strict_types=1);

namespace App\Ai\Tools;

use App\Models\Contract;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Tools\Request;

class QueryContracts extends TenantScopedTool
{
    public function description(): string
    {
        return 'Search and filter contracts by title, counterparty, type, or status.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'title' => $schema->string()->description('Filter by contract title'),
            'counterparty' => $schema->string()->description('Filter by counterparty name'),
            'contract_type' => $schema->string()->description('Filter by type (e.g. service, employment, lease, insurance)'),
            'status' => $schema->string()->description('Filter by status: active, expired, terminated, pending'),
        ];
    }

    public function handle(Request $request): string
    {
        $query = $this->scopedQuery(Contract::class);

        $title = $request['title'] ?? null;
        if ($title !== null) {
            $query->where('title', 'LIKE', '%'.$title.'%');
        }

        $counterparty = $request['counterparty'] ?? null;
        if ($counterparty !== null) {
            $query->where('counterparty', 'LIKE', '%'.$counterparty.'%');
        }

        $type = $request['contract_type'] ?? null;
        if ($type !== null) {
            $query->where('contract_type', $type);
        }

        $status = $request['status'] ?? null;
        if ($status !== null) {
            $query->where('status', $status);
        }

        $totalCount = $query->count();
        $contracts = $query->orderBy('end_date')->limit(20)->get();

        if ($contracts->isEmpty()) {
            return 'No contracts found matching your criteria.';
        }

        $lines = $contracts->map(function (Contract $c): string {
            $endInfo = $c->end_date
                ? sprintf('%s (%d days left)', $c->end_date->format('Y-m-d'), $c->days_until_expiration)
                : 'no end date';

            return sprintf(
                '- %s (%s): %s, %s to %s, value: %s [%s]',
                $c->title,
                $c->contract_type,
                $c->counterparty,
                $c->start_date->format('Y-m-d'),
                $endInfo,
                $c->contract_value ? number_format((float) $c->contract_value, 2) : 'N/A',
                $c->status,
            );
        });

        $showing = $contracts->count();

        return "Found {$totalCount} contracts".($totalCount > $showing ? " (showing {$showing})" : '').":\n"
            .$lines->implode("\n");
    }
}
