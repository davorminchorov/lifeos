<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Models\Contract;
use App\Models\Warranty;
use App\Models\Investment;
use App\Models\Expense;
use App\Models\UtilityBill;
use Illuminate\Http\Request;

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
                'action_text' => 'View'
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
                'action_text' => 'Review'
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
                'action_text' => 'View'
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
                'action_text' => 'Pay Now'
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
                'action_text' => 'View'
            ];
        }

        // Limit to recent 10 alerts
        return array_slice($alerts, 0, 10);
    }

    /**
     * Get dashboard overview data for API.
     */
    public function apiOverview(Request $request)
    {
        $userId = auth()->id();

        $overview = [
            // Financial Overview
            'financial_summary' => [
                'monthly_subscriptions' => Subscription::where('user_id', $userId)
                    ->where('status', 'active')
                    ->get()
                    ->sum('monthly_cost'),
                'yearly_subscriptions' => Subscription::where('user_id', $userId)
                    ->where('status', 'active')
                    ->get()
                    ->sum('yearly_cost'),
                'current_month_expenses' => Expense::where('user_id', $userId)
                    ->whereMonth('expense_date', now()->month)
                    ->whereYear('expense_date', now()->year)
                    ->sum('amount'),
                'current_month_utility_bills' => UtilityBill::where('user_id', $userId)
                    ->whereMonth('bill_period_start', now()->month)
                    ->whereYear('bill_period_start', now()->year)
                    ->sum('bill_amount'),
                'investment_portfolio_value' => Investment::where('user_id', $userId)
                    ->where('status', 'active')
                    ->sum('current_market_value'),
            ],

            // Module Counts
            'module_counts' => [
                'active_subscriptions' => Subscription::where('user_id', $userId)
                    ->where('status', 'active')
                    ->count(),
                'active_contracts' => Contract::where('user_id', $userId)
                    ->where('status', 'active')
                    ->count(),
                'active_warranties' => Warranty::where('user_id', $userId)
                    ->where('current_status', 'active')
                    ->count(),
                'active_investments' => Investment::where('user_id', $userId)
                    ->where('status', 'active')
                    ->count(),
                'this_month_expenses' => Expense::where('user_id', $userId)
                    ->whereMonth('expense_date', now()->month)
                    ->whereYear('expense_date', now()->year)
                    ->count(),
                'pending_utility_bills' => UtilityBill::where('user_id', $userId)
                    ->where('payment_status', 'pending')
                    ->count(),
            ],

            // Quick Stats
            'quick_stats' => [
                'total_monthly_recurring' => Subscription::where('user_id', $userId)
                    ->where('status', 'active')
                    ->get()
                    ->sum('monthly_cost') +
                    UtilityBill::where('user_id', $userId)
                        ->whereMonth('bill_period_start', now()->month)
                        ->sum('bill_amount'),
                'items_requiring_attention' => $this->getItemsRequiringAttentionCount($userId),
                'savings_opportunities' => $this->getSavingsOpportunities($userId),
            ]
        ];

        return response()->json(['data' => $overview]);
    }

    /**
     * Get upcoming items for API.
     */
    public function apiUpcoming(Request $request)
    {
        $userId = auth()->id();
        $days = $request->get('days', 30);

        $upcoming = [
            'subscriptions' => Subscription::where('user_id', $userId)
                ->dueSoon($days)
                ->orderBy('next_billing_date')
                ->get()
                ->map(function ($sub) {
                    return [
                        'id' => $sub->id,
                        'type' => 'subscription',
                        'title' => $sub->service_name,
                        'amount' => $sub->cost,
                        'currency' => $sub->currency,
                        'due_date' => $sub->next_billing_date,
                        'days_until_due' => now()->diffInDays($sub->next_billing_date, false),
                        'url' => route('subscriptions.show', $sub),
                    ];
                }),

            'contracts' => Contract::where('user_id', $userId)
                ->expiringSoon($days)
                ->orderBy('end_date')
                ->get()
                ->map(function ($contract) {
                    return [
                        'id' => $contract->id,
                        'type' => 'contract',
                        'title' => $contract->title,
                        'counterparty' => $contract->counterparty,
                        'end_date' => $contract->end_date,
                        'days_until_expiry' => now()->diffInDays($contract->end_date, false),
                        'requires_notice' => $contract->notice_period_days ? true : false,
                        'notice_deadline' => $contract->notice_deadline,
                        'url' => route('contracts.show', $contract),
                    ];
                }),

            'warranties' => Warranty::where('user_id', $userId)
                ->where('warranty_expiration_date', '<=', now()->addDays($days))
                ->where('current_status', 'active')
                ->orderBy('warranty_expiration_date')
                ->get()
                ->map(function ($warranty) {
                    return [
                        'id' => $warranty->id,
                        'type' => 'warranty',
                        'title' => $warranty->product_name,
                        'brand' => $warranty->brand,
                        'expiration_date' => $warranty->warranty_expiration_date,
                        'days_until_expiry' => now()->diffInDays($warranty->warranty_expiration_date, false),
                        'value' => $warranty->purchase_price,
                        'url' => route('warranties.show', $warranty),
                    ];
                }),

            'utility_bills' => UtilityBill::where('user_id', $userId)
                ->where('due_date', '<=', now()->addDays($days))
                ->where('payment_status', 'pending')
                ->orderBy('due_date')
                ->get()
                ->map(function ($bill) {
                    return [
                        'id' => $bill->id,
                        'type' => 'utility_bill',
                        'title' => $bill->service_provider . ' - ' . ucfirst($bill->utility_type),
                        'amount' => $bill->bill_amount,
                        'due_date' => $bill->due_date,
                        'days_until_due' => now()->diffInDays($bill->due_date, false),
                        'is_overdue' => $bill->due_date < now(),
                        'url' => route('utility-bills.show', $bill),
                    ];
                }),
        ];

        // Combine and sort all upcoming items by date
        $allUpcoming = collect()
            ->merge($upcoming['subscriptions'])
            ->merge($upcoming['contracts'])
            ->merge($upcoming['warranties'])
            ->merge($upcoming['utility_bills'])
            ->sortBy(function ($item) {
                return $item['due_date'] ?? $item['end_date'] ?? $item['expiration_date'];
            });

        return response()->json([
            'data' => [
                'by_type' => $upcoming,
                'combined' => $allUpcoming->values(),
                'summary' => [
                    'total_items' => $allUpcoming->count(),
                    'overdue_items' => $allUpcoming->where('is_overdue', true)->count(),
                    'due_this_week' => $allUpcoming->filter(function ($item) {
                        $dueDate = $item['due_date'] ?? $item['end_date'] ?? $item['expiration_date'];
                        return $dueDate && now()->diffInDays($dueDate, false) <= 7;
                    })->count(),
                ]
            ]
        ]);
    }

    /**
     * Get alerts and notifications for API.
     */
    public function apiAlerts(Request $request)
    {
        $userId = auth()->id();

        $alerts = [
            'high_priority' => [],
            'medium_priority' => [],
            'low_priority' => [],
        ];

        // High Priority Alerts
        // Overdue bills
        $overdueBills = UtilityBill::where('user_id', $userId)
            ->where('payment_status', 'overdue')
            ->get();
        foreach ($overdueBills as $bill) {
            $alerts['high_priority'][] = [
                'id' => 'overdue_bill_' . $bill->id,
                'type' => 'overdue_payment',
                'severity' => 'high',
                'title' => 'Overdue Bill',
                'message' => "{$bill->service_provider} bill of {$bill->bill_amount} was due on {$bill->due_date->format('M j, Y')}",
                'action_url' => route('utility-bills.show', $bill),
                'action_text' => 'Pay Now',
                'created_at' => $bill->due_date,
            ];
        }

        // Subscriptions renewing today
        $subscriptionsRenewingToday = Subscription::where('user_id', $userId)
            ->whereDate('next_billing_date', now())
            ->where('status', 'active')
            ->get();
        foreach ($subscriptionsRenewingToday as $sub) {
            $alerts['high_priority'][] = [
                'id' => 'subscription_renewing_' . $sub->id,
                'type' => 'subscription_renewal',
                'severity' => 'high',
                'title' => 'Subscription Renewing Today',
                'message' => "{$sub->service_name} subscription renews today for {$sub->currency} {$sub->cost}",
                'action_url' => route('subscriptions.show', $sub),
                'action_text' => 'Manage',
                'created_at' => now(),
            ];
        }

        // Medium Priority Alerts
        // Contracts requiring notice
        $contractsRequiringNotice = Contract::where('user_id', $userId)
            ->whereNotNull('notice_period_days')
            ->whereNotNull('end_date')
            ->where('status', 'active')
            ->whereRaw('DATEDIFF(end_date, CURDATE()) <= notice_period_days')
            ->get();
        foreach ($contractsRequiringNotice as $contract) {
            $alerts['medium_priority'][] = [
                'id' => 'contract_notice_' . $contract->id,
                'type' => 'contract_notice',
                'severity' => 'medium',
                'title' => 'Contract Notice Required',
                'message' => "{$contract->title} requires notice by {$contract->notice_deadline->format('M j, Y')}",
                'action_url' => route('contracts.show', $contract),
                'action_text' => 'Review',
                'created_at' => now(),
            ];
        }

        // Bills due in 3 days
        $billsDueSoon = UtilityBill::where('user_id', $userId)
            ->whereBetween('due_date', [now()->addDays(1), now()->addDays(3)])
            ->where('payment_status', 'pending')
            ->get();
        foreach ($billsDueSoon as $bill) {
            $alerts['medium_priority'][] = [
                'id' => 'bill_due_soon_' . $bill->id,
                'type' => 'bill_due_soon',
                'severity' => 'medium',
                'title' => 'Bill Due Soon',
                'message' => "{$bill->service_provider} bill of {$bill->bill_amount} is due on {$bill->due_date->format('M j, Y')}",
                'action_url' => route('utility-bills.show', $bill),
                'action_text' => 'View',
                'created_at' => $bill->due_date->subDays(3),
            ];
        }

        // Low Priority Alerts
        // Warranties expiring in 30 days
        $warrantiesExpiringSoon = Warranty::where('user_id', $userId)
            ->whereBetween('warranty_expiration_date', [now()->addDays(15), now()->addDays(30)])
            ->where('current_status', 'active')
            ->get();
        foreach ($warrantiesExpiringSoon as $warranty) {
            $alerts['low_priority'][] = [
                'id' => 'warranty_expiring_' . $warranty->id,
                'type' => 'warranty_expiring',
                'severity' => 'low',
                'title' => 'Warranty Expiring Soon',
                'message' => "{$warranty->product_name} warranty expires on {$warranty->warranty_expiration_date->format('M j, Y')}",
                'action_url' => route('warranties.show', $warranty),
                'action_text' => 'View',
                'created_at' => $warranty->warranty_expiration_date->subDays(30),
            ];
        }

        // Subscription renewals in 7 days
        $subscriptionsDueSoon = Subscription::where('user_id', $userId)
            ->whereBetween('next_billing_date', [now()->addDays(5), now()->addDays(7)])
            ->where('status', 'active')
            ->get();
        foreach ($subscriptionsDueSoon as $sub) {
            $alerts['low_priority'][] = [
                'id' => 'subscription_due_' . $sub->id,
                'type' => 'subscription_due_soon',
                'severity' => 'low',
                'title' => 'Subscription Renewal Soon',
                'message' => "{$sub->service_name} renews on {$sub->next_billing_date->format('M j, Y')} for {$sub->currency} {$sub->cost}",
                'action_url' => route('subscriptions.show', $sub),
                'action_text' => 'Manage',
                'created_at' => $sub->next_billing_date->subDays(7),
            ];
        }

        // Sort alerts by created_at desc within each priority
        foreach ($alerts as $priority => $priorityAlerts) {
            $alerts[$priority] = collect($priorityAlerts)
                ->sortByDesc('created_at')
                ->values()
                ->toArray();
        }

        return response()->json([
            'data' => [
                'alerts' => $alerts,
                'summary' => [
                    'total_alerts' => array_sum(array_map('count', $alerts)),
                    'high_priority_count' => count($alerts['high_priority']),
                    'medium_priority_count' => count($alerts['medium_priority']),
                    'low_priority_count' => count($alerts['low_priority']),
                ]
            ]
        ]);
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
