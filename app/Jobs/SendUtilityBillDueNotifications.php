<?php

namespace App\Jobs;

use App\Models\Expense;
use App\Models\UtilityBill;
use App\Notifications\UtilityBillDueAlert;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendUtilityBillDueNotifications implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private array $notificationDays = [14, 7, 3, 1, 0]
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Starting utility bill due notification job');

        foreach ($this->notificationDays as $days) {
            $this->sendNotificationsForDay($days);
        }

        Log::info('Completed utility bill due notification job');
    }

    /**
     * Send notifications for utility bills due in specific days.
     */
    private function sendNotificationsForDay(int $days): void
    {
        $targetDate = now()->addDays($days)->toDateString();

        $utilityBills = UtilityBill::with('user')
            ->where('payment_status', 'pending')
            ->whereDate('due_date', $targetDate)
            ->get();

        Log::info("Found {$utilityBills->count()} utility bills due in {$days} days");

        foreach ($utilityBills as $bill) {
            try {
                // Check if user has any enabled channels for this notification type
                $enabledChannels = $bill->user->getEnabledNotificationChannels('utility_bill_due');

                if (empty($enabledChannels)) {
                    Log::info("Skipping notification for utility bill {$bill->id} - user has disabled all channels");

                    continue;
                }

                $bill->user->notify(
                    new UtilityBillDueAlert($bill, $days)
                );

                Log::info("Sent payment reminder for utility bill {$bill->id} ({$bill->utility_type} - {$bill->service_provider}) to user {$bill->user->email} via channels: ".implode(', ', $enabledChannels));

                // If due today and auto-pay is enabled, create an expense (idempotent)
                if ($days === 0 && $bill->auto_pay_enabled) {
                    $this->createAutopayExpenseForUtility($bill);
                }
            } catch (\Exception $e) {
                Log::error("Failed to send payment reminder for utility bill {$bill->id}: {$e->getMessage()}");
            }
        }
    }

    private function createAutopayExpenseForUtility(UtilityBill $bill): void
    {
        try {
            $defaults = [
                'user_id' => $bill->user_id,
                'expense_date' => $bill->due_date?->toDateString() ?? now()->toDateString(),
                'amount' => $bill->bill_amount,
                'currency' => $bill->currency ?? config('currency.default', 'MKD'),
                'category' => 'Utilities',
                'subcategory' => $bill->utility_type,
                'description' => 'Auto-pay for utility bill: '.$bill->utility_type,
                'merchant' => $bill->service_provider,
                'payment_method' => null,
                'tags' => ['autopay', 'utility'],
                'location' => null,
                'is_tax_deductible' => false,
                'expense_type' => 'personal',
                'is_recurring' => false,
                'recurring_schedule' => null,
                'budget_allocated' => null,
                'notes' => null,
                'status' => 'paid',
            ];

            Expense::firstOrCreate(
                [
                    'user_id' => $bill->user_id,
                    'expense_date' => $defaults['expense_date'],
                    'amount' => $bill->bill_amount,
                    'merchant' => $bill->service_provider,
                    'description' => $defaults['description'],
                ],
                $defaults
            );

            Log::info("Created auto-pay expense for utility bill {$bill->id} on {$defaults['expense_date']}");
        } catch (\Throwable $e) {
            Log::error("Failed creating auto-pay expense for utility bill {$bill->id}: {$e->getMessage()}");
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Utility bill due notification job failed: '.$exception->getMessage());
    }
}
