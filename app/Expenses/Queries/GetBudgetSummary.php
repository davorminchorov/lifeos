<?php

namespace App\Expenses\Queries;

use Illuminate\Support\Facades\DB;

class GetBudgetSummary
{
    public function handle(?string $categoryId = null): array
    {
        $query = DB::table('budget_performance')
            ->select([
                'budget_id',
                'category_id',
                'budget_amount',
                'current_spending',
                'status',
                'start_date',
                'end_date',
                DB::raw('(budget_amount - current_spending) as remaining'),
                DB::raw('(current_spending / budget_amount * 100) as percentage_used'),
            ])
            ->where('status', 'active')
            ->where('end_date', '>=', now());

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        $budgets = $query->get()
            ->map(function ($budget) {
                return (array) $budget;
            })
            ->toArray();

        return $budgets;
    }
}
