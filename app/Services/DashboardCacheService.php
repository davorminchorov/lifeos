<?php

namespace App\Services;

use App\Models\Budget;
use App\Models\Contract;
use App\Models\Expense;
use App\Models\Investment;
use App\Models\Subscription;
use App\Models\UtilityBill;
use App\Models\Warranty;

class DashboardCacheService
{
    protected TenantCacheService $cache;

    protected CurrencyService $currencyService;

    public function __construct(TenantCacheService $cache, CurrencyService $currencyService)
    {
        $this->cache = $cache;
        $this->currencyService = $currencyService;
    }

    /**
     * Get cached dashboard stats (the most expensive aggregation).
     */
    public function getStats(): array
    {
        return $this->cache->tracked('dashboard', 'stats', function () {
            return $this->computeStats();
        });
    }

    /**
     * Get cached dashboard alerts.
     */
    public function getAlerts(): array
    {
        return $this->cache->tracked('dashboard', 'alerts', function () {
            return $this->computeAlerts();
        }, 300); // 5 min — alerts should be fresher
    }

    /**
     * Get cached engagement insights.
     */
    public function getInsights(): array
    {
        return $this->cache->tracked('dashboard', 'insights', function () {
            return $this->computeInsights();
        }, 1800); // 30 min — historical data changes slowly
    }

    /**
     * Get cached chart data for a given period.
     */
    public function getChartData(string $period): array
    {
        return $this->cache->tracked('dashboard', "charts:{$period}", function () use ($period) {
            return $this->computeChartData($period);
        }, 1800);
    }

    /**
     * Get cached recent expenses.
     */
    public function getRecentExpenses()
    {
        return $this->cache->tracked('dashboard', 'recent_expenses', function () {
            return Expense::where('user_id', auth()->id())
                ->orderBy('expense_date', 'desc')
                ->limit(5)
                ->get();
        }, 300);
    }

    /**
     * Get cached upcoming bills.
     */
    public function getUpcomingBills()
    {
        return $this->cache->tracked('dashboard', 'upcoming_bills', function () {
            return UtilityBill::where('user_id', auth()->id())
                ->where('payment_status', 'pending')
                ->orderBy('due_date', 'asc')
                ->limit(5)
                ->get();
        }, 300);
    }

    // ---------------------------------------------------------------
    // Private computation methods (extracted from DashboardController)
    // ---------------------------------------------------------------

