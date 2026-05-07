<?php

declare(strict_types=1);

namespace App\Mcp\Tools\Investments;

use App\Mcp\Tools\AbstractTool;
use App\Models\Investment;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;

class Portfolio extends AbstractTool
{
    protected string $name = 'investments.portfolio';

    protected string $description = 'Return the authenticated tenant\'s investment portfolio: positions, totals by currency, and last price-update timestamp.';

    public function schema(JsonSchema $schema): array
    {
        return [
            'investment_type' => $schema->string()->description('Filter by investment_type (e.g. "stock", "etf", "fund").'),
        ];
    }

    public function handle(Request $request): Response|ResponseFactory
    {
        if ($error = $this->authorize()) {
            return $error;
        }

        $query = Investment::query()->orderBy('name');

        if ($type = $request->get('investment_type')) {
            $query->where('investment_type', $type);
        }

        $positions = $query->get()->map(function (Investment $i): array {
            $costBasis = (float) $i->quantity * (float) $i->purchase_price;
            $marketValue = (float) $i->quantity * (float) $i->current_value;

            return [
                'id' => $i->id,
                'name' => $i->name,
                'symbol_identifier' => $i->symbol_identifier,
                'investment_type' => $i->investment_type,
                'quantity' => (float) $i->quantity,
                'purchase_price' => (float) $i->purchase_price,
                'current_value' => (float) $i->current_value,
                'currency' => $i->currency,
                'cost_basis' => round($costBasis, 2),
                'market_value' => round($marketValue, 2),
                'unrealized_gain_loss' => round($marketValue - $costBasis, 2),
                'account_broker' => $i->account_broker,
                'last_price_update' => $i->last_price_update?->toDateString(),
                'status' => $i->status,
            ];
        });

        $totalsByCurrency = $positions
            ->groupBy('currency')
            ->map(fn ($group) => [
                'cost_basis' => round($group->sum('cost_basis'), 2),
                'market_value' => round($group->sum('market_value'), 2),
                'unrealized_gain_loss' => round($group->sum('unrealized_gain_loss'), 2),
            ])
            ->all();

        $lastPricedAt = $positions->pluck('last_price_update')->filter()->max();

        return Response::structured([
            'count' => $positions->count(),
            'totals_by_currency' => $totalsByCurrency,
            'last_priced_at' => $lastPricedAt,
            'positions' => $positions->all(),
        ]);
    }
}
