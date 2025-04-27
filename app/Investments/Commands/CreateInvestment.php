<?php

namespace App\Investments\Commands;

class CreateInvestment
{
    public function __construct(
        public readonly string $investmentId,
        public readonly string $name,
        public readonly string $type,
        public readonly string $institution,
        public readonly ?string $accountNumber,
        public readonly float $initialInvestment,
        public readonly string $startDate,
        public readonly ?string $endDate = null,
        public readonly ?string $description = null
    ) {
    }
}
