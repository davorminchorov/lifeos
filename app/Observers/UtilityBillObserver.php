<?php

namespace App\Observers;

use App\Models\Expense;
use App\Models\UtilityBill;
use Illuminate\Support\Facades\Log;

class UtilityBillObserver
{
    public function updated(UtilityBill $bill): void
    {
        try {
            // Create expense when bill transitions to paid
            if ($bill->wasChanged('payment_status') && $bill->payment_status === 'paid') {
                $this->createExpenseIfMissing($bill);
            }

            // If payment_date was set while already paid (edge-case), ensure expense exists
            if ($bill->wasChanged('payment_date') && $bill->payment_status === 'paid') {
                $this->createExpenseIfMissing($bill);
            }
        } catch (\Throwable $e) {
            Log::error("UtilityBillObserver failed for bill {$bill->id}: {$e->getMessage()}");
        }
    }

    private function createExpenseIfMissing(UtilityBill $bill): void
    {
        $paymentDate = optional($bill->payment_date)?->toDateString() ?? now()->toDateString();
        $tag = 'utility-bill:'.$bill->id;

        $exists = Expense::query()
            ->where('user_id', $bill->user_id)
            ->whereJsonContains('tags', $tag)
            ->exists();

        if ($exists) {
            return; // idempotent
        }

        Expense::create([
            'user_id' => $bill->user_id,
            'amount' => $bill->bill_amount,
            'currency' => $bill->currency ?? config('currency.default', 'MKD'),
            'category' => 'utilities',
            'subcategory' => strtolower((string) $bill->utility_type) ?: null,
            'expense_date' => $paymentDate,
            'description' => sprintf('Payment for %s utility bill (%s)', $bill->utility_type, $bill->service_provider),
            'merchant' => $bill->service_provider,
            'payment_method' => 'Auto/Manual',
            'receipt_attachments' => [],
            'tags' => [$tag, 'utility-bill'],
            'location' => null,
            'is_tax_deductible' => false,
            'expense_type' => 'personal',
            'is_recurring' => false,
            'recurring_schedule' => null,
            'budget_allocated' => $bill->budget_alert_threshold,
            'notes' => 'Created automatically when bill marked as paid',
            'status' => 'confirmed',
        ]);
    }
}
