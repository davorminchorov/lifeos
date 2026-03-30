<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Budget;
use App\Models\Contract;
use App\Models\CycleMenu;
use App\Models\Expense;
use App\Models\Holiday;
use App\Models\Investment;
use App\Models\Invoice;
use App\Models\Iou;
use App\Models\JobApplication;
use App\Models\Subscription;
use App\Models\User;
use App\Models\UtilityBill;
use App\Models\Warranty;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;

final class AssistantContextService
{
    public function loadForPage(User $user, string $page = ''): string
    {
        $tenantId = $user->current_tenant_id;

        $summary = Cache::remember(
            "assistant_context_{$user->id}_{$tenantId}",
            60,
            fn (): string => $this->buildSummary($user->id, $tenantId),
        );

        $pageContext = $this->loadPageContext($tenantId, $user->id, $page);

        if ($pageContext !== '') {
            return $summary."\n".$pageContext;
        }

        return $summary;
    }

    private function buildSummary(int $userId, int $tenantId): string
    {
        $lines = [];

        $lines[] = $this->buildSubscriptionSummary($tenantId);
        $lines[] = $this->buildRecentExpenses($tenantId);
        $lines[] = $this->buildJobApplicationsSummary($tenantId);
        $lines[] = $this->buildBudgetsSummary($tenantId, $userId);
        $lines[] = $this->buildPendingBills($tenantId);
        $lines[] = $this->buildIousSummary($tenantId);
        $lines[] = $this->buildInvestmentsSummary($tenantId);
        $lines[] = $this->buildInvoicesSummary($tenantId);
        $lines[] = $this->buildContractsSummary($tenantId);
        $lines[] = $this->buildWarrantiesSummary($tenantId);

        return implode("\n", array_filter($lines));
    }

    private function buildSubscriptionSummary(int $tenantId): string
    {
        $active = Subscription::query()
            ->where('tenant_id', $tenantId)
            ->active()
            ->get();

        if ($active->isEmpty()) {
            return 'SUBSCRIPTIONS: None active';
        }

        // monthly_cost is a computed accessor on Subscription (from cost + billing_cycle)
        $total = $active->sum(fn (Subscription $s): float => (float) $s->monthly_cost);

        return sprintf(
            'SUBSCRIPTIONS: %d active, %s/month total',
            $active->count(),
            number_format($total, 2),
        );
    }

    private function buildRecentExpenses(int $tenantId): string
    {
        $expenses = Expense::query()
            ->where('tenant_id', $tenantId)
            ->orderByDesc('expense_date')
            ->limit(5)
            ->get();

        if ($expenses->isEmpty()) {
            return 'RECENT EXPENSES: None';
        }

        $items = $expenses->map(
            fn (Expense $e): string => sprintf(
                '%s %s (%s) %s',
                number_format((float) $e->amount, 2),
                $e->merchant ?? 'Unknown',
                $e->category,
                $e->expense_date->format('M j'),
            ),
        );

        return 'RECENT EXPENSES: '.$items->implode(' | ');
    }

    private function buildJobApplicationsSummary(int $tenantId): string
    {
        $applications = JobApplication::query()
            ->where('tenant_id', $tenantId)
            ->whereNotIn('status', ['rejected', 'withdrawn', 'archived'])
            ->get();

        if ($applications->isEmpty()) {
            return 'JOB APPLICATIONS: None open';
        }

        $items = $applications->map(
            fn (JobApplication $a): string => sprintf(
                '%s (%s, %s)',
                $a->company_name,
                $a->job_title,
                $a->status instanceof \BackedEnum ? $a->status->value : $a->status,
            ),
        );

        return 'JOB APPLICATIONS: '.$items->implode(' | ');
    }

    private function buildBudgetsSummary(int $tenantId, int $userId): string
    {
        $budgets = Budget::query()
            ->where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->get();

        if ($budgets->isEmpty()) {
            return 'BUDGETS: None active';
        }

        $items = $budgets->map(function (Budget $b): string {
            $spent = $b->getCurrentSpending();
            $pct = $b->getUtilizationPercentage();

            return sprintf(
                '%s %s/%s (%s%%)',
                $b->category,
                number_format((float) $spent, 2),
                number_format((float) $b->amount, 2),
                $pct,
            );
        });

        return 'BUDGETS: '.$items->implode(' | ');
    }

    private function buildPendingBills(int $tenantId): string
    {
        $bills = UtilityBill::query()
            ->where('tenant_id', $tenantId)
            ->where('payment_status', 'pending')
            ->orderBy('due_date')
            ->get();

        if ($bills->isEmpty()) {
            return 'PENDING BILLS: None';
        }

        $items = $bills->map(
            fn (UtilityBill $b): string => sprintf(
                '%s %s due %s',
                $b->utility_type,
                number_format((float) $b->bill_amount, 2),
                $b->due_date->format('M j'),
            ),
        );

        return 'PENDING BILLS: '.$items->implode(' | ');
    }

