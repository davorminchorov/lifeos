<?php

namespace App\Ai\Tools\Expenses;

use App\Ai\Tools\Concerns\ResolvesContext;
use App\Models\Expense;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;

class SpendingSummaryTool implements Tool
{
    use ResolvesContext;

    public function description(): string
    {
        return 'Get a spending summary broken down by category. Use when the user asks about their spending, how much they spent, or wants a financial overview.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'period' => $schema->string()->description('Period: "week", "month", "year". Default: "month"'),
        ];
    }

    public function handle(Request $request): string
    {
        $period = $request['period'] ?? 'month';

        $startDate = match ($period) {
            'week' => now()->startOfWeek(),
            'year' => now()->startOfYear(),
            default => now()->startOfMonth(),
        };

        $expenses = Expense::where('tenant_id', $this->tenantId())
            ->where('expense_date', '>=', $startDate)
            ->get();

        if ($expenses->isEmpty()) {
            return "No expenses found for this {$period}.";
        }

        $byCategory = $expenses->groupBy('category');
        $total = $expenses->sum('amount');

        $periodLabel = match ($period) {
            'week' => 'This week ('.$startDate->format('M j').' - '.now()->format('M j').')',
            'year' => 'This year ('.now()->year.')',
            default => 'This month ('.now()->format('F Y').')',
        };

        $lines = ["Spending Summary — {$periodLabel}:"];

        $sorted = $byCategory->map(fn ($items) => $items->sum('amount'))->sortDesc();

        foreach ($sorted as $category => $amount) {
            $pct = round(($amount / $total) * 100);
            $lines[] = "  {$category}: ".$this->formatAmount($amount)." ({$pct}%)";
        }

        $lines[] = 'Total: '.$this->formatAmount($total);
        $lines[] = 'Transactions: '.$expenses->count();

        return implode("\n", $lines);
    }
}
