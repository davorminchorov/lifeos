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

class UpdateSubscriptionNextBillingDates implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Starting UpdateSubscriptionNextBillingDates job');

        $today = Carbon::today();

        $query = Subscription::query()
            ->where('status', 'active')
            ->whereDate('next_billing_date', '<=', $today);

        $count = 0;

        $query->chunkById(200, function ($subscriptions) use ($today, &$count) {
            foreach ($subscriptions as $subscription) {
                try {
                    $originalDate = $subscription->next_billing_date ? Carbon::parse($subscription->next_billing_date) : null;

                    if (! $originalDate) {
                        // If next_billing_date is missing, try to infer from start_date; otherwise skip
                        $inferred = $subscription->start_date ? Carbon::parse($subscription->start_date) : null;
                        if (! $inferred) {
                            Log::warning("Subscription {$subscription->id} has no next_billing_date and no start_date; skipping");

                            continue;
                        }
                        $originalDate = $inferred;
                    }

                    $incrementDays = $this->determineIncrementDays($subscription);

                    if ($incrementDays <= 0) {
                        Log::warning("Subscription {$subscription->id} has invalid increment days ({$incrementDays}); skipping");

                        continue;
                    }

                    // If the subscription is due today and auto-renewal is enabled, create an expense once
                    if ($subscription->auto_renewal && $originalDate->isSameDay($today)) {
                        $this->createAutopayExpenseForSubscription($subscription, $originalDate);
                    }

                    $next = $originalDate->copy();

                    // If it's due or overdue, keep advancing until it's in the future
                    while ($next->lte($today)) {
                        $next->addDays($incrementDays);
                    }

                    // Only save if changed
                    if ($subscription->next_billing_date?->ne($next)) {
                        $subscription->next_billing_date = $next->toDateString();
                        $subscription->save();
                        $count++;
                        Log::info("Updated subscription {$subscription->id} next_billing_date from {$originalDate->toDateString()} to {$next->toDateString()}");
                    }
                } catch (\Throwable $e) {
                    Log::error("Failed updating subscription {$subscription->id}: {$e->getMessage()}");
                }
            }
        });

        Log::info("Completed UpdateSubscriptionNextBillingDates job. Updated {$count} subscriptions.");
    }

    private function determineIncrementDays(Subscription $subscription): int
    {
        $cycle = strtolower((string) $subscription->billing_cycle);

        return match ($cycle) {
            'monthly' => 30,
            'quarterly' => 90,
            'yearly' => 365,
            'weekly' => 7,
            'custom' => max(0, (int) ($subscription->billing_cycle_days ?? 0)),
            default => max(0, (int) ($subscription->billing_cycle_days ?? 0)), // fallback to custom days if provided
        };
    }

    private function createAutopayExpenseForSubscription(Subscription $subscription, Carbon $billingDate): void
    {
        try {
            $defaults = [
                'user_id' => $subscription->user_id,
                'expense_date' => $billingDate->toDateString(),
                'amount' => $subscription->cost,
                'currency' => $subscription->currency ?? config('currency.default', 'MKD'),
                'category' => $subscription->category ?: 'Subscriptions',
                'subcategory' => null,
                'description' => 'Auto-pay for subscription: '.$subscription->service_name,
                'merchant' => $subscription->merchant_info ?: $subscription->service_name,
                'payment_method' => $subscription->payment_method,
                'tags' => ['autopay', 'subscription'],
                'location' => null,
                'is_tax_deductible' => false,
                'expense_type' => 'personal',
                'is_recurring' => true,
                'recurring_schedule' => $subscription->billing_cycle,
                'budget_allocated' => null,
                'notes' => null,
                'status' => 'paid',
            ];

            // Idempotency: do not create duplicates for same user+date+amount+merchant+description
            Expense::firstOrCreate(
                [
                    'user_id' => $subscription->user_id,
                    'expense_date' => $billingDate->toDateString(),
                    'amount' => $subscription->cost,
                    'merchant' => $subscription->merchant_info ?: $subscription->service_name,
                    'description' => 'Auto-pay for subscription: '.$subscription->service_name,
                ],
                $defaults
            );

            Log::info("Created auto-pay expense for subscription {$subscription->id} on {$billingDate->toDateString()}");
        } catch (\Throwable $e) {
            Log::error("Failed creating auto-pay expense for subscription {$subscription->id}: {$e->getMessage()}");
        }
    }
}
