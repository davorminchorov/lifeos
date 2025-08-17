<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\Expense;
use App\Models\Investment;
use App\Models\Subscription;
use App\Models\UtilityBill;
use App\Models\Warranty;

class DashboardController extends Controller
{
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
        // Subscription stats
        $activeSubscriptions = Subscription::active()->count();
        $monthlySubscriptionCost = Subscription::active()->sum('monthly_cost');

        // Contract stats
        $activeContracts = Contract::active()->count();
        $contractsExpiringSoon = Contract::expiringSoon(30)->count();

        // Investment stats
        $activeInvestments = Investment::active()->get();
        $portfolioValue = $activeInvestments->sum('current_market_value');
        $totalReturn = $activeInvestments->sum('total_return');

        // Other stats
        $totalWarranties = Warranty::active()->count();
        $totalExpenses = Expense::currentMonth()->count();
        $pendingBills = UtilityBill::pending()->count();

        return [
            'active_subscriptions' => $activeSubscriptions,
            'monthly_subscription_cost' => $monthlySubscriptionCost,
            'active_contracts' => $activeContracts,
            'contracts_expiring_soon' => $contractsExpiringSoon,
            'portfolio_value' => $portfolioValue,
            'total_return' => $totalReturn,
            'total_warranties' => $totalWarranties,
            'total_expenses' => $totalExpenses,
            'pending_bills' => $pendingBills,
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
            $alerts[] = [
                'type' => 'error',
                'title' => 'Overdue Bill',
                'message' => "{$bill->service_provider} bill was due on {$bill->due_date->format('M j, Y')}",
                'action_url' => route('utility-bills.show', $bill),
                'action_text' => 'Pay Now',
            ];
        }

        // Bills due soon
        $billsDueSoon = UtilityBill::dueSoon(7)->get();
        foreach ($billsDueSoon as $bill) {
            $alerts[] = [
                'type' => 'info',
                'title' => 'Bill Due Soon',
                'message' => "{$bill->service_provider} bill is due on {$bill->due_date->format('M j, Y')}",
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
