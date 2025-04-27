<?php

namespace App\UtilityBills\Queries;

use App\UtilityBills\Projections\BillList;

class GetPendingBillsCount
{
    public function handle(): int
    {
        return BillList::where('status', 'pending')->count();
    }
}
