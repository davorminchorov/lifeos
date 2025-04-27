<?php

namespace App\UtilityBills\Queries\Handlers;

use App\Core\Queries\QueryHandler;
use App\UtilityBills\Queries\GetPendingBills;
use Illuminate\Support\Facades\DB;

class GetPendingBillsHandler implements QueryHandler
{
    public function __invoke(GetPendingBills $query): array
    {
        $pendingBillsQuery = DB::table('pending_bills');

        if ($query->category) {
            $pendingBillsQuery->where('category', $query->category);
        }

        if ($query->dueDate) {
            $pendingBillsQuery->where('due_date', '<=', $query->dueDate);
        }

        return $pendingBillsQuery->orderBy('due_date', 'asc')->get()->toArray();
    }
}
