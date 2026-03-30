<?php

declare(strict_types=1);

namespace App\Ai\Tools;

use App\Models\Investment;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Tools\Request;

class QueryInvestments extends TenantScopedTool
{
    public function description(): string
    {
        return 'Search and filter investments by name, symbol, type, status, or risk tolerance.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string()->description('Filter by investment name or symbol'),
            'investment_type' => $schema->string()->description('Filter by type: stock, bond, etf, mutual_fund, crypto, real_estate, commodities, cash, project'),
            'status' => $schema->string()->description('Filter by status: active, sold, pending'),
            'risk_tolerance' => $schema->string()->description('Filter by risk: conservative, moderate, aggressive'),
        ];
    }

    public function handle(Request $request): string
    {
        $query = $this->scopedQuery(Investment::class);

        $name = $request['name'] ?? null;
        if ($name !== null) {
            $query->where(function ($q) use ($name) {
                $q->where('name', 'LIKE', '%'.$name.'%')
                    ->orWhere('symbol_identifier', 'LIKE', '%'.$name.'%');
            });
        }

        $type = $request['investment_type'] ?? null;
        if ($type !== null) {
            $query->where('investment_type', $type);
        }

        $status = $request['status'] ?? null;
        if ($status !== null) {
            $query->where('status', $status);
        }

        $risk = $request['risk_tolerance'] ?? null;
        if ($risk !== null) {
            $query->where('risk_tolerance', $risk);
        }

        $totalCount = $query->count();
        $investments = $query->orderBy('name')->limit(20)->get();

        if ($investments->isEmpty()) {
            return 'No investments found matching your criteria.';
        }

        $lines = $investments->map(
            fn (Investment $i): string => sprintf(
                '- %s %s (%s): %s shares @ %s %s, current value %s, %s [%s]',
                $i->symbol_identifier ?? '',
                $i->name,
                $i->investment_type,
                number_format((float) $i->quantity, 4),
                number_format((float) $i->purchase_price, 2),
                $i->currency ?? 'MKD',
                number_format((float) $i->current_value, 2),
                $i->risk_tolerance ?? 'N/A',
                $i->status,
            ),
        );

        $showing = $investments->count();

        return "Found {$totalCount} investments".($totalCount > $showing ? " (showing {$showing})" : '').":\n"
            .$lines->implode("\n");
    }
}
