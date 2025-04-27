<?php

namespace App\Investments\Commands;

class RecordTransaction
{
    public function __construct(
        public readonly string $investmentId,
        public readonly string $transactionId,
        public readonly string $type,
        public readonly float $amount,
        public readonly string $date,
        public readonly ?string $notes = null
    ) {
    }
}
