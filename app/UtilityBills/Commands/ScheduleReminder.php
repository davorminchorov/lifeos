<?php

namespace App\UtilityBills\Commands;

use App\Core\Commands\Command;

class ScheduleReminder implements Command
{
    public function __construct(
        public readonly string $billId,
        public readonly string $reminderDate,
        public readonly string $reminderMessage
    ) {
    }
}
