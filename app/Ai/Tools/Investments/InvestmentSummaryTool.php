<?php

namespace App\Ai\Tools\Investments;

use App\Ai\Tools\Concerns\ResolvesContext;
use App\Models\Investment;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;

class InvestmentSummaryTool implements Tool
{
    use ResolvesContext;

    public function description(): string
    {
        return 'Get investment portfolio summary. Use when the user asks about their investments, portfolio, stocks, or returns.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'type' => $schema->string()->description('Filter by type: stocks, bonds, crypto, real_estate, mutual_fund, etf, other'),
        ];
    }

    public function handle(Request $request): string
    {
        $query = Investment::where('tenant_id', $this->tenantId())
            ->where('status', 'active');

        if ($request['type'] ?? null) {
            $query->where('investment_type', $request['type']);
        }

        $investments = $query->get();

        if ($investments->isEmpty()) {
            return 'No active investments found.';
        }

        $totalCost = $investments->sum(fn ($i) => $i->total_cost_basis);
        $totalValue = $investments->sum(fn ($i) => $i->current_market_value);
        $totalDividends = $investments->sum('total_dividends_received');
        $totalGainLoss = $totalValue - $totalCost;
        $gainPct = $totalCost > 0 ? round(($totalGainLoss / $totalCost) * 100, 2) : 0;

        $lines = ['Investment Portfolio Summary:'];
        $lines[] = 'Total invested: '.$this->formatAmount($totalCost);
        $lines[] = 'Current value: '.$this->formatAmount($totalValue);
        $lines[] = 'Unrealized P&L: '.$this->formatAmount($totalGainLoss)." ({$gainPct}%)";
        $lines[] = 'Total dividends: '.$this->formatAmount($totalDividends);
        $lines[] = "\nHoldings ({$investments->count()}):";

        foreach ($investments as $inv) {
            $value = $this->formatAmount($inv->current_market_value);
            $pct = round($inv->unrealized_gain_loss_percentage, 1);
            $sign = $pct >= 0 ? '+' : '';
            $lines[] = "- {$inv->name} ({$inv->investment_type}): {$value} ({$sign}{$pct}%)";
        }

        return implode("\n", $lines);
    }
}
