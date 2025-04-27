<?php

namespace App\UtilityBills\Queries\Handlers;

use App\Core\Queries\QueryHandler;
use App\UtilityBills\Queries\GetUpcomingReminders;
use Illuminate\Support\Facades\DB;

class GetUpcomingRemindersHandler implements QueryHandler
{
    public function __invoke(GetUpcomingReminders $query): array
    {
        $remindersQuery = DB::table('upcoming_reminders');

        if ($query->afterDate) {
            $remindersQuery->where('reminder_date', '>=', $query->afterDate);
        }

        if ($query->beforeDate) {
            $remindersQuery->where('reminder_date', '<=', $query->beforeDate);
        }

        return $remindersQuery->orderBy('reminder_date', 'asc')->get()->toArray();
    }
}
