<?php

namespace App\UtilityBills\Commands\Handlers;

use App\Core\Commands\CommandHandler;
use App\Core\EventSourcing\EventStore;
use App\UtilityBills\Commands\PayBill;
use App\UtilityBills\Domain\UtilityBill;

class PayBillHandler implements CommandHandler
{
    public function __construct(
        private EventStore $eventStore
    ) {
    }

    public function __invoke(PayBill $command): void
    {
        $bill = $this->eventStore->load(UtilityBill::class, $command->billId);

        $bill->pay(
            $command->paymentDate,
            $command->paymentAmount,
            $command->paymentMethod,
            $command->notes
        );

        $this->eventStore->store($bill);
    }
}
