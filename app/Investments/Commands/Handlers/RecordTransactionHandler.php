<?php

namespace App\Investments\Commands\Handlers;

use App\Investments\Commands\RecordTransaction;
use App\Investments\Domain\Investment;

class RecordTransactionHandler
{
    public function handle(RecordTransaction $command): void
    {
        $investment = Investment::retrieve($command->investmentId);

        $investment->recordTransaction(
            $command->transactionId,
            $command->type,
            $command->amount,
            $command->date,
            $command->notes
        );

        $investment->persist();
    }
}
