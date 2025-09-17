<?php

namespace App\Listeners;

use App\Events\BudgetThresholdCrossed;
use App\Events\ExpenseSaved;
use App\Models\Budget;
use Illuminate\Contracts\Queue\ShouldQueue;

class EvaluateBudgetOnExpenseSaved implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(ExpenseSaved $event): void
    {
        $expense = $event->expense;

        // Find active, current budgets for the expense category and user
        $budgets = Budget::query()
            ->where('user_id', $expense->user_id)
            ->forCategory($expense->category)
            ->active()
            ->current()
            ->get();

        foreach ($budgets as $budget) {
            $status = $budget->getStatus();

            // We will emit an event when budget status indicates warning or exceeded
            if (in_array($status, ['warning', 'exceeded'], true)) {
                event(new BudgetThresholdCrossed($budget, 'up'));
            }
        }
    }
}
