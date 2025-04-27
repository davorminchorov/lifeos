<?php

namespace App\UtilityBills\Commands;

use App\Core\Commands\Command;

class PayBill implements Command
{
    public function __construct(
        public readonly string $billId,
        public readonly string $paymentDate,
        public readonly float $paymentAmount,
        public readonly string $paymentMethod,
        public readonly ?string $notes
    ) {
    }
}
