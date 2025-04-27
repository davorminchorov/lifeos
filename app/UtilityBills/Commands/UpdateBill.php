<?php

namespace App\UtilityBills\Commands;

use App\Core\Commands\Command;

class UpdateBill implements Command
{
    public function __construct(
        public readonly string $billId,
        public readonly ?string $name,
        public readonly ?string $provider,
        public readonly ?float $amount,
        public readonly ?string $dueDate,
        public readonly ?string $category,
        public readonly ?bool $isRecurring,
        public readonly ?string $recurrencePeriod,
        public readonly ?string $notes
    ) {
    }
}
