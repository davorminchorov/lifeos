<?php

namespace App\Investments\Commands\Handlers;

use App\Investments\Commands\UpdateValuation;
use App\Investments\Domain\Investment;

class UpdateValuationHandler
{
    public function handle(UpdateValuation $command): void
    {
        $investment = Investment::retrieve($command->investmentId);

        $investment->updateValuation(
            $command->newValue,
            $command->valuationDate,
            $command->notes
        );

        $investment->persist();
    }
}
