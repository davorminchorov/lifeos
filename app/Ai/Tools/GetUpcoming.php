<?php

declare(strict_types=1);

namespace App\Ai\Tools;

use App\Models\Contract;
use App\Models\Holiday;
use App\Models\Invoice;
use App\Models\JobApplicationInterview;
use App\Models\RecurringInvoice;
use App\Models\Subscription;
use App\Models\UtilityBill;
use App\Models\Warranty;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Tools\Request;

class GetUpcoming extends TenantScopedTool
{
    public function description(): string
    {
        return 'Get upcoming items that need attention within a number of days (bills, subscriptions, interviews, contracts, warranties).';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'days' => $schema->integer()->description('Number of days to look ahead. Defaults to 7'),
        ];
    }

    public function handle(Request $request): string
    {
        $days = max(1, min(90, (int) ($request['days'] ?? 7)));
        $now = CarbonImmutable::now();
        $cutoff = $now->addDays($days);

        $sections = [];

        $bills = $this->scopedQuery(UtilityBill::class)
            ->where('payment_status', 'pending')
            ->whereBetween('due_date', [$now->toDateString(), $cutoff->toDateString()])
            ->orderBy('due_date')
            ->get();

        if ($bills->isNotEmpty()) {
            $lines = $bills->map(
                fn (UtilityBill $b): string => sprintf(
                    '  - %s: %s due %s',
                    $b->utility_type,
                    number_format((float) $b->bill_amount, 2),
                    $b->due_date->format('M j'),
                ),
            );
            $sections[] = "BILLS:\n".$lines->implode("\n");
        }

        $subscriptions = $this->scopedQuery(Subscription::class)
            ->where('status', 'active')
            ->whereBetween('next_billing_date', [$now->toDateString(), $cutoff->toDateString()])
            ->orderBy('next_billing_date')
            ->get();

        if ($subscriptions->isNotEmpty()) {
            $lines = $subscriptions->map(
                fn (Subscription $s): string => sprintf(
                    '  - %s: %s on %s',
                    $s->service_name,
                    number_format((float) $s->cost, 2),
                    $s->next_billing_date->format('M j'),
                ),
            );
            $sections[] = "SUBSCRIPTIONS:\n".$lines->implode("\n");
        }

        $interviews = $this->scopedQuery(JobApplicationInterview::class)
            ->where('completed', false)
            ->whereBetween('scheduled_at', [$now, $cutoff])
            ->with('jobApplication')
            ->orderBy('scheduled_at')
            ->get();

        if ($interviews->isNotEmpty()) {
            $lines = $interviews->map(
                fn (JobApplicationInterview $i): string => sprintf(
                    '  - %s %s interview on %s',
                    $i->jobApplication?->company_name ?? 'Unknown',
                    $i->type instanceof \BackedEnum ? $i->type->value : $i->type,
                    $i->scheduled_at->format('M j \a\t g:ia'),
                ),
            );
            $sections[] = "INTERVIEWS:\n".$lines->implode("\n");
        }

        $contracts = $this->scopedQuery(Contract::class)
            ->where('status', 'active')
            ->whereNotNull('end_date')
            ->whereBetween('end_date', [$now->toDateString(), $cutoff->toDateString()])
            ->orderBy('end_date')
            ->get();

        if ($contracts->isNotEmpty()) {
            $lines = $contracts->map(
                fn (Contract $c): string => sprintf(
                    '  - %s expires %s',
                    $c->title,
                    $c->end_date->format('M j'),
                ),
            );
            $sections[] = "CONTRACTS EXPIRING:\n".$lines->implode("\n");
        }

        $warranties = $this->scopedQuery(Warranty::class)
            ->where('current_status', 'active')
            ->whereBetween('warranty_expiration_date', [$now->toDateString(), $cutoff->toDateString()])
            ->orderBy('warranty_expiration_date')
            ->get();

        if ($warranties->isNotEmpty()) {
            $lines = $warranties->map(
                fn (Warranty $w): string => sprintf(
                    '  - %s expires %s',
                    $w->product_name,
                    $w->warranty_expiration_date->format('M j'),
                ),
            );
            $sections[] = "WARRANTIES EXPIRING:\n".$lines->implode("\n");
        }

        $invoices = $this->scopedQuery(Invoice::class)
            ->whereIn('status', ['issued', 'partially_paid', 'past_due'])
            ->whereBetween('due_at', [$now->toDateString(), $cutoff->toDateString().' 23:59:59'])
            ->with('customer')
            ->orderBy('due_at')
            ->get();

        if ($invoices->isNotEmpty()) {
            $lines = $invoices->map(
                fn (Invoice $i): string => sprintf(
                    '  - %s: %s %s due %s',
                    $i->number ?? 'DRAFT',
                    $i->customer?->name ?? 'Unknown',
                    number_format($i->amount_due / 100, 2),
                    $i->due_at->format('M j'),
                ),
            );
            $sections[] = "INVOICES DUE:\n".$lines->implode("\n");
        }

        $recurring = RecurringInvoice::query()
            ->where('user_id', $this->userId)
            ->where('status', 'active')
            ->whereBetween('next_billing_date', [$now->toDateString(), $cutoff->toDateString()])
            ->with('customer')
            ->orderBy('next_billing_date')
            ->get();

        if ($recurring->isNotEmpty()) {
            $lines = $recurring->map(
                fn (RecurringInvoice $r): string => sprintf(
                    '  - %s: %s on %s',
                    $r->name,
                    $r->customer?->name ?? 'Unknown',
                    $r->next_billing_date->format('M j'),
                ),
            );
            $sections[] = "RECURRING INVOICES:\n".$lines->implode("\n");
        }

        $holidays = $this->scopedQuery(Holiday::class)
            ->whereBetween('date', [$now->toDateString(), $cutoff->toDateString()])
            ->orderBy('date')
            ->get();

        if ($holidays->isNotEmpty()) {
            $lines = $holidays->map(
                fn (Holiday $h): string => sprintf(
                    '  - %s: %s (%s)',
                    $h->date->format('M j'),
                    $h->name,
                    $h->country,
                ),
            );
            $sections[] = "HOLIDAYS:\n".$lines->implode("\n");
        }

        if ($sections === []) {
            return "Nothing upcoming in the next {$days} days.";
        }

        return "Upcoming in the next {$days} days:\n\n".implode("\n\n", $sections);
    }
}
