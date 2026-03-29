<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Budget;
use App\Models\Expense;
use App\Models\Iou;
use App\Models\JobApplication;
use App\Models\Subscription;
use App\Models\User;
use App\Models\UtilityBill;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;

final class AssistantContextService
{
    public function loadForPage(User $user, string $page = ''): string
    {
        $tenantId = $user->current_tenant_id;

        $summary = Cache::remember(
            "assistant_context_{$user->id}",
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
