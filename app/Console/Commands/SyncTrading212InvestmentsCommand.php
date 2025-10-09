<?php

namespace App\Console\Commands;

use App\Jobs\SyncTrading212OrdersJob;
use App\Services\Trading212Service;
use Illuminate\Console\Command;

class SyncTrading212InvestmentsCommand extends Command
{
    protected $signature = 'investments:sync-trading212 {--dispatch-job : Dispatch as queued job}';

    protected $description = 'Sync newly bought or sold shares from Trading212 and create investment records.';

    public function handle(Trading212Service $t212): int
    {
        $since = $t212->getLastSyncOrDefault(now());
        $this->info('Syncing Trading212 orders since: '.$since->toIso8601String());

        if ($this->option('dispatch-job')) {
            SyncTrading212OrdersJob::dispatch();
            $this->info('Dispatched SyncTrading212OrdersJob');

            return self::SUCCESS;
        }

        // Run inline
        (new SyncTrading212OrdersJob)->handle($t212);
        $this->info('Sync completed.');

        return self::SUCCESS;
    }
}
