<?php

declare(strict_types=1);

namespace App\Ai\Tools;

use App\Models\Invoice;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Tools\Request;

class QueryInvoices extends TenantScopedTool
{
    public function description(): string
    {
        return 'Search and filter invoices by status, customer, date range, or amount.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'status' => $schema->string()->description('Filter by status: draft, issued, paid, partially_paid, past_due, void, written_off, archived'),
            'customer' => $schema->string()->description('Filter by customer name'),
            'date_from' => $schema->string()->description('Start date YYYY-MM-DD for issued_at'),
            'date_to' => $schema->string()->description('End date YYYY-MM-DD for issued_at'),
            'min_amount' => $schema->number()->description('Minimum total amount in whole currency units'),
            'max_amount' => $schema->number()->description('Maximum total amount in whole currency units'),
        ];
    }

    public function handle(Request $request): string
    {
        $query = $this->scopedQuery(Invoice::class)->with('customer');

        $status = $request['status'] ?? null;
        if ($status !== null) {
            $query->where('status', strtoupper($status));
        }

        $customer = $request['customer'] ?? null;
        if ($customer !== null) {
            $query->whereHas('customer', function ($q) use ($customer) {
                $q->where('name', 'LIKE', '%'.$customer.'%');
            });
        }

        $dateFrom = $request['date_from'] ?? null;
        if ($dateFrom !== null) {
            $query->where('issued_at', '>=', $dateFrom);
        }

        $dateTo = $request['date_to'] ?? null;
        if ($dateTo !== null) {
            $query->where('issued_at', '<=', $dateTo.' 23:59:59');
        }

        $minAmount = $request['min_amount'] ?? null;
        if ($minAmount !== null) {
            $query->where('total', '>=', (int) ($minAmount * 100));
        }

        $maxAmount = $request['max_amount'] ?? null;
        if ($maxAmount !== null) {
            $query->where('total', '<=', (int) ($maxAmount * 100));
        }

        $totalCount = $query->count();
        $invoices = $query->orderByDesc('issued_at')->limit(20)->get();

        if ($invoices->isEmpty()) {
            return 'No invoices found matching your criteria.';
        }

        $lines = $invoices->map(function (Invoice $i): string {
            $status = $i->status instanceof \BackedEnum ? $i->status->value : $i->status;

            return sprintf(
                '- %s: %s — %s %s (%s) issued %s, due %s',
                $i->number ?? 'DRAFT',
                $i->customer?->name ?? 'No customer',
                number_format($i->total / 100, 2),
                $i->currency ?? 'MKD',
                strtolower($status),
                $i->issued_at?->format('Y-m-d') ?? 'not issued',
                $i->due_at?->format('Y-m-d') ?? 'no due date',
            );
        });

        $totalAmount = $invoices->sum(fn (Invoice $i): float => $i->total / 100);
        $showing = $invoices->count();

        return "Found {$totalCount} invoices".($totalCount > $showing ? " (showing {$showing})" : '').":\n"
            .$lines->implode("\n")
            ."\nTotal shown: ".number_format($totalAmount, 2);
    }
}
