<?php

namespace App\UtilityBills\Queries\Handlers;

use App\Core\Queries\QueryHandler;
use App\UtilityBills\Queries\GetBills;
use Illuminate\Support\Facades\DB;

class GetBillsHandler implements QueryHandler
{
    public function __invoke(GetBills $query): array
    {
        $billsQuery = DB::table('utility_bills');

        if ($query->filters) {
            if (isset($query->filters['category'])) {
                $billsQuery->where('category', $query->filters['category']);
            }

            if (isset($query->filters['status'])) {
                $billsQuery->where('status', $query->filters['status']);
            }

            if (isset($query->filters['is_recurring'])) {
                $billsQuery->where('is_recurring', $query->filters['is_recurring']);
            }

            if (isset($query->filters['due_date_from'])) {
                $billsQuery->where('due_date', '>=', $query->filters['due_date_from']);
            }

            if (isset($query->filters['due_date_to'])) {
                $billsQuery->where('due_date', '<=', $query->filters['due_date_to']);
            }

            if (isset($query->filters['provider'])) {
                $billsQuery->where('provider', 'like', '%' . $query->filters['provider'] . '%');
            }

            if (isset($query->filters['name'])) {
                $billsQuery->where('name', 'like', '%' . $query->filters['name'] . '%');
            }
        }

        return $billsQuery->orderBy('due_date', 'asc')->get()->toArray();
    }
}