    private function computeStats(): array
    {
        $userId = auth()->id();
        $defaultCurrency = config('currency.default', 'MKD');

        // Subscription stats
        $activeSubscriptions = Subscription::where('user_id', $userId)->active()->count();
        $subscriptions = Subscription::where('user_id', $userId)->active()->get();
        $monthlySubscriptionCost = $subscriptions->sum(fn ($sub) => $this->currencyService->convertToDefault(
            $sub->cost,
            $sub->currency ?? $defaultCurrency
        ));

        // Contract stats
        $activeContracts = Contract::where('user_id', $userId)->active()->count();
        $contractsExpiringSoon = Contract::where('user_id', $userId)->expiringSoon(30)->count();
        $totalContractValue = Contract::where('user_id', $userId)
            ->active()
            ->whereNotNull('contract_value')
            ->get()
            ->sum(fn ($c) => $this->currencyService->convertToDefault(
                $c->contract_value,
                $c->currency ?? $defaultCurrency
            ));

        // Investment stats
        $activeInvestments = Investment::where('user_id', $userId)->active()->get();
        $totalInvestments = $activeInvestments->count();
        $portfolioValue = $activeInvestments->sum('current_value');
        $totalReturn = $activeInvestments->sum('realized_gain_loss');

        // Utility bills
        $pendingBills = UtilityBill::where('user_id', $userId)->pending()->count();
        $totalPendingBills = UtilityBill::where('user_id', $userId)
            ->pending()
            ->get()
            ->sum(fn ($bill) => $this->currencyService->convertToDefault(
                $bill->bill_amount,
                $bill->currency ?? $defaultCurrency
            ));

        // Current month expenses
        $totalExpenses = Expense::where('user_id', $userId)->currentMonth()->count();
        $totalExpensesAmount = Expense::where('user_id', $userId)
            ->currentMonth()
            ->get()
            ->sum(fn ($exp) => $this->currencyService->convertToDefault(
                $exp->amount,
                $exp->currency ?? $defaultCurrency
            ));

        // Warranties
        $totalWarranties = Warranty::where('user_id', $userId)->active()->count();

        return [
            'active_subscriptions' => $activeSubscriptions,
            'monthly_subscription_cost' => $monthlySubscriptionCost,
            'monthly_subscription_cost_formatted' => $this->currencyService->format($monthlySubscriptionCost),
            'active_contracts' => $activeContracts,
            'contracts_expiring_soon' => $contractsExpiringSoon,
            'total_contract_value' => $totalContractValue,
            'total_contract_value_formatted' => $this->currencyService->format($totalContractValue),
            'total_investments' => $totalInvestments,
            'portfolio_value' => $portfolioValue,
            'portfolio_value_formatted' => $this->currencyService->format($portfolioValue),
            'total_return' => $totalReturn,
            'total_return_formatted' => $this->currencyService->format($totalReturn),
            'total_warranties' => $totalWarranties,
            'total_expenses' => $totalExpenses,
            'total_expenses_amount' => $totalExpensesAmount,
            'total_expenses_formatted' => $this->currencyService->format($totalExpensesAmount),
            'pending_bills' => $pendingBills,
            'pending_bills_amount' => $totalPendingBills,
            'pending_bills_formatted' => $this->currencyService->format($totalPendingBills),
        ];
    }

    private function computeAlerts(): array
    {
        $userId = auth()->id();
        $alerts = [];

        // Subscription renewals due soon
        foreach (Subscription::where('user_id', $userId)->dueSoon(7)->get() as $subscription) {
            $alerts[] = [
                'type' => 'warning',
                'title' => 'Subscription Renewal Due',
                'message' => "{$subscription->service_name} renews on {$subscription->next_billing_date->format('M j, Y')}",
                'action_url' => route('subscriptions.show', $subscription),
                'action_text' => 'View',
            ];
        }

        // Contracts expiring soon
        foreach (Contract::where('user_id', $userId)->expiringSoon(30)->get() as $contract) {
            $alerts[] = [
                'type' => 'warning',
                'title' => 'Contract Expiring Soon',
                'message' => "{$contract->title} expires on {$contract->end_date->format('M j, Y')}",
                'action_url' => route('contracts.show', $contract),
                'action_text' => 'Review',
            ];
        }

        // Warranties expiring soon
        foreach (Warranty::where('user_id', $userId)->expiringSoon(30)->get() as $warranty) {
            $alerts[] = [
                'type' => 'info',
                'title' => 'Warranty Expiring Soon',
                'message' => "{$warranty->product_name} warranty expires on {$warranty->warranty_expiration_date->format('M j, Y')}",
                'action_url' => route('warranties.show', $warranty),
                'action_text' => 'View',
            ];
        }

        // Overdue bills
        foreach (UtilityBill::where('user_id', $userId)->overdue()->get() as $bill) {
            $formattedAmount = $this->currencyService->format(
                $this->currencyService->convertToDefault($bill->bill_amount, $bill->currency ?? config('currency.default', 'MKD'))
            );
            $alerts[] = [
                'type' => 'error',
                'title' => 'Overdue Bill',
                'message' => "{$bill->service_provider} bill ({$formattedAmount}) was due on {$bill->due_date->format('M j, Y')}",
                'action_url' => route('utility-bills.show', $bill),
                'action_text' => 'Pay Now',
            ];
        }

        // Bills due soon
        foreach (UtilityBill::where('user_id', $userId)->dueSoon(7)->get() as $bill) {
            $formattedAmount = $this->currencyService->format(
                $this->currencyService->convertToDefault($bill->bill_amount, $bill->currency ?? config('currency.default', 'MKD'))
            );
            $alerts[] = [
                'type' => 'info',
                'title' => 'Bill Due Soon',
                'message' => "{$bill->service_provider} bill ({$formattedAmount}) is due on {$bill->due_date->format('M j, Y')}",
                'action_url' => route('utility-bills.show', $bill),
                'action_text' => 'View',
            ];
        }

        return array_slice($alerts, 0, 10);
    }

