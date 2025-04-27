<?php

namespace App\Expenses\Projections;

use App\Core\Projections\Projection;
use App\Expenses\Events\BudgetExceeded;
use App\Expenses\Events\BudgetSet;
use App\Expenses\Events\ExpenseRecorded;
use Illuminate\Support\Facades\DB;

class BudgetPerformance extends Projection
{
    public function __construct()
    {
        $this->handlesEvents([
            BudgetSet::class => 'onBudgetSet',
            ExpenseRecorded::class => 'onExpenseRecorded',
            BudgetExceeded::class => 'onBudgetExceeded',
        ]);
    }

    protected function onBudgetSet(BudgetSet $event): void
    {
        DB::table('budget_performance')->updateOrInsert(
            ['budget_id' => $event->budgetId],
            [
                'category_id' => $event->categoryId,
                'budget_amount' => $event->amount,
                'current_spending' => 0,
                'status' => 'active',
                'start_date' => $event->startDate,
                'end_date' => $event->endDate,
                'updated_at' => now(),
            ]
        );
    }

    protected function onExpenseRecorded(ExpenseRecorded $event): void
    {
        // Find relevant budgets for this expense
        $budgets = DB::table('budget_performance')
            ->where(function ($query) use ($event) {
                $query->where('category_id', $event->categoryId)
                    ->orWhereNull('category_id');
            })
            ->where('start_date', '<=', $event->date)
            ->where('end_date', '>=', $event->date)
            ->where('status', 'active')
            ->get();

        foreach ($budgets as $budget) {
            $newSpending = $budget->current_spending + $event->amount;
            $status = $newSpending > $budget->budget_amount ? 'exceeded' : 'active';

            DB::table('budget_performance')
                ->where('budget_id', $budget->budget_id)
                ->update([
                    'current_spending' => $newSpending,
                    'status' => $status,
                    'updated_at' => now(),
                ]);
        }
    }

    protected function onBudgetExceeded(BudgetExceeded $event): void
    {
        DB::table('budget_performance')
            ->where('budget_id', $event->budgetId)
            ->update([
                'status' => 'exceeded',
                'current_spending' => $event->currentSpending,
                'updated_at' => now(),
            ]);
    }

    public static function getTables(): array
    {
        return [
            'budget_performance' => [
                'budget_id' => 'string',
                'category_id' => 'string|null',
                'budget_amount' => 'float',
                'current_spending' => 'float',
                'status' => 'string',
                'start_date' => 'datetime',
                'end_date' => 'datetime',
                'updated_at' => 'datetime',
            ],
        ];
    }
}
