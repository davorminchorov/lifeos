<?php

namespace App\Subscriptions\Queries;

use App\Core\EventSourcing\QueryHandler;
use Illuminate\Support\Facades\DB;

class GetSubscriptionDetailHandler implements QueryHandler
{
    public function handle(GetSubscriptionDetail $query): ?array
    {
        $subscription = DB::table('subscriptions')
            ->where('id', $query->subscriptionId)
            ->first();

        if (!$subscription) {
            return null;
        }

        // Get the upcoming payment date if it exists
        $upcomingPayment = DB::table('upcoming_payments')
            ->where('subscription_id', $query->subscriptionId)
            ->first();

        // Get payment history
        $payments = DB::table('payments')
            ->where('subscription_id', $query->subscriptionId)
            ->orderBy('payment_date', 'desc')
            ->get()
            ->map(function ($payment) {
                return [
                    'id' => $payment->id,
                    'amount' => (float) $payment->amount,
                    'payment_date' => $payment->payment_date,
                    'notes' => $payment->notes,
                    'created_at' => $payment->created_at,
                ];
            })
            ->toArray();

        // Calculate total amount paid
        $totalPaid = array_sum(array_column($payments, 'amount'));

        return [
            'id' => $subscription->id,
            'name' => $subscription->name,
            'description' => $subscription->description,
            'amount' => (float) $subscription->amount,
            'currency' => $subscription->currency,
            'billing_cycle' => $subscription->billing_cycle,
            'start_date' => $subscription->start_date,
            'end_date' => $subscription->end_date,
            'status' => $subscription->status,
            'website' => $subscription->website,
            'category' => $subscription->category,
            'next_payment_date' => $upcomingPayment ? $upcomingPayment->expected_date : null,
            'payments' => $payments,
            'total_paid' => $totalPaid,
            'created_at' => $subscription->created_at,
            'updated_at' => $subscription->updated_at,
        ];
    }
}
