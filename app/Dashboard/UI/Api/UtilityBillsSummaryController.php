<?php

namespace App\Dashboard\UI\Api;

use App\Http\Controllers\Controller;
use App\UtilityBills\Queries\GetAllBills;
use App\UtilityBills\Queries\GetUpcomingBills;
use App\UtilityBills\Queries\GetOverdueBills;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class UtilityBillsSummaryController extends Controller
{
    public function __invoke(
        GetAllBills $getAllBills,
        GetUpcomingBills $getUpcomingBills,
        GetOverdueBills $getOverdueBills
    ): JsonResponse {
        // Get all bills
        $allBills = $getAllBills();

        // Get overdue bills (bills where the due date has passed and not paid)
        $overdueBills = $getOverdueBills();

        // Get bills due soon (within the next 7 days)
        $upcomingBills = $getUpcomingBills(7);

        // Due soon bills (bills with status 'due' and due within 7 days)
        $dueSoonCount = $upcomingBills->where('status', 'due')->count();

        // Calculate monthly total (sum of all bills due this month)
        $today = Carbon::today();
        $monthStart = Carbon::today()->startOfMonth();
        $monthEnd = Carbon::today()->endOfMonth();

        $monthlyTotal = $allBills
            ->where('status', '!=', 'paid')
            ->filter(function ($bill) use ($monthStart, $monthEnd) {
                $dueDate = Carbon::parse($bill->due_date);
                return $dueDate->between($monthStart, $monthEnd);
            })
            ->reduce(function ($carry, $bill) {
                return $carry + ($bill->amount ?? 0);
            }, 0);

        // Use a default currency or get it from the first bill
        $defaultCurrency = 'USD';
        if ($allBills->isNotEmpty()) {
            $defaultCurrency = $allBills->first()->currency;
        }

        return response()->json([
            'total_count' => $allBills->count(),
            'monthly_total' => round($monthlyTotal, 2),
            'overdue_count' => $overdueBills->count(),
            'due_soon_count' => $dueSoonCount,
            'currency' => $defaultCurrency,
            'upcoming_bills' => $upcomingBills->map(function ($bill) {
                return [
                    'id' => $bill->id,
                    'name' => $bill->name,
                    'amount' => $bill->amount,
                    'currency' => $bill->currency,
                    'due_date' => $bill->due_date,
                    'status' => $bill->status,
                    'provider' => $bill->provider,
                ];
            }),
        ]);
    }
}
