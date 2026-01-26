<?php

namespace App\Jobs;

use App\Events\ContractNotificationDue;
use App\Models\Contract;
use App\Scopes\TenantScope;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendContractExpirationNotifications implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 300;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

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
            $this->dispatchEventsForDay($days);
        }

        Log::info('Completed contract expiration notification job');
    }

    /**
     * Dispatch events for contracts expiring in specific days.
     */
    private function dispatchEventsForDay(int $days): void
    {
        $targetDate = now()->addDays($days)->toDateString();
        $today = now()->toDateString();

        // Get contracts expiring on target date (or today/overdue when days === 0)
        // Note: withoutGlobalScope is needed because this job runs in queue context without auth
        $baseQuery = Contract::withoutGlobalScope(TenantScope::class)
            ->with('user')
            ->where('status', 'active');

        if ($days === 0) {
            $baseQuery->whereDate('end_date', '<=', $today);
        } else {
            $baseQuery->whereDate('end_date', $targetDate);
        }

        $contracts = $baseQuery->get();

        // Also get contracts that need notice period alerts
        if ($days > 0) {
            $noticeContracts = Contract::withoutGlobalScope(TenantScope::class)
                ->with('user')
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
                $isNoticeAlert = (bool) ($contract->notice_period_days &&
                    $contract->end_date->subDays($contract->notice_period_days)->toDateString() === $targetDate);

                event(new ContractNotificationDue($contract, $days, $isNoticeAlert));
            } catch (\Exception $e) {
                Log::error("Failed to dispatch ContractNotificationDue for contract {$contract->id}: {$e->getMessage()}");
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
