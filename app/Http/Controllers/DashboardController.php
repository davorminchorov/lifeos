<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Contract;
use App\Models\Expense;
use App\Models\Investment;
use App\Models\Subscription;
use App\Models\UtilityBill;
use App\Models\Warranty;
use App\Services\CurrencyService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected CurrencyService $currencyService;

    public function __construct(CurrencyService $currencyService)
    {
        $this->currencyService = $currencyService;
    }

    /**
     * Display the dashboard with aggregated data from all modules.
     */
    public function index()
    {
        // Aggregate statistics
        $stats = $this->getStats();

        // Get alerts and notifications
        $alerts = $this->getAlerts();

        // Get analytics insights
        $insights = $this->getUserEngagementInsights();

        // Get recent activity
        $recent_expenses = Expense::where('user_id', auth()->id())
            ->orderBy('expense_date', 'desc')
            ->limit(5)
            ->get();

        $upcoming_bills = UtilityBill::where('user_id', auth()->id())
            ->where('payment_status', 'pending')
            ->orderBy('due_date', 'asc')
            ->limit(5)
            ->get();

        return view('dashboard', compact(
            'stats',
            'alerts',
            'insights',
            'recent_expenses',
            'upcoming_bills'
        ));
    }

    /**
     * Get chart data for Advanced Analytics Dashboard
     */
    public function getChartData(Request $request)
    {
        $period = $request->get('period', '6months');

        // Calculate date range based on period
        $endDate = now();
        $startDate = match ($period) {
            '3months' => $endDate->copy()->subMonths(3),
            '1year' => $endDate->copy()->subYear(),
            '2years' => $endDate->copy()->subYears(2),
            default => $endDate->copy()->subMonths(6), // 6months
        };

        return response()->json([
            'spendingTrends' => $this->getSpendingTrendsData($startDate, $endDate),
            'categoryBreakdown' => $this->getCategoryBreakdownData($startDate, $endDate),
            'portfolioPerformance' => $this->getPortfolioPerformanceData($startDate, $endDate),
            'monthlyComparison' => $this->getMonthlyComparisonData(),
        ]);
    }

    /**
     * Get spending trends data for charts
     */
    private function getSpendingTrendsData($startDate, $endDate)
    {
        $driver = config('database.default');
        $connection = config("database.connections.{$driver}.driver");

        // Use different SQL syntax based on database driver
        if ($connection === 'sqlite') {
            $yearMonth = "strftime('%Y', expense_date) as year, strftime('%m', expense_date) as month";
        } else {
            $yearMonth = 'YEAR(expense_date) as year, MONTH(expense_date) as month';
        }

        $expenses = Expense::where('user_id', auth()->id())
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->selectRaw("{$yearMonth}, SUM(amount) as total")
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        $labels = [];
        $spending = [];
        $budget = [];

        $current = $startDate->copy();
        while ($current->lte($endDate)) {
            $monthLabel = $current->format('M Y');
            $labels[] = $monthLabel;

            $monthExpense = $expenses->where('year', (string) $current->year)
                ->where('month', str_pad($current->month, 2, '0', STR_PAD_LEFT))
                ->first();

            $monthlyAmount = $monthExpense ? $this->currencyService->convertToDefault((float) $monthExpense->total, config('currency.default', 'MKD')) : 0;
            $spending[] = $monthlyAmount;

            // Calculate budget for this month from active budgets
            $monthStart = $current->copy()->startOfMonth();
            $monthEnd = $current->copy()->endOfMonth();

            $monthlyBudgets = Budget::where('user_id', auth()->id())
                ->active()
                ->where(function ($query) use ($monthStart, $monthEnd) {
                    $query->where(function ($q) use ($monthStart, $monthEnd) {
                        $q->where('start_date', '<=', $monthEnd)
                          ->where('end_date', '>=', $monthStart);
                    });
                })
                ->get();

            $totalBudget = 0;
            foreach ($monthlyBudgets as $budgetItem) {
                $currency = $budgetItem->currency ?? config('currency.default', 'MKD');
                $totalBudget += $this->currencyService->convertToDefault($budgetItem->amount, $currency);
            }

            // If no budget set, use 0 or a calculated average
            $budget[] = $totalBudget > 0 ? $totalBudget : 0;

            $current->addMonth();
        }

        return [
            'labels' => $labels,
            'spending' => $spending,
            'budget' => $budget,
        ];
    }

    /**
     * Get category breakdown data for charts
     */
    private function getCategoryBreakdownData($startDate, $endDate)
    {
        $userId = auth()->id();

        // Get subscription costs
        $subscriptionCost = Subscription::where('user_id', $userId)
            ->active()
            ->get()
            ->sum(function ($sub) {
                $currency = $sub->currency ?: config('currency.default', 'MKD');

                return $this->currencyService->convertToDefault($sub->cost, $currency);
            });

        // Get utility bills
        $utilityBills = UtilityBill::where('user_id', $userId)
            ->whereBetween('due_date', [$startDate, $endDate])
            ->get()
            ->sum(function ($bill) {
                $currency = $bill->currency ?: config('currency.default', 'MKD');

                return $this->currencyService->convertToDefault($bill->bill_amount, $currency);
            });

        // Get expenses by category (if you have categories)
        $expenses = Expense::where('user_id', $userId)
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->get();

        $labels = ['Subscriptions', 'Utilities'];
        $values = [$subscriptionCost, $utilityBills];

        // Add expense categories
        foreach ($expenses as $expense) {
            $labels[] = ucfirst($expense->category ?? 'Other');
            $values[] = $this->currencyService->convertToDefault($expense->total, config('currency.default', 'MKD'));
        }

        return [
            'labels' => $labels,
            'values' => $values,
        ];
    }

    /**
     * Get portfolio performance data for charts
     */
    private function getPortfolioPerformanceData($startDate, $endDate)
    {
        $userId = auth()->id();
        $labels = [];
        $values = [];
        $returns = [];

        // Get current investment values
        $currentInvestments = Investment::where('user_id', $userId)->active()->get();
        $currentTotalValue = $currentInvestments->sum('current_value');
        $currentTotalCost = $currentInvestments->sum('initial_investment');

        $current = $startDate->copy();
        $monthCount = 0;

        while ($current->lte($endDate) && $monthCount < 12) {
            $labels[] = $current->format('M Y');

            // Get investments that existed at this point in time
            $monthEnd = $current->copy()->endOfMonth();
            $investmentsAtTime = Investment::where('user_id', $userId)
                ->where('purchase_date', '<=', $monthEnd)
                ->get();

            $monthValue = $investmentsAtTime->sum('current_value');
            $monthCost = $investmentsAtTime->sum('initial_investment');
            $monthReturn = $monthValue - $monthCost;

            $values[] = (float) $monthValue;
            $returns[] = (float) $monthReturn;

            $current->addMonth();
            $monthCount++;
        }

        return [
            'labels' => $labels,
            'values' => $values,
            'returns' => $returns,
        ];
    }

    /**
     * Get monthly comparison data for radar chart
     */
    private function getMonthlyComparisonData()
    {
        $currentMonth = now();
        $previousMonth = now()->subMonth();

        // Get current month data
        $currentData = $this->getMonthlySpendingByCategory($currentMonth);
        $previousData = $this->getMonthlySpendingByCategory($previousMonth);

        return [
            'categories' => ['Subscriptions', 'Utilities', 'Food', 'Transport', 'Entertainment'],
            'current' => array_values($currentData),
            'previous' => array_values($previousData),
        ];
    }

    /**
     * Get spending by category for a specific month
     */
    private function getMonthlySpendingByCategory($month)
    {
        $userId = auth()->id();

        $subscriptionCost = Subscription::where('user_id', $userId)
            ->active()
            ->get()
            ->sum(function ($sub) {
                return $this->currencyService->convertToDefault($sub->cost, $sub->currency ?? config('currency.default', 'MKD'));
            });

        $utilityBills = UtilityBill::where('user_id', $userId)
            ->whereYear('due_date', $month->year)
            ->whereMonth('due_date', $month->month)
            ->get()
            ->sum(function ($bill) {
                return $this->currencyService->convertToDefault($bill->bill_amount, $bill->currency ?? config('currency.default', 'MKD'));
            });

        // Get actual expense data by category for the month
        $categoryExpenses = Expense::where('user_id', $userId)
            ->whereYear('expense_date', $month->year)
            ->whereMonth('expense_date', $month->month)
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->get()
            ->mapWithKeys(function ($expense) {
                $currency = config('currency.default', 'MKD');
                return [
                    strtolower($expense->category ?? 'other') =>
                        $this->currencyService->convertToDefault($expense->total, $currency)
                ];
            });

        $foodExpenses = $categoryExpenses->get('food', 0);
        $transportExpenses = $categoryExpenses->get('transport', 0);
        $entertainmentExpenses = $categoryExpenses->get('entertainment', 0);

        return [
            $subscriptionCost / 1000, // Convert to thousands for better chart readability
            $utilityBills / 1000,
            $foodExpenses / 1000,
            $transportExpenses / 1000,
            $entertainmentExpenses / 1000,
        ];
    }

    /**
     * Aggregate statistics from all modules.
     */
    private function getStats(): array
    {
        $userId = auth()->id();

        // Subscription stats with currency conversion to MKD
        $activeSubscriptions = Subscription::where('user_id', $userId)->active()->count();
        $subscriptions = Subscription::where('user_id', $userId)->active()->get();
        $monthlySubscriptionCostMKD = 0;

        foreach ($subscriptions as $subscription) {
            $currency = $subscription->currency ?? config('currency.default', 'MKD');
            $costInMKD = $this->currencyService->convertToDefault($subscription->cost, $currency);
            $monthlySubscriptionCostMKD += $costInMKD;
        }

        // Contract stats with currency conversion to MKD
        $activeContracts = Contract::where('user_id', $userId)->active()->count();
        $contractsExpiringSoon = Contract::where('user_id', $userId)->expiringSoon(30)->count();
        $contracts = Contract::where('user_id', $userId)->active()->whereNotNull('contract_value')->get();
        $totalContractValueMKD = 0;

        foreach ($contracts as $contract) {
            $currency = $contract->currency ?? config('currency.default', 'MKD');
            $valueInMKD = $this->currencyService->convertToDefault($contract->contract_value, $currency);
            $totalContractValueMKD += $valueInMKD;
        }

        // Investment stats - assuming these are already in base currency
        $activeInvestments = Investment::where('user_id', $userId)->active()->get();
        $totalInvestments = $activeInvestments->count();
        $portfolioValue = $activeInvestments->sum('current_value');
        $totalReturn = $activeInvestments->sum('realized_gain_loss');

        // Utility bills with currency conversion to MKD
        $pendingBills = UtilityBill::where('user_id', $userId)->pending()->count();
        $bills = UtilityBill::where('user_id', $userId)->pending()->get();
        $totalPendingBillsMKD = 0;

        foreach ($bills as $bill) {
            $currency = $bill->currency ?? config('currency.default', 'MKD');
            $amountInMKD = $this->currencyService->convertToDefault($bill->bill_amount, $currency);
            $totalPendingBillsMKD += $amountInMKD;
        }

        // Current month expenses with currency conversion to MKD
        $totalExpenses = Expense::where('user_id', $userId)->currentMonth()->count();
        $expenses = Expense::where('user_id', $userId)->currentMonth()->get();
        $totalExpensesMKD = 0;

        foreach ($expenses as $expense) {
            $currency = $expense->currency ?? config('currency.default', 'MKD');
            $amountInMKD = $this->currencyService->convertToDefault($expense->amount, $currency);
            $totalExpensesMKD += $amountInMKD;
        }

        // Other stats
        $totalWarranties = Warranty::where('user_id', $userId)->active()->count();

        return [
            'active_subscriptions' => $activeSubscriptions,
            'monthly_subscription_cost' => $monthlySubscriptionCostMKD,
            'monthly_subscription_cost_formatted' => $this->currencyService->format($monthlySubscriptionCostMKD),
            'active_contracts' => $activeContracts,
            'contracts_expiring_soon' => $contractsExpiringSoon,
            'total_contract_value' => $totalContractValueMKD,
            'total_contract_value_formatted' => $this->currencyService->format($totalContractValueMKD),
            'total_investments' => $totalInvestments,
            'portfolio_value' => $portfolioValue,
            'portfolio_value_formatted' => $this->currencyService->format($portfolioValue),
            'total_return' => $totalReturn,
            'total_return_formatted' => $this->currencyService->format($totalReturn),
            'total_warranties' => $totalWarranties,
            'total_expenses' => $totalExpenses,
            'total_expenses_amount' => $totalExpensesMKD,
            'total_expenses_formatted' => $this->currencyService->format($totalExpensesMKD),
            'pending_bills' => $pendingBills,
            'pending_bills_amount' => $totalPendingBillsMKD,
            'pending_bills_formatted' => $this->currencyService->format($totalPendingBillsMKD),
        ];
    }

    /**
     * Get alerts and notifications from all modules.
     */
    private function getAlerts(): array
    {
        $userId = auth()->id();
        $alerts = [];

        // Subscription renewals due soon
        $subscriptionsDueSoon = Subscription::where('user_id', $userId)->dueSoon(7)->get();
        foreach ($subscriptionsDueSoon as $subscription) {
            $alerts[] = [
                'type' => 'warning',
                'title' => 'Subscription Renewal Due',
                'message' => "{$subscription->service_name} renews on {$subscription->next_billing_date->format('M j, Y')}",
                'action_url' => route('subscriptions.show', $subscription),
                'action_text' => 'View',
            ];
        }

        // Contracts expiring soon
        $contractsExpiringSoon = Contract::where('user_id', $userId)->expiringSoon(30)->get();
        foreach ($contractsExpiringSoon as $contract) {
            $alerts[] = [
                'type' => 'warning',
                'title' => 'Contract Expiring Soon',
                'message' => "{$contract->title} expires on {$contract->end_date->format('M j, Y')}",
                'action_url' => route('contracts.show', $contract),
                'action_text' => 'Review',
            ];
        }

        // Warranties expiring soon
        $warrantiesExpiringSoon = Warranty::where('user_id', $userId)->expiringSoon(30)->get();
        foreach ($warrantiesExpiringSoon as $warranty) {
            $alerts[] = [
                'type' => 'info',
                'title' => 'Warranty Expiring Soon',
                'message' => "{$warranty->product_name} warranty expires on {$warranty->warranty_expiration_date->format('M j, Y')}",
                'action_url' => route('warranties.show', $warranty),
                'action_text' => 'View',
            ];
        }

        // Overdue bills
        $overdueBills = UtilityBill::where('user_id', $userId)->overdue()->get();
        foreach ($overdueBills as $bill) {
            $currency = $bill->currency ?? config('currency.default', 'MKD');
            $amountInMKD = $this->currencyService->convertToDefault($bill->bill_amount, $currency);
            $formattedAmount = $this->currencyService->format($amountInMKD);
            $alerts[] = [
                'type' => 'error',
                'title' => 'Overdue Bill',
                'message' => "{$bill->service_provider} bill ({$formattedAmount}) was due on {$bill->due_date->format('M j, Y')}",
                'action_url' => route('utility-bills.show', $bill),
                'action_text' => 'Pay Now',
            ];
        }

        // Bills due soon
        $billsDueSoon = UtilityBill::where('user_id', $userId)->dueSoon(7)->get();
        foreach ($billsDueSoon as $bill) {
            $currency = $bill->currency ?? config('currency.default', 'MKD');
            $amountInMKD = $this->currencyService->convertToDefault($bill->bill_amount, $currency);
            $formattedAmount = $this->currencyService->format($amountInMKD);
            $alerts[] = [
                'type' => 'info',
                'title' => 'Bill Due Soon',
                'message' => "{$bill->service_provider} bill ({$formattedAmount}) is due on {$bill->due_date->format('M j, Y')}",
                'action_url' => route('utility-bills.show', $bill),
                'action_text' => 'View',
            ];
        }

        // Limit to recent 10 alerts
        return array_slice($alerts, 0, 10);
    }

    /**
     * Get count of items requiring attention.
     */
    private function getItemsRequiringAttentionCount($userId)
    {
        return UtilityBill::where('user_id', $userId)->where('payment_status', 'overdue')->count() +
               Subscription::where('user_id', $userId)->dueSoon(3)->count() +
               Contract::where('user_id', $userId)->expiringSoon(30)->count();
    }

    /**
     * Calculate potential savings opportunities.
     */
    private function getSavingsOpportunities($userId)
    {
        // This is a simplified calculation - in a real app you'd have more sophisticated logic
        $subscriptionSavings = Subscription::where('user_id', $userId)
            ->where('status', 'active')
            ->where('cancellation_difficulty', '<=', 2)
            ->get()
            ->sum('monthly_cost') * 0.1; // Assume 10% potential savings on easy-to-cancel subscriptions

        $utilitySavings = UtilityBill::where('user_id', $userId)
            ->whereNotNull('budget_alert_threshold')
            ->whereRaw('bill_amount > budget_alert_threshold')
            ->get()
            ->sum(function ($bill) {
                return ($bill->bill_amount - $bill->budget_alert_threshold) * 0.15; // 15% potential savings on over-budget bills
            });

        return round($subscriptionSavings + $utilitySavings, 2);
    }

    /**
     * Get user engagement insights for analytics enhancement
     */
    private function getUserEngagementInsights(): array
    {
        $userId = auth()->id();

        // Calculate spending trends (last 6 months)
        $monthlySpending = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $expenses = Expense::where('user_id', $userId)
                ->whereYear('expense_date', $date->year)
                ->whereMonth('expense_date', $date->month)
                ->get();

            $totalMKD = 0;
            foreach ($expenses as $expense) {
                $currency = $expense->currency ?? config('currency.default', 'MKD');
                $totalMKD += $this->currencyService->convertToDefault($expense->amount, $currency);
            }

            $monthlySpending[] = [
                'month' => $date->format('M Y'),
                'amount' => $totalMKD,
                'formatted' => $this->currencyService->format($totalMKD),
            ];
        }

        // Feature discovery suggestions
        $suggestions = $this->getFeatureDiscoverySuggestions($userId);

        return [
            'monthly_spending' => $monthlySpending,
            'suggestions' => $suggestions,
        ];
    }

    /**
     * Get feature discovery suggestions based on user data
     */
    private function getFeatureDiscoverySuggestions($userId): array
    {
        $suggestions = [];

        // Check if user has utility bills but no budget alerts
        if (UtilityBill::where('user_id', $userId)->count() > 0 &&
            UtilityBill::where('user_id', $userId)->whereNull('budget_alert_threshold')->count() > 0) {
            $suggestions[] = [
                'title' => 'Set Budget Alerts',
                'description' => 'Get notified when your utility bills exceed your budget',
                'action_url' => route('utility-bills.index'),
                'icon' => 'bell',
            ];
        }

        // Check if user has expenses but no categories
        if (Expense::where('user_id', $userId)->count() > 0 &&
            Expense::where('user_id', $userId)->whereNull('category')->count() > 0) {
            $suggestions[] = [
                'title' => 'Categorize Expenses',
                'description' => 'Better track your spending by adding categories',
                'action_url' => route('expenses.index'),
                'icon' => 'tag',
            ];
        }

        return array_slice($suggestions, 0, 3);
    }
}
