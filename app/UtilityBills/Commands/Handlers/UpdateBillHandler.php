<?php

namespace App\UtilityBills\Commands\Handlers;

use App\Core\Commands\CommandHandler;
use App\Core\EventSourcing\EventStore;
use App\UtilityBills\Commands\UpdateBill;
use App\UtilityBills\Domain\UtilityBill;

class UpdateBillHandler implements CommandHandler
{
    public function __construct(
        private EventStore $eventStore
    ) {
    }

    public function __invoke(UpdateBill $command): void
    {
        $bill = $this->eventStore->load(UtilityBill::class, $command->billId);

        $bill->update(
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
