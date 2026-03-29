<?php

declare(strict_types=1);

namespace App\Ai\Tools;

use App\Models\Expense;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Tools\Request;

class SummarizeSpending extends TenantScopedTool
{
    public function description(): string
    {
        return 'Aggregate spending by category for a given time period.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'period' => $schema->string()->description('Time period: this_month, last_month, this_year, last_30_days. Defaults to this_month'),
        ];
    }

    public function handle(Request $request): string
    {
        $period = $request['period'] ?? 'this_month';
        $now = CarbonImmutable::now();

        [$startDate, $endDate, $label] = match ($period) {
            'last_month' => [
                $now->subMonth()->startOfMonth()->toDateString(),
                $now->subMonth()->endOfMonth()->toDateString(),
                'last month',
            ],
            'this_year' => [
                $now->startOfYear()->toDateString(),
                $now->endOfYear()->toDateString(),
                'this year',
            ],
            'last_30_days' => [
                $now->subDays(30)->toDateString(),
                $now->toDateString(),
                'last 30 days',
            ],
            default => [
                $now->startOfMonth()->toDateString(),
                $now->endOfMonth()->toDateString(),
                'this month',
            ],
        };

        $results = $this->scopedQuery(Expense::class)
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->selectRaw('category, SUM(amount) as total, COUNT(*) as count')
            ->groupBy('category')
            ->orderByDesc('total')
            ->get();

        if ($results->isEmpty()) {
            return "No expenses found for {$label}.";
        }

        $lines = $results->map(
            fn ($row): string => sprintf(
                '%s: %s (%d expenses)',
                $row->category,
                number_format((float) $row->total, 2),
                $row->count,
            ),
        );

        $grandTotal = $results->sum(fn ($row): float => (float) $row->total);

        return "Spending for {$label}:\n"
            .$lines->implode("\n")
            ."\nTotal: ".number_format($grandTotal, 2);
    }
}
