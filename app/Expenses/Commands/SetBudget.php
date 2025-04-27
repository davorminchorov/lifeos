<?php

namespace App\Expenses\Commands;

use App\Core\Commands\Command;
use Carbon\Carbon;

class SetBudget extends Command
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