    private function buildIousSummary(int $tenantId): string
    {
        $ious = Iou::query()
            ->where('tenant_id', $tenantId)
            ->where('status', 'pending')
            ->get();

        if ($ious->isEmpty()) {
            return 'IOUS: None outstanding';
        }

        $items = $ious->map(
            fn (Iou $i): string => match ($i->type) {
                'owed' => sprintf('%s owes you %s', $i->person_name, number_format((float) $i->amount, 2)),
                'owe' => sprintf('You owe %s %s', $i->person_name, number_format((float) $i->amount, 2)),
                default => sprintf('%s %s %s', $i->person_name, $i->type, number_format((float) $i->amount, 2)),
            },
        );

        return 'IOUS: '.$items->implode(' | ');
    }

    private function loadPageContext(int $tenantId, int $userId, string $page): string
    {
        if (str_starts_with($page, 'Expenses')) {
            return $this->loadExpenseCategories($tenantId);
        }

        if (str_starts_with($page, 'Subscriptions')) {
            return $this->loadSubscriptionDetails($tenantId);
        }

        if (str_starts_with($page, 'JobApplications')) {
            return $this->loadJobApplicationDetails($tenantId);
        }

        if (str_starts_with($page, 'CycleMenus')) {
            return $this->loadCycleMenuDetails($tenantId);
        }

        if (str_starts_with($page, 'Investments')) {
            return $this->loadInvestmentDetails($tenantId);
        }

        if (str_starts_with($page, 'Invoic')) {
            return $this->loadInvoiceDetails($tenantId);
        }

        if (str_starts_with($page, 'Budgets')) {
            return $this->loadBudgetDetails($tenantId, $userId);
        }

        if (str_starts_with($page, 'Contracts')) {
            return $this->loadContractDetails($tenantId);
        }

        if (str_starts_with($page, 'Warranties')) {
            return $this->loadWarrantyDetails($tenantId);
        }

        if (str_starts_with($page, 'Holidays')) {
            return $this->loadHolidayDetails($tenantId);
        }

        return '';
    }

    private function loadExpenseCategories(int $tenantId): string
    {
        $categories = Expense::query()
            ->where('tenant_id', $tenantId)
            ->where('expense_date', '>=', CarbonImmutable::now()->subDays(30))
            ->distinct()
            ->pluck('category')
            ->filter()
            ->sort()
            ->values();

        if ($categories->isEmpty()) {
            return '';
        }

        return 'EXPENSE CATEGORIES (last 30d): '.$categories->implode(', ');
    }

    private function loadSubscriptionDetails(int $tenantId): string
    {
        $subs = Subscription::query()
            ->where('tenant_id', $tenantId)
            ->active()
            ->orderBy('service_name')
            ->get();

        if ($subs->isEmpty()) {
            return '';
        }

        $items = $subs->map(
            fn (Subscription $s): string => sprintf(
                '%s %s/mo',
                $s->service_name,
                number_format((float) $s->monthly_cost, 2),
            ),
        );

        return 'ALL SUBSCRIPTIONS: '.$items->implode(' | ');
    }

    private function buildInvestmentsSummary(int $tenantId): string
    {
        $active = Investment::query()
            ->where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->get();

        if ($active->isEmpty()) {
            return 'INVESTMENTS: None active';
        }

        $totalValue = $active->sum(fn (Investment $i): float => (float) $i->current_market_value);

        return sprintf(
            'INVESTMENTS: %d active, %s total market value',
            $active->count(),
            number_format($totalValue, 2),
        );
    }

    private function buildInvoicesSummary(int $tenantId): string
    {
        $unpaid = Invoice::query()
            ->where('tenant_id', $tenantId)
            ->whereIn('status', ['issued', 'partially_paid', 'past_due'])
            ->get();

        if ($unpaid->isEmpty()) {
            return 'INVOICES: None outstanding';
        }

        $totalDue = $unpaid->sum(fn (Invoice $i): float => $i->amount_due / 100);

        return sprintf(
            'INVOICES: %d outstanding, %s total due',
            $unpaid->count(),
            number_format($totalDue, 2),
        );
    }

    private function buildContractsSummary(int $tenantId): string
    {
        $active = Contract::query()
            ->where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->get();

        if ($active->isEmpty()) {
            return 'CONTRACTS: None active';
        }

        $expiringSoon = $active->filter(fn (Contract $c) => $c->end_date && $c->days_until_expiration <= 30)->count();
        $expiringNote = $expiringSoon > 0 ? ", {$expiringSoon} expiring within 30 days" : '';

        return sprintf(
            'CONTRACTS: %d active%s',
            $active->count(),
            $expiringNote,
        );
    }

    private function buildWarrantiesSummary(int $tenantId): string
    {
        $active = Warranty::query()
            ->where('tenant_id', $tenantId)
            ->where('current_status', 'active')
            ->get();

        if ($active->isEmpty()) {
            return 'WARRANTIES: None active';
        }

        $expiringSoon = $active->filter(fn (Warranty $w) => $w->days_until_expiration <= 30)->count();
        $expiringNote = $expiringSoon > 0 ? ", {$expiringSoon} expiring within 30 days" : '';

        return sprintf(
            'WARRANTIES: %d active%s',
            $active->count(),
            $expiringNote,
        );
    }

