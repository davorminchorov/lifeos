<?php

namespace App\UtilityBills\Commands\Handlers;

use App\Core\Commands\CommandHandler;
use App\Core\EventSourcing\EventStore;
use App\UtilityBills\Commands\ScheduleReminder;
use App\UtilityBills\Domain\UtilityBill;

class ScheduleReminderHandler implements CommandHandler
{
    public function __construct(
        private EventStore $eventStore
    ) {
    }

    public function __invoke(ScheduleReminder $command): void
    {
        $bill = $this->eventStore->load(UtilityBill::class, $command->billId);

        $bill->scheduleReminder(
            $command->reminderDate,
            $command->reminderMessage
        );

        $this->eventStore->store($bill);
    }
}
