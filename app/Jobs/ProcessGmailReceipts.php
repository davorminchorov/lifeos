<?php

namespace App\Jobs;

use App\Models\GmailConnection;
use App\Models\ProcessedEmail;
use App\Scopes\TenantScope;
use App\Services\GmailService;
use Carbon\Carbon;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessGmailReceipts implements ShouldQueue
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
     * The Gmail connection to process.
     *
     * @var GmailConnection
     */
    protected GmailConnection $gmailConnection;

    /**
     * Whether this is the initial sync.
     *
     * @var bool
     */
    protected bool $isInitialSync;

    /**
     * Create a new job instance.
     */
    public function __construct(GmailConnection $gmailConnection, bool $isInitialSync = false)
    {
        $this->gmailConnection = $gmailConnection;
        $this->isInitialSync = $isInitialSync;
    }

    /**
     * Execute the job.
     */
    public function handle(GmailService $gmailService): void
    {
        Log::info('Starting ProcessGmailReceipts job', [
            'connection_id' => $this->gmailConnection->id,
            'user_id' => $this->gmailConnection->user_id,
            'email' => $this->gmailConnection->email_address,
        ]);

        try {
            // Check if connection is still active
            if (! $this->gmailConnection->isActive()) {
                Log::warning('Gmail connection is not active', [
                    'connection_id' => $this->gmailConnection->id,
                ]);

                return;
            }

            // Determine sync period
            $since = $this->determineSyncPeriod();

            // Get max emails per sync from config
            $maxEmails = config('gmail_receipts.sync.max_emails_per_sync', 100);

            // Fetch receipt emails
            Log::info('Fetching receipt emails', [
                'connection_id' => $this->gmailConnection->id,
                'since' => $since?->toDateTimeString(),
                'max_emails' => $maxEmails,
            ]);

            $emails = $gmailService->fetchReceiptEmails($this->gmailConnection, $since, $maxEmails);

            Log::info('Fetched receipt emails', [
                'connection_id' => $this->gmailConnection->id,
                'count' => count($emails),
            ]);

            $processed = 0;
            $skipped = 0;

            foreach ($emails as $emailData) {
                try {
                    // Check if email was already processed
                    // Note: withoutGlobalScope is needed because this job runs in queue context without auth
                    $existingEmail = ProcessedEmail::withoutGlobalScope(TenantScope::class)
                        ->where('gmail_message_id', $emailData['id'])
                        ->first();

                    if ($existingEmail) {
                        $skipped++;
                        continue;
                    }

                    // Create processed email record
                    $processedEmail = ProcessedEmail::create([
                        'user_id' => $this->gmailConnection->user_id,
                        'gmail_message_id' => $emailData['id'],
                        'processing_status' => ProcessedEmail::STATUS_PENDING,
                        'email_data' => $emailData,
                    ]);

                    // Dispatch job to parse and create expense
                    ParseReceiptAndCreateExpense::dispatch($processedEmail, $this->gmailConnection);

                    $processed++;
                } catch (Exception $e) {
                    Log::error('Failed to queue email for processing', [
                        'connection_id' => $this->gmailConnection->id,
                        'message_id' => $emailData['id'] ?? 'unknown',
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // Update last synced timestamp
            $this->gmailConnection->update([
                'last_synced_at' => Carbon::now(),
            ]);

            Log::info('Completed ProcessGmailReceipts job', [
                'connection_id' => $this->gmailConnection->id,
                'processed' => $processed,
                'skipped' => $skipped,
            ]);
        } catch (Exception $e) {
            Log::error('ProcessGmailReceipts job failed', [
                'connection_id' => $this->gmailConnection->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Re-throw to trigger retry logic
            throw $e;
        }
    }

    /**
     * Determine the sync period based on last sync time.
     */
    protected function determineSyncPeriod(): ?Carbon
    {
        // If initial sync, use configured initial sync days
        if ($this->isInitialSync || $this->gmailConnection->last_synced_at === null) {
            $days = config('gmail_receipts.sync.initial_sync_days', 30);

            return Carbon::now()->subDays($days);
        }

        // Otherwise, sync from last sync time
        return $this->gmailConnection->last_synced_at;
    }

    /**
     * Handle a job failure.
     */
    public function failed(Exception $exception): void
    {
        Log::error('ProcessGmailReceipts job failed permanently', [
            'connection_id' => $this->gmailConnection->id,
            'error' => $exception->getMessage(),
        ]);

        // Optionally notify the user
        // $this->gmailConnection->user->notify(new GmailSyncFailed($exception));
    }
}
