<?php

namespace App\Expenses\Commands;

use App\Core\EventSourcing\Command;
use Carbon\Carbon;

class RecordExpense implements Command
{
    public function __construct(
        public readonly string $expenseId,
        public readonly string $title,
        public readonly float $amount,
        public readonly ?string $categoryId,
        public readonly string $date,
        public readonly ?string $description = null,
        public readonly ?string $paymentMethod = null,
        public readonly ?string $notes = null,
        public readonly string $currency = 'USD',
        public readonly ?string $receiptUrl = null
    ) {}
}
