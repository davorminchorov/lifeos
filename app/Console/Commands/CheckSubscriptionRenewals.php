<?php

namespace App\Console\Commands;

use App\Jobs\SendSubscriptionRenewalNotifications;
use Illuminate\Console\Command;

class CheckSubscriptionRenewals extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:check-renewals
                            {--days=* : Specific days to check (e.g., --days=7 --days=1)}
                            {--dispatch-job : Dispatch the job to queue instead of running immediately}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for subscription renewals and send notifications';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔍 Checking subscription renewals...');

        $customDays = $this->option('days');
        $notificationDays = empty($customDays) ? [7, 3, 1, 0] : array_map('intval', $customDays);

        if ($this->option('dispatch-job')) {
            // Dispatch to queue for background processing
            SendSubscriptionRenewalNotifications::dispatch($notificationDays);
            $this->info('📤 Subscription renewal notification job dispatched to queue');
        } else {
            // Run immediately
            $job = new SendSubscriptionRenewalNotifications($notificationDays);
            $job->handle();
            $this->info('✅ Subscription renewal notifications processed');
        }

        $this->newLine();
        $this->info('📊 Summary:');
        $this->table(
            ['Days', 'Description'],
            collect($notificationDays)->map(fn($days) => [
                $days,
                match($days) {
                    0 => 'Due today',
                    1 => 'Due tomorrow',
                    default => "Due in {$days} days"
                }
            ])
        );

        return Command::SUCCESS;
    }
}
