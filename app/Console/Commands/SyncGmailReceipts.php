<?php

namespace App\Console\Commands;

use App\Jobs\ProcessGmailReceipts;
use App\Models\GmailConnection;
use App\Models\User;
use Illuminate\Console\Command;

class SyncGmailReceipts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gmail:sync-receipts
                            {user? : The user ID to sync receipts for}
                            {--all : Sync receipts for all users with active Gmail connections}
                            {--initial : Perform initial sync (fetch older emails)}
                            {--queue : Dispatch jobs to queue instead of running immediately}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync receipts from Gmail and create expenses';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $userId = $this->argument('user');
        $syncAll = $this->option('all');
        $isInitialSync = $this->option('initial');
        $useQueue = $this->option('queue');

        $this->info('📧 Starting Gmail receipt sync...');
        $this->newLine();

        $connections = $this->getConnections($userId, $syncAll);

        if ($connections->isEmpty()) {
            $this->warn('⚠️  No active Gmail connections found.');

            return Command::FAILURE;
        }

        $this->info("Found {$connections->count()} active Gmail connection(s)");
        $this->newLine();

        $synced = 0;
        $failed = 0;

        foreach ($connections as $connection) {
            try {
                $this->info("Syncing: {$connection->email_address} (User ID: {$connection->user_id})");

                if ($useQueue) {
                    ProcessGmailReceipts::dispatch($connection, $isInitialSync);
                    $this->line('  📤 Job dispatched to queue');
                } else {
                    $job = new ProcessGmailReceipts($connection, $isInitialSync);
                    $job->handle(app(\App\Services\GmailService::class));
                    $this->line('  ✅ Sync completed');
                }

                $synced++;
            } catch (\Exception $e) {
                $this->error("  ❌ Failed: {$e->getMessage()}");
                $failed++;
            }

            $this->newLine();
        }

        // Summary
        $this->info('Summary:');
        $this->line("  ✅ Synced: {$synced}");
        if ($failed > 0) {
            $this->line("  ❌ Failed: {$failed}");
        }

        return $failed > 0 ? Command::FAILURE : Command::SUCCESS;
    }

    /**
     * Get Gmail connections to sync.
     */
    protected function getConnections(?string $userId, bool $syncAll): \Illuminate\Database\Eloquent\Collection
    {
        if (! $userId && ! $syncAll) {
            $this->error('Please specify a user ID or use --all to sync all users');

            return new \Illuminate\Database\Eloquent\Collection;
        }

        $query = GmailConnection::query()
            ->with('user')
            ->where('sync_enabled', true);

        if ($userId) {
            $query->where('user_id', $userId);
        }

        return $query->get()->filter(function ($connection) {
            return $connection->isActive();
        });
    }
}
