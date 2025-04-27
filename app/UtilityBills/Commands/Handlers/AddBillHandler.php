<?php

namespace App\UtilityBills\Commands\Handlers;

use App\Core\Commands\CommandHandler;
use App\Core\EventSourcing\EventStore;
use App\UtilityBills\Commands\AddBill;
use App\UtilityBills\Domain\UtilityBill;

class AddBillHandler implements CommandHandler
{
    public function __construct(
        private EventStore $eventStore
    ) {
    }

    public function __invoke(AddBill $command): void
    {
        $bill = UtilityBill::create(
            $command->billId,
            $command->name,
            $command->provider,
            $command->amount,
            $command->dueDate,
            $command->category,
            $command->isRecurring,
            $command->recurrencePeriod,
            $command->notes
        );

        $this->eventStore->store($bill);
    }
}
