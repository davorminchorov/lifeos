<?php

namespace App\Subscriptions\UI\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controller;

class DashboardController extends Controller
{
    /**
     * Get upcoming reminders for the dashboard widget.
     */
    public function upcomingReminders(): JsonResponse
    {
        $today = Carbon::today();
        $nextWeek = $today->copy()->addDays(7);

        $reminders = DB::table('subscription_reminders')
            ->where('reminder_date', '>=', $today->format('Y-m-d'))
            ->where('reminder_date', '<=', $nextWeek->format('Y-m-d'))
            ->where('sent', false)
            ->orderBy('reminder_date')
            ->limit(5)
            ->get()
            ->map(function ($reminder) use ($today) {
                $reminderDate = Carbon::parse($reminder->reminder_date);
                $daysUntil = $today->diffInDays($reminderDate, false);

                return [
                    'subscription_id' => $reminder->subscription_id,
                    'subscription_name' => $reminder->subscription_name,
                    'reminder_date' => $reminder->reminder_date,
                    'payment_date' => $reminder->payment_date,
                    'amount' => (float) $reminder->amount,
                    'currency' => $reminder->currency,
                    'days_until' => $daysUntil,
                    'is_today' => $daysUntil === 0,
                ];
            });

        return response()->json([
            'reminders' => $reminders,
            'total_count' => DB::table('subscription_reminders')
                ->where('reminder_date', '>=', $today->format('Y-m-d'))
                ->where('sent', false)
                ->count(),
        ]);
    }

    /**
     * Get a summary of subscription statuses for the dashboard.
     */
    public function subscriptionSummary(): JsonResponse
    {
        $summary = [
            'active' => DB::table('subscriptions')->where('status', 'active')->count(),
            'cancelled' => DB::table('subscriptions')->where('status', 'cancelled')->count(),
            'paused' => DB::table('subscriptions')->where('status', 'paused')->count(),
        ];

        // Calculate total monthly cost
        $monthlyCost = DB::table('subscriptions')
            ->where('status', 'active')
            ->selectRaw('
                SUM(CASE
                    WHEN billing_cycle = "monthly" THEN amount
                    WHEN billing_cycle = "annually" THEN amount / 12
                    WHEN billing_cycle = "quarterly" THEN amount / 3
                    WHEN billing_cycle = "semiannually" THEN amount / 6
                    WHEN billing_cycle = "biweekly" THEN amount * 2.17
                    WHEN billing_cycle = "weekly" THEN amount * 4.34
                    WHEN billing_cycle = "bimonthly" THEN amount / 2
                    WHEN billing_cycle = "daily" THEN amount * 30
                    ELSE amount
                END) as total
            ')
            ->first()
            ->total;

        $summary['monthly_cost'] = round((float) $monthlyCost, 2);

        return response()->json($summary);
    }
}
