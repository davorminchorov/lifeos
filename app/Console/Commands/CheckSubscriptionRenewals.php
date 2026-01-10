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
                            {--days=* : Specific days to check (e.g., --days=7 --days=1). If omitted, uses each user\'s preferences}
                            {--dispatch-job : Dispatch the job to queue instead of running immediately}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for subscription renewals and send notifications based on user preferences';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔍 Checking subscription renewals...');

        $customDays = $this->option('days');

        // If no custom days specified, use null to trigger user-centric mode
        // Otherwise, use legacy mode with specified days
        $notificationDays = empty($customDays) ? null : array_map('intval', $customDays);

        if ($notificationDays === null) {
            $this->info('📋 Mode: User-centric (respecting individual user notification preferences)');
        } else {
            $this->info('📋 Mode: Legacy (using system-wide notification days: '.implode(', ', $notificationDays).')');
        }

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

        if ($notificationDays !== null) {
            $this->info('📊 Summary:');
            $this->table(
                ['Days', 'Description'],
                collect($notificationDays)->map(fn ($days) => [
                    $days,
                    match ($days) {
                        0 => 'Due today',
                        1 => 'Due tomorrow',
                        default => "Due in {$days} days"
                    },
                ])
            );
        } else {
            $this->info('ℹ️  Each user will be notified according to their individual preferences');
            $this->info('💡 Tip: Check logs for detailed processing information');
        }

        return Command::SUCCESS;
    }
}
