<?php

namespace App\Subscriptions\Queries;

use App\Core\EventSourcing\QueryHandler;
use Illuminate\Support\Facades\DB;

class GetUpcomingPaymentsHandler implements QueryHandler
{
    public function handle(GetUpcomingPayments $query): array
    {
        $today = now()->format('Y-m-d');
        $futureDate = now()->addDays($query->daysAhead)->format('Y-m-d');

        $upcomingPayments = DB::table('upcoming_payments')
            ->join('subscriptions', 'upcoming_payments.subscription_id', '=', 'subscriptions.id')
            ->where('upcoming_payments.expected_date', '>=', $today)
            ->where('upcoming_payments.expected_date', '<=', $futureDate)
            ->where('subscriptions.status', 'active')
            ->orderBy('upcoming_payments.expected_date', 'asc')
            ->select([
                'subscriptions.id',
                'subscriptions.name',
                'subscriptions.amount',
                'subscriptions.currency',
                'subscriptions.billing_cycle',
                'upcoming_payments.expected_date as payment_date',
            ])
            ->get()
            ->map(function ($payment) {
                return [
                    'subscription_id' => $payment->id,
                    'name' => $payment->name,
                    'amount' => (float) $payment->amount,
                    'currency' => $payment->currency,
                    'billing_cycle' => $payment->billing_cycle,
                    'payment_date' => $payment->payment_date,
                    'days_until' => now()->diffInDays(new \DateTime($payment->payment_date), false),
                ];
            })
            ->toArray();

        // Group by date for easier display
        $groupedByDate = [];
        foreach ($upcomingPayments as $payment) {
            $date = $payment['payment_date'];
            if (!isset($groupedByDate[$date])) {
                $groupedByDate[$date] = [];
            }
            $groupedByDate[$date][] = $payment;
        }

        // Calculate daily totals
        $dailyTotals = [];
        foreach ($groupedByDate as $date => $payments) {
            $totals = [];
            foreach ($payments as $payment) {
                $currency = $payment['currency'];
                if (!isset($totals[$currency])) {
                    $totals[$currency] = 0;
                }
                $totals[$currency] += $payment['amount'];
            }
            $dailyTotals[$date] = $totals;
        }

        return [
            'payments' => $upcomingPayments,
            'daily_totals' => $dailyTotals,
            'grouped_by_date' => $groupedByDate,
        ];
    }
}
