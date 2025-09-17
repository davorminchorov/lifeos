<?php

namespace App\Console\Commands;

use App\Jobs\UpdateSubscriptionNextBillingDates;
use Illuminate\Console\Command;

class UpdateSubscriptionNextBilling extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:update-next-billing {--dispatch-job : Dispatch the job to queue instead of running immediately}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update subscriptions next_billing_date to the next period when due or overdue';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔁 Updating subscriptions next billing dates...');

        if ($this->option('dispatch-job')) {
            UpdateSubscriptionNextBillingDates::dispatch();
            $this->info('📤 UpdateSubscriptionNextBillingDates job dispatched to queue');
        } else {
            (new UpdateSubscriptionNextBillingDates)->handle();
            $this->info('✅ Subscription next billing dates updated');
        }

        return Command::SUCCESS;
    }
}
