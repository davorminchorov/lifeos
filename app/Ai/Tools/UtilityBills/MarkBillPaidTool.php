<?php

namespace App\Ai\Tools\UtilityBills;

use App\Ai\Tools\Concerns\ResolvesContext;
use App\Models\UtilityBill;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;

class MarkBillPaidTool implements Tool
{
    use ResolvesContext;

    public function description(): string
    {
        return 'Mark a utility bill as paid. Use when the user says they paid a bill. Matches by service provider name (fuzzy match).';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'provider' => $schema->string()->description('Service provider name to search for (e.g. EVN, A1, Telekom, Toplifikacija)')->required(),
            'amount' => $schema->number()->description('Payment amount (optional, for confirmation)'),
        ];
    }

    public function handle(Request $request): string
    {
        $bill = UtilityBill::where('tenant_id', $this->tenantId())
            ->where('payment_status', 'pending')
            ->where('service_provider', 'like', '%'.$request['provider'].'%')
            ->orderBy('due_date')
            ->first();

        if (! $bill) {
            return "No pending bill found matching '{$request['provider']}'. Check the provider name or list upcoming bills.";
        }

        $bill->update([
            'payment_status' => 'paid',
            'payment_date' => now()->toDateString(),
        ]);

        $amount = $this->formatAmount($bill->bill_amount, $bill->currency);

        return "Marked as paid: {$bill->service_provider} ({$bill->utility_type}) — {$amount}";
    }
}
