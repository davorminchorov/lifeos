<?php

namespace App\Expenses\Commands;

use App\Core\Commands\Command;
use Illuminate\Support\Carbon;

class RecordExpense extends Command
{
    public function __construct(
        public readonly string $expenseId,
        public readonly string $description,
        public readonly float $amount,
        public readonly ?string $categoryId,
        public readonly ?Carbon $date,
        public readonly ?string $notes,
    ) {
    }
}
