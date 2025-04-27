<?php

namespace App\Subscriptions\Queries;

use App\Subscriptions\Domain\SubscriptionStatus;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GetUpcomingPayments
{
    /**
     * Get upcoming subscription payments within the specified days
     *
     * @param int $days Number of days to look ahead
     * @return Collection
     */
    public function __invoke(int $days = 30): Collection
    {
        $today = Carbon::today();
        $endDate = Carbon::today()->addDays($days);

        return DB::table('subscriptions_read_model')
            ->where('status', SubscriptionStatus::ACTIVE->value)
            ->whereNotNull('next_payment_date')
            ->where('next_payment_date', '>=', $today->toDateString())
            ->where('next_payment_date', '<=', $endDate->toDateString())
            ->orderBy('next_payment_date')
            ->get();
    }
}
