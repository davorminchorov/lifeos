<?php

namespace App\Console\Commands;

use App\Services\InvoiceReminderService;
use Illuminate\Console\Command;

class SendPaymentReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoicing:send-reminders
                            {--dry-run : Preview reminders without sending}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send payment reminders for overdue invoices';

    /**
     * Execute the console command.
     */
    public function handle(InvoiceReminderService $reminderService)
    {
        $this->info('Starting payment reminder processing...');

        if ($this->option('dry-run')) {
            $this->warn('DRY RUN MODE - No reminders will be sent');
        }

        try {
            if ($this->option('dry-run')) {
                // Just show what would be processed
                $invoices = $reminderService->getInvoicesNeedingReminders();
                $this->info("Would send reminders for {$invoices->count()} invoice(s)");

                if ($invoices->count() > 0) {
                    $this->table(
                        ['Invoice #', 'Customer', 'Amount Due', 'Days Overdue'],
                        $invoices->map(function ($invoice) {
                            return [
                                $invoice->number,
                                $invoice->customer->name,
                                $invoice->currency . ' ' . number_format($invoice->amount_due / 100, 2),
                                now()->diffInDays($invoice->due_at, false),
                            ];
                        })
                    );
                }

                return self::SUCCESS;
            }

            // Process all reminders
            $results = $reminderService->processAllReminders();

            $this->info("Processed: {$results['processed']}");
            $this->info("Sent: {$results['sent']}");

            if ($results['failed'] > 0) {
                $this->error("Failed: {$results['failed']}");

                foreach ($results['errors'] as $error) {
                    $this->error("  - Invoice #{$error['invoice_number']}: {$error['error']}");
                }
            }

            $this->info('Payment reminder processing completed!');

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Failed to process payment reminders: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
