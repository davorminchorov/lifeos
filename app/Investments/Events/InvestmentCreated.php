<?php

namespace App\Investments\Events;

use App\Core\EventSourcing\StoredEvent;

class InvestmentCreated extends StoredEvent
{
    public function __construct(
        public readonly string $investmentId,
        public readonly string $name,
        public readonly string $type,
        public readonly string $institution,
        public readonly ?string $accountNumber,
        public readonly float $initialInvestment,
        public readonly string $startDate,
        public readonly ?string $endDate,
        public readonly ?string $description
    ) {
    }
}
