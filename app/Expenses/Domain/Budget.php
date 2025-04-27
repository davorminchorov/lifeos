<?php

namespace App\Expenses\Domain;

use App\Core\Domain\AggregateRoot;
use App\Expenses\Commands\SetBudget;
use App\Expenses\Events\BudgetExceeded;
use App\Expenses\Events\BudgetSet;
use Carbon\Carbon;

class Budget extends AggregateRoot
{
    public string $budgetId;
    public ?string $categoryId;
    public float $amount;
    public Carbon $startDate;
    public Carbon $endDate;
    public ?string $notes;
    public float $currentSpending = 0;

    public static function setBudget(SetBudget $command): static
    {
        $budget = new static($command->budgetId);

        $budget->recordThat(new BudgetSet(
            $command->budgetId,
            $command->categoryId,
            $command->amount,
            $command->startDate,
            $command->endDate,
            $command->notes,
        ));

        return $budget;
    }

    public function trackSpending(float $amount): static
    {
        $this->currentSpending += $amount;

        if ($this->currentSpending > $this->amount) {
            $this->recordThat(new BudgetExceeded(
                $this->budgetId,
                $this->categoryId,
                $this->amount,
                $this->currentSpending,
                $this->currentSpending - $this->amount,
            ));
        }

        return $this;
    }

    protected function applyBudgetSet(BudgetSet $event): void
    {
        $this->budgetId = $event->budgetId;
        $this->categoryId = $event->categoryId;
        $this->amount = $event->amount;
        $this->startDate = $event->startDate;
        $this->endDate = $event->endDate;
        $this->notes = $event->notes;
    }
}
