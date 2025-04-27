<?php

namespace App\UtilityBills\Queries\Handlers;

use App\Core\Queries\QueryHandler;
use App\UtilityBills\Queries\GetBillById;
use Illuminate\Support\Facades\DB;

class GetBillByIdHandler implements QueryHandler
{
    public function __invoke(GetBillById $query): ?object
    {
        $bill = DB::table('utility_bills')
            ->where('id', $query->id)
            ->first();

        if (!$bill) {
            return null;
        }

        // Get payment history
        $payments = DB::table('bill_payments')
            ->where('bill_id', $query->id)
            ->orderBy('payment_date', 'desc')
            ->get();

        // Get reminders
        $reminders = DB::table('bill_reminders')
            ->where('bill_id', $query->id)
            ->orderBy('reminder_date', 'desc')
            ->get();

        $bill->payments = $payments;
        $bill->reminders = $reminders;

        return $bill;
    }
}
