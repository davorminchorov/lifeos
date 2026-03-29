<?php

declare(strict_types=1);

namespace App\Ai\Tools;

use App\Models\Expense;
use App\Models\Subscription;
use App\Models\UtilityBill;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Tools\Request;

class LogPayment extends TenantScopedTool
{
    public function description(): string
    {
        return 'Log a payment for a subscription or utility bill.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'subscription_name' => $schema->string()->description('Name of the subscription to log payment for'),
            'utility_type' => $schema->string()->description('Type of utility bill to log payment for (e.g. electricity, water, internet)'),
            'amount' => $schema->number()->required()->description('Payment amount'),
            'date' => $schema->string()->description('Payment date in YYYY-MM-DD format, defaults to today'),
            'notes' => $schema->string()->description('Additional notes about the payment'),
        ];
    }

    public function handle(Request $request): string
    {
        $subscriptionName = $request['subscription_name'];
        $utilityType = $request['utility_type'];
        $amount = $request['amount'];
        $date = $request['date'] ?? date('Y-m-d');
        $notes = $request['notes'];

        $validated = $this->validate(
            ['amount' => $amount, 'date' => $date],
            [
                'amount' => 'required|numeric|min:0.01|max:99999999',
                'date' => 'required|date',
            ],
        );

        if (is_string($validated)) {
            return $validated;
        }

        if ($subscriptionName) {
            return $this->logSubscriptionPayment($subscriptionName, (float) $amount, $date, $notes);
        }

        if ($utilityType) {
            return $this->logUtilityPayment($utilityType, (float) $amount, $date, $notes);
        }

        return 'Please provide either a subscription_name or utility_type to log the payment against.';
    }

    private function logSubscriptionPayment(string $name, float $amount, string $date, ?string $notes): string
    {
        $subscription = $this->scopedQuery(Subscription::class)
            ->where('service_name', 'LIKE', '%'.$name.'%')
            ->first();

        if (! $subscription) {
            $available = $this->scopedQuery(Subscription::class)
                ->pluck('service_name')
                ->implode(', ');

            return "No subscription found matching '{$name}'. Available subscriptions: {$available}";
        }

        Expense::create([
            'user_id' => $this->userId,
            'tenant_id' => $this->tenantId,
            'amount' => $amount,
            'currency' => $subscription->currency ?? 'MKD',
            'category' => $subscription->service_name,
            'expense_date' => $date,
            'description' => 'Subscription payment: '.$subscription->service_name,
            'merchant' => $subscription->service_name,
            'notes' => $notes,
        ]);

        return "Logged payment of {$amount} for subscription '{$subscription->service_name}' on {$date}.";
    }

    private function logUtilityPayment(string $type, float $amount, string $date, ?string $notes): string
    {
        $bill = $this->scopedQuery(UtilityBill::class)
            ->where('utility_type', 'LIKE', '%'.$type.'%')
            ->where('payment_status', 'pending')
            ->orderBy('due_date')
            ->first();

        if (! $bill) {
            $available = $this->scopedQuery(UtilityBill::class)
                ->where('payment_status', 'pending')
                ->pluck('utility_type')
                ->unique()
                ->implode(', ');

            if ($available === '') {
                return "No pending utility bills found matching '{$type}'. There are no pending utility bills.";
            }

            return "No pending utility bill found matching '{$type}'. Available pending bills: {$available}";
        }

        $bill->update([
            'payment_status' => 'paid',
            'payment_date' => CarbonImmutable::parse($date),
        ]);

        return "Marked {$bill->utility_type} bill of {$bill->bill_amount} as paid on {$date}.";
    }
}
