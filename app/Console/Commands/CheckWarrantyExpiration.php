<?php

namespace App\Console\Commands;

use App\Jobs\SendWarrantyExpirationNotifications;
use Illuminate\Console\Command;

class CheckWarrantyExpiration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'warranties:check-expiration
                            {--days=* : Specific days to check (e.g., --days=30 --days=7)}
                            {--dispatch-job : Dispatch the job to queue instead of running immediately}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for warranty expirations and send notifications';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔍 Checking warranty expirations...');

        $customDays = $this->option('days');
        $notificationDays = empty($customDays) ? [30, 14, 7, 1, 0] : array_map('intval', $customDays);

        if ($this->option('dispatch-job')) {
            // Dispatch to queue for background processing
            SendWarrantyExpirationNotifications::dispatch($notificationDays);
            $this->info('📤 Warranty expiration notification job dispatched to queue');
        } else {
            // Run immediately
            $job = new SendWarrantyExpirationNotifications($notificationDays);
            $job->handle();
            $this->info('✅ Warranty expiration notifications processed');
        }

        $this->newLine();
        $this->info('📊 Summary:');
        $this->table(
            ['Days', 'Description'],
            collect($notificationDays)->map(fn ($days) => [
                $days,
                match ($days) {
                    0 => 'Expires today',
                    1 => 'Expires tomorrow',
                    default => "Expires in {$days} days"
                },
            ])
        );

        return Command::SUCCESS;
    }
}
