<?php

namespace App\UtilityBills\Queries;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GetOverdueBills
{
    /**
     * Get overdue utility bills (due date has passed and not paid)
     *
     * @return Collection
     */
    public function __invoke(): Collection
    {
        $today = Carbon::today();

        return DB::table('utility_bills_read_model')
            ->where('status', '!=', 'paid')
            ->where('due_date', '<', $today->toDateString())
            ->orderBy('due_date')
            ->get();
    }
}
