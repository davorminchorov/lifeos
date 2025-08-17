<?php

namespace App\Jobs;

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
                $bill->user->notify(
                    new UtilityBillDueAlert($bill, $days)
                );

                Log::info("Sent payment reminder for utility bill {$bill->id} ({$bill->utility_type} - {$bill->service_provider}) to user {$bill->user->email}");
            } catch (\Exception $e) {
                Log::error("Failed to send payment reminder for utility bill {$bill->id}: {$e->getMessage()}");
            }
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
