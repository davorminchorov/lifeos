<?php

namespace App\UtilityBills\Queries;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GetUpcomingBills
{
    /**
     * Get upcoming utility bills due within the specified days
     *
     * @param int $days Number of days to look ahead
     * @return Collection
     */
    public function __invoke(int $days = 30): Collection
    {
        $today = Carbon::today();
        $endDate = Carbon::today()->addDays($days);

        return DB::table('utility_bills_read_model')
            ->where('status', '!=', 'paid')
            ->where('due_date', '>=', $today->toDateString())
            ->where('due_date', '<=', $endDate->toDateString())
            ->orderBy('due_date')
            ->get();
    }
}
