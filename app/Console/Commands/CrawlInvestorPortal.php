<?php

namespace App\Console\Commands;

use App\Jobs\FetchInvestorData;
use App\Models\BrowserlessConnection;
use App\Models\User;
use Illuminate\Console\Command;

class CrawlInvestorPortal extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'investor:crawl
                          {--user-id= : The ID of the user to crawl for}
                          {--sync : Run synchronously instead of dispatching a job}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crawl the investor portal for fund data';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $userId = $this->option('user-id');

        if (! $userId) {
            // Get the first user if no user ID specified
            $user = User::first();

            if (! $user) {
                $this->error('No users found in the database.');

                return self::FAILURE;
            }

            $userId = $user->id;
            $this->info("No user ID specified, using first user: {$user->name} (ID: {$userId})");
        }

        $user = User::find($userId);

        if (! $user) {
            $this->error("User with ID {$userId} not found.");

            return self::FAILURE;
        }

        // Get or create connection
        $connection = $user->browserlessConnections()->first();

        if (! $connection) {
            $this->info('Creating new browserless connection...');
            $connection = BrowserlessConnection::create([
                'user_id' => $user->id,
                'portal_name' => 'investor.wvpfondovi.mk',
                'sync_enabled' => true,
            ]);
        }

        if (! $connection->isActive()) {
            $this->error('Connection is not active. Please enable it first.');

            return self::FAILURE;
        }

        if ($this->option('sync')) {
            $this->info('Running crawl synchronously...');
            $this->line('');

            try {
                $service = app(\App\Services\BrowserlessService::class);
                $data = $service->crawlInvestorPortal();

                $investorData = \App\Models\InvestorData::create([
                    'user_id' => $connection->user_id,
                    'browserless_connection_id' => $connection->id,
                    'portal_name' => $connection->portal_name,
                    'raw_data' => $data,
                    'tables' => $data['tables'] ?? [],
                    'funds' => $data['funds'] ?? [],
                    'screenshot' => $data['screenshot'] ?? null,
                    'crawled_at' => now(),
                ]);

                $connection->markSyncSuccessful();

                $this->info('Crawl completed successfully!');
                $this->line('');
                $this->table(
                    ['Metric', 'Value'],
                    [
                        ['Tables found', count($investorData->tables ?? [])],
                        ['Funds found', count($investorData->funds ?? [])],
                        ['Data ID', $investorData->id],
                    ]
                );

                return self::SUCCESS;
            } catch (\Exception $e) {
                $connection->markSyncFailed($e->getMessage());
                $this->error('Crawl failed: '.$e->getMessage());

                return self::FAILURE;
            }
        } else {
            $this->info('Dispatching crawl job...');
            FetchInvestorData::dispatch($connection);
            $this->info('Job dispatched successfully! Check the queue worker for progress.');

            return self::SUCCESS;
        }
    }
}
