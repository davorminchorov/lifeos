<?php

namespace App\Investments\Commands;

class UpdateValuation
{
    public function __construct(
        public readonly string $investmentId,
        public readonly float $newValue,
        public readonly string $valuationDate,
        public readonly ?string $notes = null
    ) {
    }
}
