<?php

namespace App\Console\Commands;

use App\Jobs\SendUtilityBillDueNotifications;
use Illuminate\Console\Command;

class CheckUtilityBillDue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'utility-bills:check-due
                            {--days=* : Specific days to check (e.g., --days=7 --days=3)}
                            {--dispatch-job : Dispatch the job to queue instead of running immediately}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for utility bills due and send payment reminders';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔍 Checking utility bills due...');

        $customDays = $this->option('days');
        $notificationDays = empty($customDays) ? [14, 7, 3, 1, 0] : array_map('intval', $customDays);

        if ($this->option('dispatch-job')) {
            // Dispatch to queue for background processing
            SendUtilityBillDueNotifications::dispatch($notificationDays);
            $this->info('📤 Utility bill payment notification job dispatched to queue');
        } else {
            // Run immediately
            $job = new SendUtilityBillDueNotifications($notificationDays);
            $job->handle();
            $this->info('✅ Utility bill payment notifications processed');
        }

        $this->newLine();
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

        return Command::SUCCESS;
    }
}
