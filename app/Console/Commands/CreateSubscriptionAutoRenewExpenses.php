<?php

namespace App\Console\Commands;

use App\Jobs\CreateSubscriptionAutoRenewExpenses;
use Illuminate\Console\Command;

class CreateSubscriptionAutoRenewExpenses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:create-expenses {--dispatch-job : Dispatch the job to queue instead of running immediately}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create expense records for subscriptions with auto-renewal due today';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🧾 Creating expenses for auto-renewed subscriptions due today...');

        if ($this->option('dispatch-job')) {
            CreateSubscriptionAutoRenewExpenses::dispatch();
            $this->info('📤 Job dispatched to queue');
        } else {
            (new CreateSubscriptionAutoRenewExpenses)->handle();
            $this->info('✅ Expenses created');
        }

        return Command::SUCCESS;
    }
}
