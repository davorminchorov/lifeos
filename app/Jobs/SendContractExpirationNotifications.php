<?php

namespace App\Jobs;

use App\Models\Contract;
use App\Notifications\ContractExpirationAlert;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendContractExpirationNotifications implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private array $notificationDays = [60, 30, 14, 7, 1, 0]
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Starting contract expiration notification job');

        foreach ($this->notificationDays as $days) {
            $this->sendNotificationsForDay($days);
        }

        Log::info('Completed contract expiration notification job');
    }

    /**
     * Send notifications for contracts expiring in specific days.
     */
    private function sendNotificationsForDay(int $days): void
    {
        $targetDate = now()->addDays($days)->toDateString();

        // Get contracts expiring on target date
        $contracts = Contract::with('user')
            ->where('status', 'active')
            ->whereDate('end_date', $targetDate)
            ->get();

        // Also get contracts that need notice period alerts
        if ($days > 0) {
            $noticeContracts = Contract::with('user')
                ->where('status', 'active')
                ->whereNotNull('notice_period_days')
                ->whereNotNull('end_date')
                ->get()
                ->filter(function ($contract) use ($days) {
                    // Check if this is the notice deadline
                    $noticeDays = $contract->notice_period_days;
                    $noticeDate = $contract->end_date->subDays($noticeDays)->toDateString();

                    return $noticeDate === now()->addDays($days)->toDateString();
                });

            $contracts = $contracts->merge($noticeContracts);
        }

        Log::info("Found {$contracts->count()} contracts requiring notifications in {$days} days");

        foreach ($contracts as $contract) {
            try {
                // Determine notification type
                $isNoticeAlert = $contract->notice_period_days &&
                    $contract->end_date->subDays($contract->notice_period_days)->toDateString() === $targetDate;

                $contract->user->notify(
                    new ContractExpirationAlert($contract, $days, $isNoticeAlert)
                );

                $alertType = $isNoticeAlert ? 'notice period' : 'expiration';
                Log::info("Sent {$alertType} notification for contract {$contract->id} ({$contract->title}) to user {$contract->user->email}");
            } catch (\Exception $e) {
                Log::error("Failed to send notification for contract {$contract->id}: {$e->getMessage()}");
            }
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Contract expiration notification job failed: '.$exception->getMessage());
    }
}
