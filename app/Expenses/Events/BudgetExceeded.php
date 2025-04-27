<?php

namespace App\Expenses\Events;

use App\Core\Events\DomainEvent;

class BudgetExceeded extends DomainEvent
{
    public function __construct(
        public readonly string $budgetId,
        public readonly ?string $categoryId,
        public readonly float $budgetAmount,
        public readonly float $currentSpending,
        public readonly float $exceededAmount,
    ) {
    }
}
