<?php

namespace App\Jobs;

use App\Events\ReceiptExpenseCreated;
use App\Models\Expense;
use App\Models\GmailConnection;
use App\Models\ProcessedEmail;
use App\Services\GmailService;
use App\Services\ReceiptParserService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ParseReceiptAndCreateExpense implements ShouldQueue
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
    public $backoff = 30;

    /**
     * The processed email to parse.
     *
     * @var ProcessedEmail
     */
    protected ProcessedEmail $processedEmail;

    /**
     * The Gmail connection for downloading attachments.
     *
     * @var GmailConnection
     */
    protected GmailConnection $connection;

    /**
     * Create a new job instance.
     */
    public function __construct(ProcessedEmail $processedEmail, GmailConnection $connection)
    {
        $this->processedEmail = $processedEmail;
        $this->connection = $connection;
    }

    /**
     * Execute the job.
     */
    public function handle(ReceiptParserService $parser, GmailService $gmailService): void
    {
        Log::info('Starting ParseReceiptAndCreateExpense job', [
            'processed_email_id' => $this->processedEmail->id,
            'gmail_message_id' => $this->processedEmail->gmail_message_id,
            'user_id' => $this->processedEmail->user_id,
        ]);

        try {
            $emailData = $this->processedEmail->email_data;

            // Parse receipt data
            $parsedData = $parser->parse($emailData);

            Log::info('Parsed receipt data', [
                'processed_email_id' => $this->processedEmail->id,
                'merchant' => $parsedData['merchant'] ?? null,
                'amount' => $parsedData['amount'] ?? null,
                'confidence' => $parsedData['confidence'] ?? null,
            ]);

            // Check if parsed data is valid
            if (! $parser->isValidExpense($parsedData)) {
                $this->processedEmail->markAsSkipped(
                    'Insufficient data to create expense. '.
                    'Amount: '.($parsedData['amount'] ?? 'none').', '.
                    'Confidence: '.($parsedData['confidence'] ?? 0)
                );

                Log::warning('Skipped email - insufficient data', [
                    'processed_email_id' => $this->processedEmail->id,
                    'parsed_data' => $parsedData,
                ]);

                return;
            }

            // Download attachments if any
            $attachmentPaths = $this->downloadAttachments($gmailService, $emailData);

            // Extract additional metadata
            $orderId = $parser->extractOrderId($emailData);
            $location = $parser->extractLocation($emailData);

            // Build expense data
            $expenseData = [
                'user_id' => $this->processedEmail->user_id,
                'unique_key' => 'gmail:'.$this->processedEmail->gmail_message_id,
                'amount' => $parsedData['amount'],
                'currency' => $parsedData['currency'],
                'category' => $parsedData['category'],
                'subcategory' => $parsedData['subcategory'],
                'expense_date' => $parsedData['expense_date'],
                'description' => $parsedData['description'],
                'merchant' => $parsedData['merchant'],
                'payment_method' => $parsedData['payment_method'],
                'receipt_attachments' => $attachmentPaths,
                'tags' => array_filter([
                    'gmail-receipt',
                    'auto-imported',
                    $orderId ? "order:{$orderId}" : null,
                ]),
                'location' => $location,
                'status' => config('gmail_receipts.default_expense_status', 'pending'),
                'notes' => $this->buildNotes($parsedData, $orderId),
            ];

            // Create expense (or get existing if unique_key already exists)
            $expense = Expense::firstOrCreate(
                ['unique_key' => $expenseData['unique_key']],
                $expenseData
            );

            if ($expense->wasRecentlyCreated) {
                // Mark processed email as successfully processed
                $this->processedEmail->markAsProcessed($expense->id);

                // Add Gmail label to mark as processed
                $gmailService->addLabel(
                    $this->connection,
                    $this->processedEmail->gmail_message_id,
                    'LifeOS/Processed'
                );

                // Fire event for notifications
                event(new ReceiptExpenseCreated($expense, $this->processedEmail));

                Log::info('Created expense from receipt', [
                    'processed_email_id' => $this->processedEmail->id,
                    'expense_id' => $expense->id,
                    'amount' => $expense->amount,
                    'merchant' => $expense->merchant,
                ]);
            } else {
                // Expense already exists (duplicate)
                $this->processedEmail->markAsSkipped('Expense already exists for this receipt');

                Log::info('Expense already exists', [
                    'processed_email_id' => $this->processedEmail->id,
                    'expense_id' => $expense->id,
                ]);
            }
        } catch (Exception $e) {
            Log::error('Failed to parse receipt and create expense', [
                'processed_email_id' => $this->processedEmail->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Re-throw to trigger job retry
            // Status will be marked as failed in failed() handler after all retries exhausted
            throw $e;
        }
    }

    /**
     * Download all attachments from the email.
     */
    protected function downloadAttachments(GmailService $gmailService, array $emailData): array
    {
        $attachmentPaths = [];

        if (empty($emailData['attachments'])) {
            return $attachmentPaths;
        }

        foreach ($emailData['attachments'] as $attachment) {
            try {
                $path = $gmailService->downloadAttachment(
                    $this->connection,
                    $emailData['id'],
                    $attachment['attachment_id'],
                    $attachment['filename']
                );

                if ($path) {
                    $attachmentPaths[] = $path;
                }
            } catch (Exception $e) {
                Log::warning('Failed to download attachment', [
                    'processed_email_id' => $this->processedEmail->id,
                    'attachment' => $attachment['filename'],
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $attachmentPaths;
    }

    /**
     * Build notes for the expense.
     */
    protected function buildNotes(array $parsedData, ?string $orderId): ?string
    {
        $notes = [];

        if (isset($parsedData['confidence']) && $parsedData['confidence'] < 0.8) {
            $notes[] = 'Automatically imported from Gmail (confidence: '.round($parsedData['confidence'] * 100).'%). Please verify details.';
        } else {
            $notes[] = 'Automatically imported from Gmail receipt.';
        }

        if ($orderId) {
            $notes[] = "Order ID: {$orderId}";
        }

        return implode("\n", $notes);
    }

    /**
     * Handle a job failure.
     */
    public function failed(Exception $exception): void
    {
        Log::error('ParseReceiptAndCreateExpense job failed permanently', [
            'processed_email_id' => $this->processedEmail->id,
            'error' => $exception->getMessage(),
        ]);

        // Mark as failed if not already marked
        if ($this->processedEmail->processing_status === ProcessedEmail::STATUS_PENDING) {
            $this->processedEmail->markAsFailed('Job failed after maximum retries: '.$exception->getMessage());
        }
    }
}
