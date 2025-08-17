<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\Expense;
use App\Models\Investment;
use App\Models\Subscription;
use App\Models\UtilityBill;
use App\Models\Warranty;
use App\Services\CurrencyService;

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

        // Get recent activity
        $recent_expenses = Expense::with('user')
            ->orderBy('expense_date', 'desc')
            ->limit(5)
            ->get();

        $upcoming_bills = UtilityBill::with('user')
            ->where('payment_status', 'pending')
            ->orderBy('due_date', 'asc')
            ->limit(5)
            ->get();

        return view('dashboard', compact(
            'stats',
            'alerts',
            'recent_expenses',
            'upcoming_bills'
        ));
    }

    /**
     * Aggregate statistics from all modules.
     */
    private function getStats(): array
    {
        // Subscription stats with currency conversion to MKD
        $activeSubscriptions = Subscription::active()->count();
        $subscriptions = Subscription::active()->get();
        $monthlySubscriptionCostMKD = 0;

        foreach ($subscriptions as $subscription) {
            $currency = $subscription->currency ?? config('currency.default', 'MKD');
            $costInMKD = $this->currencyService->convertToDefault($subscription->cost, $currency);
            $monthlySubscriptionCostMKD += $costInMKD;
        }

        // Contract stats with currency conversion to MKD
        $activeContracts = Contract::active()->count();
        $contractsExpiringSoon = Contract::expiringSoon(30)->count();
        $contracts = Contract::active()->whereNotNull('contract_value')->get();
        $totalContractValueMKD = 0;

        foreach ($contracts as $contract) {
            $currency = $contract->currency ?? config('currency.default', 'MKD');
            $valueInMKD = $this->currencyService->convertToDefault($contract->contract_value, $currency);
            $totalContractValueMKD += $valueInMKD;
        }

        // Investment stats - assuming these are already in base currency
        $activeInvestments = Investment::active()->get();
        $portfolioValue = $activeInvestments->sum('current_market_value');
        $totalReturn = $activeInvestments->sum('total_return');

        // Utility bills with currency conversion to MKD
        $pendingBills = UtilityBill::pending()->count();
        $bills = UtilityBill::pending()->get();
        $totalPendingBillsMKD = 0;

        foreach ($bills as $bill) {
            $currency = $bill->currency ?? config('currency.default', 'MKD');
            $amountInMKD = $this->currencyService->convertToDefault($bill->bill_amount, $currency);
            $totalPendingBillsMKD += $amountInMKD;
        }

        // Current month expenses with currency conversion to MKD
        $totalExpenses = Expense::currentMonth()->count();
        $expenses = Expense::currentMonth()->get();
        $totalExpensesMKD = 0;

        foreach ($expenses as $expense) {
            $currency = $expense->currency ?? config('currency.default', 'MKD');
            $amountInMKD = $this->currencyService->convertToDefault($expense->amount, $currency);
            $totalExpensesMKD += $amountInMKD;
        }

        // Other stats
        $totalWarranties = Warranty::active()->count();

        return [
            'active_subscriptions' => $activeSubscriptions,
            'monthly_subscription_cost' => $monthlySubscriptionCostMKD,
            'monthly_subscription_cost_formatted' => $this->currencyService->format($monthlySubscriptionCostMKD),
            'active_contracts' => $activeContracts,
            'contracts_expiring_soon' => $contractsExpiringSoon,
            'total_contract_value' => $totalContractValueMKD,
            'total_contract_value_formatted' => $this->currencyService->format($totalContractValueMKD),
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
        $alerts = [];

        // Subscription renewals due soon
        $subscriptionsDueSoon = Subscription::dueSoon(7)->get();
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
        $contractsExpiringSoon = Contract::expiringSoon(30)->get();
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
        $warrantiesExpiringSoon = Warranty::expiringSoon(30)->get();
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
        $overdueBills = UtilityBill::overdue()->get();
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
        $billsDueSoon = UtilityBill::dueSoon(7)->get();
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
}
