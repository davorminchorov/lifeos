<?php

namespace App\Expenses\Domain;

use App\Core\Domain\AggregateRoot;
use App\Expenses\Commands\CategorizeExpense;
use App\Expenses\Commands\RecordExpense;
use App\Expenses\Events\ExpenseCategorized;
use App\Expenses\Events\ExpenseRecorded;
use Carbon\Carbon;

class Expense extends AggregateRoot
{
    public string $expenseId;
    public string $description;
    public float $amount;
    public ?string $categoryId;
    public Carbon $date;
    public ?string $notes;

    public static function recordExpense(RecordExpense $command): static
    {
        $expense = new static($command->expenseId);

        $expense->recordThat(new ExpenseRecorded(
            $command->expenseId,
            $command->description,
            $command->amount,
            $command->categoryId,
            $command->date ?? Carbon::now(),
            $command->notes,
        ));

        return $expense;
    }

    public function categorizeExpense(CategorizeExpense $command): static
    {
        $this->recordThat(new ExpenseCategorized(
            $command->expenseId,
            $command->categoryId,
            $this->categoryId,
        ));

        return $this;
    }

    protected function applyExpenseRecorded(ExpenseRecorded $event): void
    {
        $this->expenseId = $event->expenseId;
        $this->description = $event->description;
        $this->amount = $event->amount;
        $this->categoryId = $event->categoryId;
        $this->date = $event->date;
        $this->notes = $event->notes;
    }

    protected function applyExpenseCategorized(ExpenseCategorized $event): void
    {
        $this->categoryId = $event->categoryId;
    }
}
