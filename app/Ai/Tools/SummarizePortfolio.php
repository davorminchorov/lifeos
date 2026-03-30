<?php

declare(strict_types=1);

namespace App\Ai\Tools;

use App\Models\Investment;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Tools\Request;

class SummarizePortfolio extends TenantScopedTool
{
    public function description(): string
    {
        return 'Summarize the investment portfolio with total value, gains/losses, and allocation breakdown by type.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [];
    }

    public function handle(Request $request): string
    {
        $investments = $this->scopedQuery(Investment::class)
            ->where('status', 'active')
            ->get();

        if ($investments->isEmpty()) {
            return 'No active investments in your portfolio.';
        }

        $totalCost = $investments->sum(fn (Investment $i): float => (float) $i->total_cost_basis);
        $totalValue = $investments->sum(fn (Investment $i): float => (float) $i->current_market_value);
        $totalGainLoss = $totalValue - $totalCost;
        $totalDividends = $investments->sum(fn (Investment $i): float => (float) $i->total_dividends_received);
        $gainPct = $totalCost > 0 ? round(($totalGainLoss / $totalCost) * 100, 2) : 0;

        $lines = [];
        $lines[] = sprintf(
            'PORTFOLIO SUMMARY: %d active investments',
            $investments->count(),
        );
        $lines[] = sprintf('Total cost basis: %s', number_format($totalCost, 2));
        $lines[] = sprintf('Current market value: %s', number_format($totalValue, 2));
        $lines[] = sprintf(
            'Unrealized gain/loss: %s (%s%%)',
            number_format($totalGainLoss, 2),
            $gainPct,
        );
        $lines[] = sprintf('Total dividends received: %s', number_format($totalDividends, 2));

        $grouped = $investments->groupBy('investment_type');
        $lines[] = '';
        $lines[] = 'ALLOCATION BY TYPE:';

        foreach ($grouped as $type => $group) {
            $typeValue = $group->sum(fn (Investment $i): float => (float) $i->current_market_value);
            $allocationPct = $totalValue > 0 ? round(($typeValue / $totalValue) * 100, 1) : 0;

            $lines[] = sprintf(
                '- %s: %d holdings, %s value (%s%%)',
                $type,
                $group->count(),
                number_format($typeValue, 2),
                $allocationPct,
            );
        }

        return implode("\n", $lines);
    }
}
