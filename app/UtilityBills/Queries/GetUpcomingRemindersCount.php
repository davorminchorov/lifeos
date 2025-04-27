<?php

namespace App\UtilityBills\Queries;

use App\UtilityBills\Projections\ReminderList;
use Carbon\Carbon;

class GetUpcomingRemindersCount
{
    public function handle(): int
    {
        $now = Carbon::now();
        $sevenDaysFromNow = $now->copy()->addDays(7);

        return ReminderList::where('status', 'scheduled')
            ->whereBetween('reminder_date', [$now->toDateString(), $sevenDaysFromNow->toDateString()])
            ->count();
    }
}
