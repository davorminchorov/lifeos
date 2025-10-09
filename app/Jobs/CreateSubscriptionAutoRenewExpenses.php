<?php

namespace App\Jobs;

use App\Models\Expense;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CreateSubscriptionAutoRenewExpenses implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Starting CreateSubscriptionAutoRenewExpenses job');

        $today = Carbon::today()->toDateString();

        $subscriptions = Subscription::query()
            ->with('user')
            ->where('status', 'active')
            ->where('auto_renewal', true)
            ->whereDate('next_billing_date', $today)
            ->get();

        $created = 0;

        foreach ($subscriptions as $subscription) {
            try {
                $tag = 'subscription:'.$subscription->id;
                $uniqueKey = $tag.':'.$today; // one expense per subscription per billing date

                // Atomic create-or-skip via DB unique key to avoid race conditions
                $expense = Expense::firstOrCreate(
                    ['unique_key' => $uniqueKey],
                    [
                        'user_id' => $subscription->user_id,
                        'amount' => $subscription->cost,
                        'currency' => $subscription->currency ?? config('currency.default', 'MKD'),
                        'category' => $subscription->category ?? 'subscriptions',
                        'subcategory' => null,
                        'expense_date' => $today,
                        'description' => sprintf('Auto-renewal for %s', $subscription->service_name),
                        'merchant' => $subscription->merchant_info ?: $subscription->service_name,
                        'payment_method' => $subscription->payment_method,
                        'receipt_attachments' => [],
                        'tags' => [$tag, 'auto-renewal'],
                        'location' => null,
                        'is_tax_deductible' => false,
                        'expense_type' => 'personal',
                        'is_recurring' => true,
                        'recurring_schedule' => $subscription->billing_cycle ?: 'custom',
                        'budget_allocated' => null,
                        'notes' => 'Created automatically on renewal date',
                        'status' => 'confirmed',
                    ]
                );

                if ($expense->wasRecentlyCreated) {
                    $created++;
                } else {
                    Log::info("Skipping duplicate expense for subscription {$subscription->id} on {$today}");
                }
            } catch (\Throwable $e) {
                Log::error("Failed creating expense for subscription {$subscription->id}: {$e->getMessage()}");
            }
        }

        Log::info("Completed CreateSubscriptionAutoRenewExpenses job. Created {$created} expenses.");
    }
}
