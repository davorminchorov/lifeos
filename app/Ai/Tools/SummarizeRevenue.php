<?php

declare(strict_types=1);

namespace App\Ai\Tools;

use App\Models\Invoice;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\DB;
use Laravel\Ai\Tools\Request;

class SummarizeRevenue extends TenantScopedTool
{
    public function description(): string
    {
        return 'Summarize revenue from invoices for a given time period.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'period' => $schema->string()->description('Time period: this_month, last_month, this_year, last_30_days. Defaults to this_month'),
        ];
    }

    public function handle(Request $request): string
    {
        $period = $request['period'] ?? 'this_month';
        $now = CarbonImmutable::now();

        [$startDate, $endDate, $label] = match ($period) {
            'last_month' => [
                $now->subMonth()->startOfMonth()->toDateString(),
                $now->subMonth()->endOfMonth()->toDateString(),
                'Last month',
            ],
            'this_year' => [
                $now->startOfYear()->toDateString(),
                $now->endOfYear()->toDateString(),
                'This year',
            ],
            'last_30_days' => [
                $now->subDays(30)->toDateString(),
                $now->toDateString(),
                'Last 30 days',
            ],
            default => [
                $now->startOfMonth()->toDateString(),
                $now->endOfMonth()->toDateString(),
                'This month',
            ],
        };

        $query = $this->scopedQuery(Invoice::class)
            ->where('issued_at', '>=', $startDate)
            ->where('issued_at', '<=', $endDate.' 23:59:59');

        $totalInvoices = $query->count();

        if ($totalInvoices === 0) {
            return "{$label}: No invoices found.";
        }

        $statusBreakdown = (clone $query)
            ->select('status', DB::raw('COUNT(*) as count'), DB::raw('SUM(total) as total'))
            ->groupBy('status')
            ->get();

        $lines = [];
        $lines[] = "{$label} REVENUE SUMMARY:";

        $grandTotal = 0;
        $paidTotal = 0;

        foreach ($statusBreakdown as $row) {
            $status = $row->status instanceof \BackedEnum ? $row->status->value : $row->status;
            $amount = (int) $row->total / 100;
            $grandTotal += $amount;

            if (in_array(strtoupper($status), ['PAID', 'PARTIALLY_PAID'])) {
                $paidTotal += $amount;
            }

            $lines[] = sprintf(
                '- %s: %d invoices, %s total',
                strtolower($status),
                $row->count,
                number_format($amount, 2),
            );
        }

        $lines[] = '';
        $lines[] = sprintf('Total invoiced: %s', number_format($grandTotal, 2));
        $lines[] = sprintf('Total collected: %s', number_format($paidTotal, 2));
        $lines[] = sprintf('Outstanding: %s', number_format($grandTotal - $paidTotal, 2));

        return implode("\n", $lines);
    }
}
