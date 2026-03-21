<?php

namespace App\Ai\Tools;

use App\Models\Investment;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Auth;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class GetInvestmentsTool implements Tool
{
    public function description(): Stringable|string
    {
        return 'Get investment portfolio summary — current values, returns, and performance by asset type.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [];
    }

    public function handle(Request $request): Stringable|string
    {
        $investments = Investment::where('user_id', Auth::id())->active()->get();

        if ($investments->isEmpty()) {
            return 'No active investments found.';
        }

        $totalValue = $investments->sum('current_value');
        $totalCost = $investments->sum('initial_investment');
        $totalReturn = $totalValue - $totalCost;

        return json_encode([
            'portfolio_value' => round($totalValue, 2),
            'total_invested' => round($totalCost, 2),
            'total_return' => round($totalReturn, 2),
            'return_pct' => $totalCost > 0 ? round(($totalReturn / $totalCost) * 100, 2) : 0,
            'count' => $investments->count(),
            'by_type' => $investments->groupBy('investment_type')->map(fn ($g, $type) => [
                'type' => $type,
                'count' => $g->count(),
                'value' => round($g->sum('current_value'), 2),
                'invested' => round($g->sum('initial_investment'), 2),
            ])->values(),
            'top_performers' => $investments
                ->sortByDesc(fn ($i) => $i->current_value - $i->initial_investment)
                ->take(3)
                ->map(fn ($i) => [
                    'name' => $i->name ?? $i->symbol,
                    'type' => $i->investment_type,
                    'value' => round((float) $i->current_value, 2),
                    'return' => round($i->current_value - $i->initial_investment, 2),
                ])->values(),
        ]);
    }
}
