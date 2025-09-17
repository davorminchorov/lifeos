<?php

namespace App\Observers;

use App\Events\ExpenseSaved;
use App\Models\Expense;

class ExpenseObserver
{
    public function created(Expense $expense): void
    {
        event(new ExpenseSaved($expense, true));
    }

    public function updated(Expense $expense): void
    {
        event(new ExpenseSaved($expense, false));
    }
}
