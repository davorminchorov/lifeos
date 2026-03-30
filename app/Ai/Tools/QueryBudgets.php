<?php

declare(strict_types=1);

namespace App\Ai\Tools;

use App\Models\Budget;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Tools\Request;

class QueryBudgets extends TenantScopedTool
{
    public function description(): string
    {
        return 'Search and filter budgets by category, period, or active status.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'category' => $schema->string()->description('Filter by budget category'),
            'is_active' => $schema->boolean()->description('Filter by active status'),
            'budget_period' => $schema->string()->description('Filter by period: weekly, monthly, quarterly, yearly'),
        ];
    }

    public function handle(Request $request): string
    {
        $query = $this->scopedQuery(Budget::class);

        $category = $request['category'] ?? null;
        if ($category !== null) {
            $query->where('category', 'LIKE', '%'.$category.'%');
        }

        $isActive = $request['is_active'] ?? null;
        if ($isActive !== null) {
            $query->where('is_active', (bool) $isActive);
        }

        $period = $request['budget_period'] ?? null;
        if ($period !== null) {
            $query->where('budget_period', $period);
        }

        $totalCount = $query->count();
        $budgets = $query->orderByDesc('start_date')->limit(20)->get();

        if ($budgets->isEmpty()) {
            return 'No budgets found matching your criteria.';
        }

        $lines = $budgets->map(function (Budget $b): string {
            $spent = $b->getCurrentSpending();
            $pct = $b->getUtilizationPercentage();
            $status = $b->getStatus();

            return sprintf(
                '- %s (%s): %s/%s %s (%s%%, %s) %s to %s',
                $b->category,
                $b->budget_period,
                number_format((float) $spent, 2),
                number_format((float) $b->amount, 2),
                $b->currency ?? 'MKD',
                $pct,
                $status,
                $b->start_date->format('Y-m-d'),
                $b->end_date->format('Y-m-d'),
            );
        });

        $showing = $budgets->count();

        return "Found {$totalCount} budgets".($totalCount > $showing ? " (showing {$showing})" : '').":\n"
            .$lines->implode("\n");
    }
}
