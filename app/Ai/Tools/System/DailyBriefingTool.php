<?php

namespace App\Ai\Tools\System;

use App\Ai\Tools\Concerns\ResolvesContext;
use App\Models\Budget;
use App\Models\Contract;
use App\Models\Expense;
use App\Models\Iou;
use App\Models\JobApplication;
use App\Models\Subscription;
use App\Models\UtilityBill;
use App\Models\Warranty;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;

class DailyBriefingTool implements Tool
{
    use ResolvesContext;

    public function description(): string
    {
        return 'Generate a daily briefing with key financial and life status. Use when the user says /briefing, asks for a summary, or wants to know their current status.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [];
    }

    public function handle(Request $request): string
    {
        $tenantId = $this->tenantId();
        $lines = ['Daily Briefing — '.now()->format('l, M j, Y')];

        // Bills due this week
        $billsDue = UtilityBill::where('tenant_id', $tenantId)
            ->where('payment_status', 'pending')
            ->where('due_date', '<=', now()->addDays(7))
            ->orderBy('due_date')
            ->get();

        if ($billsDue->isNotEmpty()) {
            $lines[] = "\nBills Due This Week:";
            foreach ($billsDue as $bill) {
                $amount = $this->formatAmount($bill->bill_amount, $bill->currency);
                $due = $bill->due_date->format('M j');
                $overdue = $bill->is_overdue ? ' [OVERDUE]' : '';
                $lines[] = "- {$bill->service_provider}: {$amount} due {$due}{$overdue}";
            }
        }

        // Subscriptions renewing soon
        $subs = Subscription::where('tenant_id', $tenantId)
            ->dueSoon(7)
            ->get();

        if ($subs->isNotEmpty()) {
            $lines[] = "\nSubscriptions Renewing Soon:";
            foreach ($subs as $sub) {
                $amount = $this->formatAmount($sub->cost, $sub->currency);
                $next = $sub->next_billing_date->format('M j');
                $lines[] = "- {$sub->service_name}: {$amount} on {$next}";
            }
        }

        // Month spending summary
        $monthSpent = Expense::where('tenant_id', $tenantId)
            ->currentMonth()
            ->sum('amount');
        $lines[] = "\nMonth Spending: ".$this->formatAmount($monthSpent);

        // Budget status
        $budgets = Budget::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->current()
            ->get();

        $overBudget = $budgets->filter(fn ($b) => $b->isExceeded());
        $warningBudgets = $budgets->filter(fn ($b) => $b->isOverThreshold() && ! $b->isExceeded());

        if ($overBudget->isNotEmpty()) {
            $lines[] = 'Budgets exceeded: '.$overBudget->pluck('category')->join(', ');
        }
        if ($warningBudgets->isNotEmpty()) {
            $lines[] = 'Budgets near limit: '.$warningBudgets->pluck('category')->join(', ');
        }

        // Active job applications
        $activeJobs = JobApplication::where('tenant_id', $tenantId)
            ->active()
            ->whereNotIn('status', ['rejected', 'withdrawn', 'accepted'])
            ->count();

        if ($activeJobs > 0) {
            $lines[] = "\nActive Job Applications: {$activeJobs}";
        }

        // Contracts expiring soon
        $expiringContracts = Contract::where('tenant_id', $tenantId)
            ->expiringSoon(30)
            ->get();

        if ($expiringContracts->isNotEmpty()) {
            $lines[] = "\nContracts Expiring Soon:";
            foreach ($expiringContracts as $contract) {
                $days = $contract->days_until_expiration;
                $lines[] = "- {$contract->title}: {$days} days left";
            }
        }

        // Warranties expiring soon
        $expiringWarranties = Warranty::where('tenant_id', $tenantId)
            ->expiringSoon(30)
            ->get();

        if ($expiringWarranties->isNotEmpty()) {
            $lines[] = "\nWarranties Expiring Soon:";
            foreach ($expiringWarranties as $warranty) {
                $days = $warranty->days_until_expiration;
                $lines[] = "- {$warranty->product_name}: {$days} days left";
            }
        }

        // Pending IOUs
        $pendingIous = Iou::where('tenant_id', $tenantId)->pending()->get();
        $iOweTotal = $pendingIous->where('type', 'owe')->sum('remaining_balance');
        $owedToMeTotal = $pendingIous->where('type', 'owed')->sum('remaining_balance');

        if ($iOweTotal > 0 || $owedToMeTotal > 0) {
            $lines[] = "\nDebts:";
            if ($iOweTotal > 0) {
                $lines[] = '- I owe: '.$this->formatAmount($iOweTotal);
            }
            if ($owedToMeTotal > 0) {
                $lines[] = '- Owed to me: '.$this->formatAmount($owedToMeTotal);
            }
        }

        return implode("\n", $lines);
    }
}
