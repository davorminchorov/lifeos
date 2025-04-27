<?php

namespace App\Investments\Events;

use App\Core\EventSourcing\StoredEvent;

class TransactionRecorded extends StoredEvent
{
    public function __construct(
        public readonly string $investmentId,
        public readonly string $transactionId,
        public readonly string $type,
        public readonly float $amount,
        public readonly string $date,
        public readonly ?string $notes
    ) {
    }
}
