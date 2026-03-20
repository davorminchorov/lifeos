<?php

namespace App\Ai\Tools\UtilityBills;

use App\Ai\Tools\Concerns\ResolvesContext;
use App\Models\UtilityBill;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;

class ListUpcomingBillsTool implements Tool
{
    use ResolvesContext;

    public function description(): string
    {
        return 'List upcoming utility bills and their due dates. Use when the user asks about bills, due dates, or what they need to pay.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'days' => $schema->integer()->description('Show bills due within N days (default: 30)'),
            'include_paid' => $schema->boolean()->description('Include already paid bills (default: false)'),
        ];
    }

    public function handle(Request $request): string
    {
        $days = $request['days'] ?? 30;

        $query = UtilityBill::where('tenant_id', $this->tenantId())
            ->where('due_date', '<=', now()->addDays($days))
            ->orderBy('due_date');

        if (! ($request['include_paid'] ?? false)) {
            $query->where('payment_status', '!=', 'paid');
        }

        $bills = $query->limit(20)->get();

        if ($bills->isEmpty()) {
            return "No upcoming bills in the next {$days} days.";
        }

        $lines = ['Upcoming Bills:'];
        $totalDue = 0;

        foreach ($bills as $bill) {
            $amount = $this->formatAmount($bill->bill_amount, $bill->currency);
            $due = $bill->due_date->format('M j');
            $status = $bill->payment_status === 'paid' ? ' [PAID]' : '';
            $overdue = $bill->is_overdue ? ' [OVERDUE]' : '';
            $lines[] = "- {$bill->service_provider} ({$bill->utility_type}): {$amount} due {$due}{$status}{$overdue}";

            if ($bill->payment_status !== 'paid') {
                $totalDue += $bill->bill_amount;
            }
        }

        $lines[] = 'Total due: '.$this->formatAmount($totalDue);

        return implode("\n", $lines);
    }
}