    private function computeInsights(): array
    {
        $userId = auth()->id();
        $defaultCurrency = config('currency.default', 'MKD');

        $monthlySpending = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $totalAmount = Expense::where('user_id', $userId)
                ->whereYear('expense_date', $date->year)
                ->whereMonth('expense_date', $date->month)
                ->get()
                ->sum(fn ($exp) => $this->currencyService->convertToDefault(
                    $exp->amount,
                    $exp->currency ?? $defaultCurrency
                ));

            $monthlySpending[] = [
                'month' => $date->format('M Y'),
                'amount' => $totalAmount,
                'formatted' => $this->currencyService->format($totalAmount),
            ];
        }

        $suggestions = $this->getFeatureDiscoverySuggestions($userId);

        return [
            'monthly_spending' => $monthlySpending,
            'suggestions' => $suggestions,
        ];
    }

    private function getFeatureDiscoverySuggestions(int $userId): array
    {
        $suggestions = [];

        if (UtilityBill::where('user_id', $userId)->count() > 0 &&
            UtilityBill::where('user_id', $userId)->whereNull('budget_alert_threshold')->count() > 0) {
            $suggestions[] = [
                'title' => 'Set Budget Alerts',
                'description' => 'Get notified when your utility bills exceed your budget',
                'action_url' => route('utility-bills.index'),
                'icon' => 'bell',
            ];
        }

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

    private function computeChartData(string $period): array
    {
        $endDate = now();
        $startDate = match ($period) {
            '3months' => $endDate->copy()->subMonths(3),
            '1year' => $endDate->copy()->subYear(),
            '2years' => $endDate->copy()->subYears(2),
            default => $endDate->copy()->subMonths(6),
        };

        return [
            'spendingTrends' => $this->getSpendingTrendsData($startDate, $endDate),
            'categoryBreakdown' => $this->getCategoryBreakdownData($startDate, $endDate),
            'portfolioPerformance' => $this->getPortfolioPerformanceData($startDate, $endDate),
            'monthlyComparison' => $this->getMonthlyComparisonData(),
        ];
    }

    private function getSpendingTrendsData($startDate, $endDate): array
    {
        $driver = config('database.default');
        $connection = config("database.connections.{$driver}.driver");

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
            $labels[] = $current->format('M Y');

            $monthExpense = $expenses->where('year', (string) $current->year)
                ->where('month', str_pad($current->month, 2, '0', STR_PAD_LEFT))
                ->first();

            $spending[] = $monthExpense
                ? $this->currencyService->convertToDefault((float) $monthExpense->total, config('currency.default', 'MKD'))
                : 0;

            $monthStart = $current->copy()->startOfMonth();
            $monthEnd = $current->copy()->endOfMonth();

            $monthlyBudgets = Budget::where('user_id', auth()->id())
                ->active()
                ->where(function ($query) use ($monthStart, $monthEnd) {
                    $query->where('start_date', '<=', $monthEnd)
                        ->where('end_date', '>=', $monthStart);
                })
                ->get();

            $totalBudget = $monthlyBudgets->sum(fn ($b) => $this->currencyService->convertToDefault(
                $b->amount,
                $b->currency ?? config('currency.default', 'MKD')
            ));

            $budget[] = $totalBudget > 0 ? $totalBudget : 0;
            $current->addMonth();
        }

        return compact('labels', 'spending', 'budget');
    }

    private function getCategoryBreakdownData($startDate, $endDate): array
    {
        $userId = auth()->id();
        $defaultCurrency = config('currency.default', 'MKD');

        $subscriptionCost = Subscription::where('user_id', $userId)
            ->active()
            ->get()
            ->sum(fn ($sub) => $this->currencyService->convertToDefault(
                $sub->cost,
                $sub->currency ?: $defaultCurrency
            ));

        $utilityBills = UtilityBill::where('user_id', $userId)
            ->whereBetween('due_date', [$startDate, $endDate])
            ->get()
            ->sum(fn ($bill) => $this->currencyService->convertToDefault(
                $bill->bill_amount,
                $bill->currency ?: $defaultCurrency
            ));

        $expenses = Expense::where('user_id', $userId)
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->get();

        $labels = ['Subscriptions', 'Utilities'];
        $values = [$subscriptionCost, $utilityBills];

        foreach ($expenses as $expense) {
            $labels[] = ucfirst($expense->category ?? 'Other');
            $values[] = $this->currencyService->convertToDefault($expense->total, $defaultCurrency);
        }

        return compact('labels', 'values');
    }

    private function getPortfolioPerformanceData($startDate, $endDate): array
    {
        $userId = auth()->id();
        $labels = [];
        $values = [];
        $returns = [];

        $current = $startDate->copy();
        $monthCount = 0;

        while ($current->lte($endDate) && $monthCount < 12) {
            $labels[] = $current->format('M Y');

            $monthEnd = $current->copy()->endOfMonth();
            $investmentsAtTime = Investment::where('user_id', $userId)
                ->where('purchase_date', '<=', $monthEnd)
                ->get();

            $monthValue = $investmentsAtTime->sum('current_value');
            $monthCost = $investmentsAtTime->sum('initial_investment');

            $values[] = (float) $monthValue;
            $returns[] = (float) ($monthValue - $monthCost);

            $current->addMonth();
            $monthCount++;
        }

        return compact('labels', 'values', 'returns');
    }

    private function getMonthlyComparisonData(): array
    {
        $currentMonth = now();
        $previousMonth = now()->subMonth();

        return [
            'categories' => ['Subscriptions', 'Utilities', 'Food', 'Transport', 'Entertainment'],
            'current' => array_values($this->getMonthlySpendingByCategory($currentMonth)),
            'previous' => array_values($this->getMonthlySpendingByCategory($previousMonth)),
        ];
    }

    private function getMonthlySpendingByCategory($month): array
    {
        $userId = auth()->id();
        $defaultCurrency = config('currency.default', 'MKD');

        $subscriptionCost = Subscription::where('user_id', $userId)
            ->active()
            ->get()
            ->sum(fn ($sub) => $this->currencyService->convertToDefault(
                $sub->cost,
                $sub->currency ?? $defaultCurrency
            ));

        $utilityBills = UtilityBill::where('user_id', $userId)
            ->whereYear('due_date', $month->year)
            ->whereMonth('due_date', $month->month)
            ->get()
            ->sum(fn ($bill) => $this->currencyService->convertToDefault(
                $bill->bill_amount,
                $bill->currency ?? $defaultCurrency
            ));

        $categoryExpenses = Expense::where('user_id', $userId)
            ->whereYear('expense_date', $month->year)
            ->whereMonth('expense_date', $month->month)
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->get()
            ->mapWithKeys(fn ($expense) => [
                strtolower($expense->category ?? 'other') =>
                    $this->currencyService->convertToDefault($expense->total, $defaultCurrency),
            ]);

        return [
            $subscriptionCost / 1000,
            $utilityBills / 1000,
            $categoryExpenses->get('food', 0) / 1000,
            $categoryExpenses->get('transport', 0) / 1000,
            $categoryExpenses->get('entertainment', 0) / 1000,
        ];
    }
}
