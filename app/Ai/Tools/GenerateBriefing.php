<?php

declare(strict_types=1);

namespace App\Ai\Tools;

use App\Models\Budget;
use App\Models\Contract;
use App\Models\JobApplicationInterview;
use App\Models\Subscription;
use App\Models\UtilityBill;
use App\Models\Warranty;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Tools\Request;

final class GenerateBriefing extends TenantScopedTool
{
    public function description(): string
    {
        return "Generate a daily briefing summarizing what needs attention today. Use when the user asks 'briefing', 'what's happening today', or similar.";
    }

    public function schema(JsonSchema $schema): array
    {
        return [];
    }

    public function handle(Request $request): string
    {
        $now = CarbonImmutable::now();
        $weekFromNow = $now->addDays(7);

        $sections = [];

        $sections[] = $this->billsSection($now, $weekFromNow);
        $sections[] = $this->subscriptionsSection($now, $weekFromNow);
        $sections[] = $this->interviewsSection($now, $weekFromNow);
        $sections[] = $this->budgetsSection();
        $sections[] = $this->overdueSection($now);

        $content = array_filter($sections);

        if ($content === []) {
            return 'All clear! No urgent items this week.';
        }

        return implode("\n", $content);
    }

    private function billsSection(CarbonImmutable $now, CarbonImmutable $weekFromNow): string
    {
        $bills = $this->scopedQuery(UtilityBill::class)
            ->where('payment_status', 'pending')
            ->whereBetween('due_date', [$now->toDateString(), $weekFromNow->toDateString()])
            ->orderBy('due_date')
            ->get();

        if ($bills->isEmpty()) {
            return '';
        }

        $total = $bills->sum(fn (UtilityBill $b): float => (float) $b->bill_amount);

        $items = $bills->map(
            fn (UtilityBill $b): string => sprintf(
                '%s %s due %s',
                $b->utility_type,
                number_format((float) $b->bill_amount, 2),
                $b->due_date->format('M j'),
            ),
        );

        return sprintf(
            'BILLS: %d due this week (%s total). %s.',
            $bills->count(),
            number_format($total, 2),
            $items->implode(', '),
        );
    }

    private function subscriptionsSection(CarbonImmutable $now, CarbonImmutable $weekFromNow): string
    {
        $renewals = $this->scopedQuery(Subscription::class)
            ->where('status', 'active')
            ->whereBetween('next_billing_date', [$now->toDateString(), $weekFromNow->toDateString()])
            ->orderBy('next_billing_date')
            ->get();

        if ($renewals->isEmpty()) {
            return '';
        }

        $items = $renewals->map(
            fn (Subscription $s): string => sprintf(
                '%s renews %s (%s)',
                $s->service_name,
                $s->next_billing_date->format('M j'),
                number_format((float) $s->cost, 2),
            ),
        );

        return 'SUBSCRIPTIONS: '.$items->implode(', ').'.';
    }

    private function interviewsSection(CarbonImmutable $now, CarbonImmutable $weekFromNow): string
    {
        $interviews = $this->scopedQuery(JobApplicationInterview::class)
            ->where('completed', false)
            ->whereBetween('scheduled_at', [$now, $weekFromNow])
            ->with('jobApplication')
            ->orderBy('scheduled_at')
            ->get();

        if ($interviews->isEmpty()) {
            return '';
        }

        $items = $interviews->map(
            fn (JobApplicationInterview $i): string => sprintf(
                '%s %s interview on %s',
                $i->jobApplication?->company_name ?? 'Unknown',
                $i->type instanceof \BackedEnum ? $i->type->value : $i->type,
                $i->scheduled_at->format('M j \a\t g:ia'),
            ),
        );

        return 'INTERVIEWS: '.$items->implode(', ').'.';
    }

    private function budgetsSection(): string
    {
        $budgets = $this->scopedQuery(Budget::class)
            ->where('is_active', true)
            ->get();

        if ($budgets->isEmpty()) {
            return '';
        }

        $items = $budgets->map(function (Budget $b): string {
            $spent = $b->getCurrentSpending();
            $pct = $b->getUtilizationPercentage();

            return sprintf(
                '%s at %s%% (%s/%s)',
                $b->category,
                $pct,
                number_format((float) $spent, 2),
                number_format((float) $b->amount, 2),
            );
        });

        return 'BUDGETS: '.$items->implode(', ').'.';
    }

    private function overdueSection(CarbonImmutable $now): string
    {
        $overdueParts = [];

        $overdueBills = $this->scopedQuery(UtilityBill::class)
            ->where('payment_status', 'pending')
            ->where('due_date', '<', $now->toDateString())
            ->orderBy('due_date')
            ->get();

        foreach ($overdueBills as $bill) {
            $overdueParts[] = sprintf(
                '%s bill %s was due %s',
                $bill->utility_type,
                number_format((float) $bill->bill_amount, 2),
                $bill->due_date->format('M j'),
            );
        }

        $expiredContracts = $this->scopedQuery(Contract::class)
            ->where('status', 'active')
            ->where('end_date', '<', $now->toDateString())
            ->get();

        foreach ($expiredContracts as $contract) {
            $overdueParts[] = sprintf(
                'Contract "%s" expired %s',
                $contract->title,
                $contract->end_date->format('M j'),
            );
        }

        $expiredWarranties = $this->scopedQuery(Warranty::class)
            ->where('current_status', 'active')
            ->where('warranty_expiration_date', '<', $now->toDateString())
            ->get();

        foreach ($expiredWarranties as $warranty) {
            $overdueParts[] = sprintf(
                'Warranty for %s expired %s',
                $warranty->product_name,
                $warranty->warranty_expiration_date->format('M j'),
            );
        }

        if ($overdueParts === []) {
            return '';
        }

        return 'OVERDUE: '.implode(', ', $overdueParts).'.';
    }
}
