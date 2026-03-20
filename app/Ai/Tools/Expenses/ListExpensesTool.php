<?php

namespace App\Ai\Tools\Expenses;

use App\Ai\Tools\Concerns\ResolvesContext;
use App\Models\Expense;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;

class ListExpensesTool implements Tool
{
    use ResolvesContext;

    public function description(): string
    {
        return 'List recent expenses. Use when the user asks to see their expenses or recent purchases.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'limit' => $schema->integer()->description('Number of expenses to return (default: 10, max: 25)'),
            'category' => $schema->string()->description('Filter by category'),
            'days' => $schema->integer()->description('Show expenses from last N days (default: 7)'),
        ];
    }

    public function handle(Request $request): string
    {
        $days = $request['days'] ?? 7;
        $limit = min($request['limit'] ?? 10, 25);

        $query = Expense::where('tenant_id', $this->tenantId())
            ->where('expense_date', '>=', now()->subDays($days))
            ->orderBy('expense_date', 'desc');

        if ($request['category'] ?? null) {
            $query->where('category', $request['category']);
        }

        $expenses = $query->limit($limit)->get();

        if ($expenses->isEmpty()) {
            return "No expenses found in the last {$days} days.";
        }

        $total = $expenses->sum('amount');
        $lines = ["Expenses (last {$days} days):"];

        foreach ($expenses as $expense) {
            $amount = $this->formatAmount($expense->amount, $expense->currency);
            $date = $expense->expense_date->format('M j');
            $lines[] = "- {$date}: {$expense->merchant} — {$amount} ({$expense->category})";
        }

        $lines[] = 'Total: '.$this->formatAmount($total);

        return implode("\n", $lines);
    }
}
