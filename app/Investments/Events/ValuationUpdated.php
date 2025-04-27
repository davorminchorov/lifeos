<?php

namespace App\Investments\Events;

use App\Core\EventSourcing\StoredEvent;

class ValuationUpdated extends StoredEvent
{
    public function __construct(
        public readonly string $investmentId,
        public readonly float $newValue,
        public readonly string $valuationDate,
        public readonly ?string $notes
    ) {
    }
}
