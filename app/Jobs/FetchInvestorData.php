<?php

namespace App\Jobs;

use App\Models\BrowserlessConnection;
use App\Models\InvestorData;
use App\Services\BrowserlessService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class FetchInvestorData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public $backoff = 60;

    /**
     * The browserless connection to process.
     *
     * @var BrowserlessConnection
     */
    protected BrowserlessConnection $connection;

    /**
     * Create a new job instance.
     */
    public function __construct(BrowserlessConnection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Execute the job.
     */
    public function handle(BrowserlessService $browserlessService): void
    {
        Log::info('Starting FetchInvestorData job', [
            'connection_id' => $this->connection->id,
            'user_id' => $this->connection->user_id,
            'portal' => $this->connection->portal_name,
        ]);

        try {
            // Check if connection is still active
            if (! $this->connection->isActive()) {
                Log::warning('Browserless connection is not active', [
                    'connection_id' => $this->connection->id,
                ]);

                return;
            }

            // Test browserless connection first
            if (! $browserlessService->testConnection()) {
                throw new Exception('Failed to connect to Browserless API');
            }

            // Crawl the investor portal
            Log::info('Starting investor portal crawl', [
                'connection_id' => $this->connection->id,
            ]);

            $crawlData = $browserlessService->crawlInvestorPortal();

            if (empty($crawlData) || !isset($crawlData['success']) || $crawlData['success'] !== true) {
                $error = $crawlData['error'] ?? 'Unknown error during crawl';
                throw new Exception("Crawl failed: {$error}");
            }

            // Store the crawled data
            $investorData = InvestorData::create([
                'user_id' => $this->connection->user_id,
                'browserless_connection_id' => $this->connection->id,
                'portal_name' => $this->connection->portal_name,
                'raw_data' => $crawlData,
                'tables' => $crawlData['tables'] ?? [],
                'funds' => $crawlData['funds'] ?? [],
                'screenshot' => $crawlData['screenshot'] ?? null,
                'crawled_at' => now(),
            ]);

            Log::info('Investor data stored successfully', [
                'connection_id' => $this->connection->id,
                'investor_data_id' => $investorData->id,
                'tables_count' => count($investorData->tables ?? []),
                'funds_count' => count($investorData->funds ?? []),
            ]);

            // Mark sync as successful
            $this->connection->markSyncSuccessful();

            Log::info('Completed FetchInvestorData job', [
                'connection_id' => $this->connection->id,
                'investor_data_id' => $investorData->id,
            ]);
        } catch (Exception $e) {
            Log::error('FetchInvestorData job failed', [
                'connection_id' => $this->connection->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Re-throw to trigger retry logic
            // Note: markSyncFailed() is called in failed() method after all retries are exhausted
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(Exception $exception): void
    {
        Log::error('FetchInvestorData job failed permanently', [
            'connection_id' => $this->connection->id,
            'error' => $exception->getMessage(),
        ]);

        // Mark sync as failed
        $this->connection->markSyncFailed($exception->getMessage());

        // Optionally notify the user
        // $this->connection->user->notify(new InvestorSyncFailed($exception));
    }
}
