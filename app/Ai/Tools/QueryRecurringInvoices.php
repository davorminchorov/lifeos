<?php

declare(strict_types=1);

namespace App\Ai\Tools;

use App\Models\RecurringInvoice;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Tools\Request;

class QueryRecurringInvoices extends TenantScopedTool
{
    public function description(): string
    {
        return 'Query recurring invoices by status, customer, or billing interval.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'status' => $schema->string()->description('Filter by status: active, paused, cancelled, completed'),
            'customer' => $schema->string()->description('Filter by customer name'),
            'billing_interval' => $schema->string()->description('Filter by interval: daily, weekly, monthly, quarterly, yearly'),
        ];
    }

    public function handle(Request $request): string
    {
        $query = RecurringInvoice::query()
            ->where('user_id', $this->userId)
            ->with('customer');

        $status = $request['status'] ?? null;
        if ($status !== null) {
            $query->where('status', strtolower($status));
        }

        $customer = $request['customer'] ?? null;
        if ($customer !== null) {
            $query->whereHas('customer', function ($q) use ($customer) {
                $q->where('name', 'LIKE', '%'.$customer.'%');
            });
        }

        $interval = $request['billing_interval'] ?? null;
        if ($interval !== null) {
            $query->where('billing_interval', strtoupper($interval));
        }

        $totalCount = $query->count();
        $recurring = $query->orderBy('next_billing_date')->limit(20)->get();

        if ($recurring->isEmpty()) {
            return 'No recurring invoices found matching your criteria.';
        }

        $lines = $recurring->map(function (RecurringInvoice $r): string {
            $status = $r->status instanceof \BackedEnum ? $r->status->value : $r->status;
            $interval = $r->billing_interval instanceof \BackedEnum ? $r->billing_interval->value : $r->billing_interval;

            return sprintf(
                '- %s: %s — %s (every %d %s), next billing %s [%s]',
                $r->name,
                $r->customer?->name ?? 'No customer',
                $r->currency ?? 'MKD',
                $r->interval_count,
                strtolower($interval),
                $r->next_billing_date?->format('Y-m-d') ?? 'not set',
                strtolower($status),
            );
        });

        $showing = $recurring->count();

        return "Found {$totalCount} recurring invoices".($totalCount > $showing ? " (showing {$showing})" : '').":\n"
            .$lines->implode("\n");
    }
}
