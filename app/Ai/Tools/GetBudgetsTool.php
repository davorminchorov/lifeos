<?php

namespace App\Ai\Tools;

use App\Models\Budget;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Auth;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class GetBudgetsTool implements Tool
{
    public function description(): Stringable|string
    {
        return 'Get active budgets and how much has been spent vs. the allocated amount.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [];
    }

    public function handle(Request $request): Stringable|string
    {
        $budgets = Budget::where('user_id', Auth::id())->active()->get();

        if ($budgets->isEmpty()) {
            return 'No active budgets found.';
        }

        return json_encode([
            'count' => $budgets->count(),
            'currency' => config('currency.default', 'MKD'),
            'budgets' => $budgets->map(fn ($b) => [
                'name' => $b->name,
                'amount' => $b->amount,
                'currency' => $b->currency,
                'spent' => $b->spent_amount ?? 0,
                'remaining' => ($b->amount ?? 0) - ($b->spent_amount ?? 0),
                'period_type' => $b->period_type,
                'start_date' => $b->start_date?->format('Y-m-d'),
                'end_date' => $b->end_date?->format('Y-m-d'),
            ])->values(),
        ]);
    }
}
