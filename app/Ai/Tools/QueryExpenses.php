<?php

declare(strict_types=1);

namespace App\Ai\Tools;

use App\Models\Expense;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Tools\Request;

class QueryExpenses extends TenantScopedTool
{
    public function description(): string
    {
        return 'Search and filter expenses by category, merchant, date range, or amount.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'category' => $schema->string()->description('Filter by expense category'),
            'merchant' => $schema->string()->description('Filter by merchant/vendor name'),
            'date_from' => $schema->string()->description('Start date in YYYY-MM-DD format'),
            'date_to' => $schema->string()->description('End date in YYYY-MM-DD format'),
            'min_amount' => $schema->number()->description('Minimum expense amount'),
            'max_amount' => $schema->number()->description('Maximum expense amount'),
        ];
    }

    public function handle(Request $request): string
    {
        $query = $this->scopedQuery(Expense::class);

        $category = $request['category'] ?? null;
        if ($category !== null) {
            $query->where('category', 'LIKE', '%'.$category.'%');
        }

        $merchant = $request['merchant'] ?? null;
        if ($merchant !== null) {
            $query->where('merchant', 'LIKE', '%'.$merchant.'%');
        }

        $dateFrom = $request['date_from'] ?? null;
        if ($dateFrom !== null) {
            $query->where('expense_date', '>=', $dateFrom);
        }

        $dateTo = $request['date_to'] ?? null;
        if ($dateTo !== null) {
            $query->where('expense_date', '<=', $dateTo);
        }

        $minAmount = $request['min_amount'] ?? null;
        if ($minAmount !== null) {
            $query->where('amount', '>=', $minAmount);
        }

        $maxAmount = $request['max_amount'] ?? null;
        if ($maxAmount !== null) {
            $query->where('amount', '<=', $maxAmount);
        }

        $totalCount = $query->count();
        $expenses = $query->orderByDesc('expense_date')->limit(20)->get();

        if ($expenses->isEmpty()) {
            return 'No expenses found matching your criteria.';
        }

        $lines = $expenses->map(
            fn (Expense $e): string => sprintf(
                '- %s: %s %s at %s (%s)',
                $e->expense_date->format('Y-m-d'),
                number_format((float) $e->amount, 2),
                $e->currency ?? 'MKD',
                $e->merchant ?? 'N/A',
                $e->category,
            ),
        );

        $total = $expenses->sum(fn (Expense $e): float => (float) $e->amount);
        $showing = $expenses->count();

        return "Found {$totalCount} expenses".($totalCount > $showing ? " (showing {$showing})" : '').":\n"
            .$lines->implode("\n")
            ."\nTotal shown: ".number_format($total, 2);
    }
}