    private function loadCycleMenuDetails(int $tenantId): string
    {
        $menus = CycleMenu::query()
            ->where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->withCount('days')
            ->orderBy('name')
            ->get();

        if ($menus->isEmpty()) {
            return '';
        }

        $items = $menus->map(
            fn (CycleMenu $m): string => sprintf(
                '%s (%d-day cycle, starts %s)',
                $m->name,
                $m->cycle_length_days,
                $m->starts_on->format('M j'),
            ),
        );

        return 'ACTIVE CYCLE MENUS: '.$items->implode(' | ');
    }

    private function loadInvestmentDetails(int $tenantId): string
    {
        $investments = Investment::query()
            ->where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        if ($investments->isEmpty()) {
            return '';
        }

        $items = $investments->map(
            fn (Investment $i): string => sprintf(
                '%s %s %s @ %s',
                $i->symbol_identifier ?? '',
                $i->name,
                number_format((float) $i->quantity, 2),
                number_format((float) $i->current_value, 2),
            ),
        );

        return 'ACTIVE INVESTMENTS: '.$items->implode(' | ');
    }

    private function loadInvoiceDetails(int $tenantId): string
    {
        $invoices = Invoice::query()
            ->where('tenant_id', $tenantId)
            ->whereIn('status', ['draft', 'issued', 'partially_paid', 'past_due'])
            ->with('customer')
            ->orderBy('due_at')
            ->get();

        if ($invoices->isEmpty()) {
            return '';
        }

        $items = $invoices->map(
            fn (Invoice $i): string => sprintf(
                '%s %s %s due %s',
                $i->number ?? 'DRAFT',
                $i->customer?->name ?? 'N/A',
                number_format($i->amount_due / 100, 2),
                $i->due_at?->format('M j') ?? 'N/A',
            ),
        );

        return 'OPEN INVOICES: '.$items->implode(' | ');
    }

    private function loadBudgetDetails(int $tenantId, int $userId): string
    {
        $budgets = Budget::query()
            ->where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->get();

        if ($budgets->isEmpty()) {
            return '';
        }

        $items = $budgets->map(function (Budget $b): string {
            $pct = $b->getUtilizationPercentage();

            return sprintf(
                '%s %s%% of %s',
                $b->category,
                $pct,
                number_format((float) $b->amount, 2),
            );
        });

        return 'ALL BUDGETS: '.$items->implode(' | ');
    }

    private function loadContractDetails(int $tenantId): string
    {
        $contracts = Contract::query()
            ->where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->orderBy('end_date')
            ->get();

        if ($contracts->isEmpty()) {
            return '';
        }

        $items = $contracts->map(
            fn (Contract $c): string => sprintf(
                '%s with %s%s',
                $c->title,
                $c->counterparty,
                $c->end_date ? ' expires '.$c->end_date->format('M j') : '',
            ),
        );

        return 'ACTIVE CONTRACTS: '.$items->implode(' | ');
    }

    private function loadWarrantyDetails(int $tenantId): string
    {
        $warranties = Warranty::query()
            ->where('tenant_id', $tenantId)
            ->where('current_status', 'active')
            ->orderBy('warranty_expiration_date')
            ->get();

        if ($warranties->isEmpty()) {
            return '';
        }

        $items = $warranties->map(
            fn (Warranty $w): string => sprintf(
                '%s (%s) expires %s',
                $w->product_name,
                $w->brand ?? 'N/A',
                $w->warranty_expiration_date->format('M j'),
            ),
        );

        return 'ACTIVE WARRANTIES: '.$items->implode(' | ');
    }

    private function loadHolidayDetails(int $tenantId): string
    {
        $holidays = Holiday::query()
            ->where('tenant_id', $tenantId)
            ->where('date', '>=', CarbonImmutable::now()->toDateString())
            ->orderBy('date')
            ->limit(10)
            ->get();

        if ($holidays->isEmpty()) {
            return '';
        }

        $items = $holidays->map(
            fn (Holiday $h): string => sprintf(
                '%s %s (%s)',
                $h->date->format('M j'),
                $h->name,
                $h->country,
            ),
        );

        return 'UPCOMING HOLIDAYS: '.$items->implode(' | ');
    }

    private function loadJobApplicationDetails(int $tenantId): string
    {
        $apps = JobApplication::query()
            ->where('tenant_id', $tenantId)
            ->whereNotIn('status', ['rejected', 'withdrawn', 'archived'])
            ->orderByDesc('applied_at')
            ->get();

        if ($apps->isEmpty()) {
            return '';
        }

        $items = $apps->map(
            fn (JobApplication $a): string => sprintf(
                '%s — %s (%s) applied %s%s',
                $a->company_name,
                $a->job_title,
                $a->status instanceof \BackedEnum ? $a->status->value : $a->status,
                $a->applied_at?->format('M j') ?? 'N/A',
                $a->salary_max ? sprintf(' salary %s-%s %s', number_format((float) $a->salary_min, 0), number_format((float) $a->salary_max, 0), $a->currency) : '',
            ),
        );

        return 'ALL JOB APPLICATIONS: '.$items->implode(' | ');
    }
}
