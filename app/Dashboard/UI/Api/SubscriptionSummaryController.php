<?php

namespace App\Dashboard\UI\Api;

use App\Http\Controllers\Controller;
use App\Subscriptions\Queries\GetActiveSubscriptions;
use App\Subscriptions\Queries\GetUpcomingPayments;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class SubscriptionSummaryController extends Controller
{
    public function __invoke(
        GetActiveSubscriptions $getActiveSubscriptions,
        GetUpcomingPayments $getUpcomingPayments
    ): JsonResponse {
        // Get active subscriptions
        $activeSubscriptions = $getActiveSubscriptions();

        // Calculate monthly total
        $monthlyTotal = $activeSubscriptions->reduce(function ($carry, $subscription) {
            // Normalize all subscriptions to monthly cost
            $amount = $subscription->amount;

            switch ($subscription->billing_cycle) {
                case 'daily':
                    $amount *= 30; // Approximate days in a month
                    break;
                case 'weekly':
                    $amount *= 4.33; // Average weeks in a month
                    break;
                case 'biweekly':
                    $amount *= 2.17; // Biweekly in a month
                    break;
                case 'monthly':
                    // Already monthly
                    break;
                case 'bimonthly':
                    $amount /= 2; // Every two months
                    break;
                case 'quarterly':
                    $amount /= 3; // Every three months
                    break;
                case 'semiannually':
                    $amount /= 6; // Twice a year
                    break;
                case 'annually':
                    $amount /= 12; // Once a year
                    break;
            }

            return $carry + $amount;
        }, 0);

        // Get upcoming payments (next 30 days)
        $upcomingPayments = $getUpcomingPayments(30);

        // Use a default currency or get it from the first subscription
        $defaultCurrency = 'USD';
        if ($activeSubscriptions->isNotEmpty()) {
            $defaultCurrency = $activeSubscriptions->first()->currency;
        }

        return response()->json([
            'active_count' => $activeSubscriptions->count(),
            'monthly_total' => round($monthlyTotal, 2),
            'annual_total' => round($monthlyTotal * 12, 2),
            'currency' => $defaultCurrency,
            'upcoming_payments' => $upcomingPayments->map(function ($payment) {
                return [
                    'id' => $payment->id,
                    'name' => $payment->name,
                    'amount' => $payment->amount,
                    'currency' => $payment->currency,
                    'due_date' => $payment->next_payment_date,
                ];
            }),
        ]);
    }
}
