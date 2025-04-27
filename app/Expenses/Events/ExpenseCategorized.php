<?php

namespace App\Expenses\Events;

use App\Core\Events\DomainEvent;

class ExpenseCategorized extends DomainEvent
{
    public function __construct(
        public readonly string $expenseId,
        public readonly string $categoryId,
        public readonly ?string $previousCategoryId,
    ) {
    }
}
