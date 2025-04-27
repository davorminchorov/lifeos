<?php

namespace App\Expenses\Commands;

use App\Core\Commands\Command;

class CategorizeExpense extends Command
{
    public function __construct(
        public readonly string $expenseId,
        public readonly string $categoryId,
    ) {
    }
}
