<?php

namespace App\Expenses\Events;

use App\Core\Events\DomainEvent;
use Carbon\Carbon;

class BudgetSet extends DomainEvent
{
    public function __construct(
        public readonly string $budgetId,
        public readonly ?string $categoryId,
        public readonly float $amount,
        public readonly Carbon $startDate,
        public readonly Carbon $endDate,
        public readonly ?string $notes,
    ) {
    }
}
