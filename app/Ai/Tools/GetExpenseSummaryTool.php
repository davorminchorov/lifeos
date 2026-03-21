<?php

namespace App\Ai\Tools;

use App\Models\Expense;
use App\Services\CurrencyService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Auth;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class GetExpenseSummaryTool implements Tool
{
    public function description(): Stringable|string
    {
        return 'Get expense totals and a breakdown by category for a given time period.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'period' => $schema->string()
                ->enum(['this_month', 'last_month', 'last_3_months', 'last_6_months', 'this_year'])
                ->description('The time period to query')
                ->required(),
        ];
    }

    public function handle(Request $request): Stringable|string
    {
        $currency = resolve(CurrencyService::class);
        $userId = Auth::id();
        [$start, $end] = $this->periodToDates($request['period']);

        $expenses = Expense::where('user_id', $userId)
            ->whereBetween('expense_date', [$start, $end])
            ->get();

        if ($expenses->isEmpty()) {
            return "No expenses found for {$request['period']}.";
        }

        $toDefault = fn ($amount, $cur) => $currency->convertToDefault((float) ($amount ?? 0), $cur ?? config('currency.default', 'MKD'));

        $total = $expenses->sum(fn ($e) => $toDefault($e->amount, $e->currency));

        $byCategory = $expenses->groupBy('category')->map(fn ($group, $cat) => [
            'category' => $cat ?: 'Uncategorized',
            'count' => $group->count(),
            'total' => round($group->sum(fn ($e) => $toDefault($e->amount, $e->currency)), 2),
        ])->sortByDesc('total')->values();

        return json_encode([
            'period' => $request['period'],
            'total' => round($total, 2),
            'currency' => config('currency.default', 'MKD'),
            'count' => $expenses->count(),
            'by_category' => $byCategory,
            'largest' => $expenses->sortByDesc('amount')->first()?->only(['description', 'merchant', 'amount', 'currency', 'expense_date', 'category']),
        ]);
    }

    private function periodToDates(string $period): array
    {
        return match ($period) {
            'last_month' => [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()],
            'last_3_months' => [now()->subMonths(3)->startOfMonth(), now()->endOfMonth()],
            'last_6_months' => [now()->subMonths(6)->startOfMonth(), now()->endOfMonth()],
            'this_year' => [now()->startOfYear(), now()->endOfYear()],
            default => [now()->startOfMonth(), now()->endOfMonth()],
        };
    }
}
