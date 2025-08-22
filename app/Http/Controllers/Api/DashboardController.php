<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Investment;
use App\Models\Subscription;
use App\Models\UtilityBill;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function chartData(): JsonResponse
    {
        $user = Auth::user();

        // Generate chart data without passing PHP variables to JS
        $chartData = [
            'spendingTrends' => $this->getSpendingTrendsData($user),
            'categoryBreakdown' => $this->getCategoryBreakdownData($user),
            'portfolioPerformance' => $this->getPortfolioPerformanceData($user),
            'monthlyComparison' => $this->getMonthlyComparisonData($user),
        ];

        return response()->json($chartData);
    }

    private function getSpendingTrendsData($user): array
    {
        $monthlySpending = [];
        $currentDate = Carbon::now();

        // Get last 6 months of spending data
        for ($i = 5; $i >= 0; $i--) {
            $month = $currentDate->copy()->subMonths($i);
            $monthName = $month->format('M Y');

            $totalSpending = Expense::where('user_id', $user->id)
                ->whereYear('expense_date', $month->year)
                ->whereMonth('expense_date', $month->month)
                ->sum('amount');

            $monthlySpending[] = [
                'month' => $monthName,
                'amount' => (float) $totalSpending,
            ];
        }

        return [
            'labels' => array_column($monthlySpending, 'month'),
            'spending' => array_column($monthlySpending, 'amount'),
            'budget' => array_fill(0, count($monthlySpending), 50000), // Default budget
        ];
    }

    private function getCategoryBreakdownData($user): array
    {
        $subscriptionCost = Subscription::where('user_id', $user->id)
            ->where('is_active', true)
            ->sum('price');

        $pendingBills = UtilityBill::where('user_id', $user->id)
            ->where('is_paid', false)
            ->where('due_date', '>=', Carbon::now())
            ->sum('bill_amount');

        // Mock data for other categories
        return [
            'labels' => ['Subscriptions', 'Utilities', 'Food', 'Transport', 'Entertainment', 'Other'],
            'values' => [
                (float) $subscriptionCost,
                (float) $pendingBills,
                15000, 8000, 5000, 7000,
            ],
        ];
    }

    private function getPortfolioPerformanceData($user): array
    {
        $portfolioValue = Investment::where('user_id', $user->id)->sum('current_value');
        $totalReturn = Investment::where('user_id', $user->id)
            ->selectRaw('SUM(current_value - initial_investment) as total_return')
            ->first()
            ->total_return ?? 0;

        // Generate historical performance data
        return [
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            'values' => [
                $portfolioValue * 0.8,
                $portfolioValue * 0.9,
                $portfolioValue * 0.85,
                $portfolioValue * 0.95,
                $portfolioValue * 1.1,
                (float) $portfolioValue,
            ],
            'returns' => [
                $totalReturn * 0.3,
                $totalReturn * 0.5,
                $totalReturn * 0.4,
                $totalReturn * 0.7,
                $totalReturn * 0.9,
                (float) $totalReturn,
            ],
        ];
    }

    private function getMonthlyComparisonData($user): array
    {
        $subscriptionCost = Subscription::where('user_id', $user->id)
            ->where('is_active', true)
            ->sum('price');

        $pendingBills = UtilityBill::where('user_id', $user->id)
            ->where('is_paid', false)
            ->where('due_date', '>=', Carbon::now())
            ->sum('bill_amount');

        return [
            'categories' => ['Subscriptions', 'Utilities', 'Food', 'Transport', 'Entertainment'],
            'current' => [
                $subscriptionCost / 1000,
                $pendingBills / 1000,
                15, 8, 5,
            ],
            'previous' => [
                ($subscriptionCost / 1000) * 0.9,
                ($pendingBills / 1000) * 1.1,
                12, 10, 7,
            ],
        ];
    }
}
