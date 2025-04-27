<?php

namespace App\Investments\Commands\Handlers;

use App\Investments\Commands\CreateInvestment;
use App\Investments\Domain\Investment;

class CreateInvestmentHandler
{
    public function handle(CreateInvestment $command): void
    {
        $investment = Investment::create(
            $command->investmentId,
            $command->name,
            $command->type,
            $command->institution,
            $command->accountNumber,
            $command->initialInvestment,
            $command->startDate,
            $command->endDate,
            $command->description
        );

        $investment->persist();
    }
}
