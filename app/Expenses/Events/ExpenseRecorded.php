<?php

namespace App\Expenses\Events;

use App\Core\Events\DomainEvent;
use Illuminate\Support\Carbon;

class ExpenseRecorded extends DomainEvent
{
    public function __construct(
        public readonly string $expenseId,
        public readonly string $description,
        public readonly float $amount,
        public readonly ?string $categoryId,
        public readonly Carbon $date,
        public readonly ?string $notes,
    ) {
    }
}
