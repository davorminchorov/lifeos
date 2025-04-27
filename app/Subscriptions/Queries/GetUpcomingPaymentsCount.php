<?php

namespace App\Subscriptions\Queries;

use App\Subscriptions\Projections\UpcomingPayment;
use Carbon\Carbon;

class GetUpcomingPaymentsCount
{
    public function handle(): int
    {
        $now = Carbon::now();
        $thirtyDaysFromNow = $now->copy()->addDays(30);

        return UpcomingPayment::whereBetween('due_date', [$now->toDateString(), $thirtyDaysFromNow->toDateString()])
            ->count();
    }
}
