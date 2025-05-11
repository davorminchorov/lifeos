<?php

namespace App\Subscriptions\Queries;

use App\Core\EventSourcing\QueryHandler;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class GetUpcomingRemindersHandler implements QueryHandler
{
    public function handle(GetUpcomingReminders $query): array
    {
        $today = Carbon::today();
        $endDate = $today->copy()->addDays($query->daysAhead);

        $reminders = DB::table('subscription_reminders')
            ->where('reminder_date', '>=', $today->format('Y-m-d'))
            ->where('reminder_date', '<=', $endDate->format('Y-m-d'))
            ->where('sent', false)
            ->orderBy('reminder_date')
            ->get();

        return $reminders->map(function ($reminder) {
            return [
                'subscription_id' => $reminder->subscription_id,
                'subscription_name' => $reminder->subscription_name,
                'reminder_date' => $reminder->reminder_date,
                'payment_date' => $reminder->payment_date,
                'amount' => (float) $reminder->amount,
                'currency' => $reminder->currency,
                'method' => $reminder->method,
                'days_until_reminder' => Carbon::today()->diffInDays(Carbon::parse($reminder->reminder_date), false),
                'days_until_payment' => Carbon::today()->diffInDays(Carbon::parse($reminder->payment_date), false),
            ];
        })->toArray();
    }
}
