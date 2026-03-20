<?php

namespace App\Ai\Tools\Budgets;

use App\Ai\Tools\Concerns\ResolvesContext;
use App\Models\Budget;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;

class BudgetSummaryTool implements Tool
{
    use ResolvesContext;

    public function description(): string
    {
        return 'Get budget status and utilization. Use when the user asks about budgets, remaining budget, or if they are over budget.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'category' => $schema->string()->description('Filter by specific category'),
        ];
    }

    public function handle(Request $request): string
    {
        $query = Budget::where('tenant_id', $this->tenantId())
            ->where('is_active', true)
            ->current();

        if ($request['category'] ?? null) {
            $query->where('category', $request['category']);
        }

        $budgets = $query->get();

        if ($budgets->isEmpty()) {
            return 'No active budgets found for the current period.';
        }

        $lines = ['Budget Status:'];

        foreach ($budgets as $budget) {
            $spent = $budget->getCurrentSpending();
            $remaining = $budget->getRemainingAmount();
            $pct = $budget->getUtilizationPercentage();
            $status = $budget->getStatus();

            $statusIcon = match ($status) {
                'exceeded' => '[OVER]',
                'warning' => '[WARNING]',
                default => '[OK]',
            };

            $lines[] = "- {$budget->category}: ".$this->formatAmount($spent).' / '.$this->formatAmount($budget->amount)." ({$pct}%) {$statusIcon}";
            $lines[] = '  Remaining: '.$this->formatAmount($remaining);
        }

        return implode("\n", $lines);
    }
}
