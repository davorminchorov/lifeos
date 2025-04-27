<?php

namespace App\Dashboard\UI\Api;

use App\Expenses\Queries\GetMonthlyExpenseSummary;
use App\Http\Controllers\Controller;
use App\Subscriptions\Queries\GetActiveSubscriptionsCount;
use App\Subscriptions\Queries\GetMonthlyCost;
use App\Subscriptions\Queries\GetTotalSubscriptionsCount;
use App\Subscriptions\Queries\GetUpcomingPaymentsCount;
use App\UtilityBills\Queries\GetPendingBillsCount;
use App\UtilityBills\Queries\GetUpcomingRemindersCount;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function summary(): JsonResponse
    {
        $totalSubscriptions = app(GetTotalSubscriptionsCount::class)->handle();
        $activeSubscriptions = app(GetActiveSubscriptionsCount::class)->handle();
        $upcomingPayments = app(GetUpcomingPaymentsCount::class)->handle();
        $monthlyCost = app(GetMonthlyCost::class)->handle();
        $pendingBills = app(GetPendingBillsCount::class)->handle();
        $upcomingReminders = app(GetUpcomingRemindersCount::class)->handle();

        return response()->json([
            'totalSubscriptions' => $totalSubscriptions,
            'activeSubscriptions' => $activeSubscriptions,
            'upcomingPayments' => $upcomingPayments,
            'monthlyCost' => $monthlyCost,
            'pendingBills' => $pendingBills,
            'upcomingReminders' => $upcomingReminders,
        ]);
    }
}
