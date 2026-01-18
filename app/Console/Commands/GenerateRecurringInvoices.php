<?php

namespace App\Console\Commands;

use App\Services\RecurringInvoiceService;
use Illuminate\Console\Command;

class GenerateRecurringInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoicing:generate-recurring
                            {--dry-run : Simulate the process without actually generating invoices}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate invoices from active recurring invoice templates';

    /**
     * Execute the console command.
     */
    public function handle(RecurringInvoiceService $recurringService)
    {
        $this->info('Starting recurring invoice generation...');

        if ($this->option('dry-run')) {
            $this->warn('DRY RUN MODE - No invoices will be generated');
        }

        try {
            if ($this->option('dry-run')) {
                // Just show what would be processed
                $count = \App\Models\RecurringInvoice::dueForGeneration()->count();
                $this->info("Would process {$count} recurring invoice(s)");
                return self::SUCCESS;
            }

            // Process all due recurring invoices
            $results = $recurringService->processAllDue();

            $this->info("Processed: {$results['processed']}");
            $this->info("Generated: {$results['generated']}");

            if ($results['failed'] > 0) {
                $this->error("Failed: {$results['failed']}");

                foreach ($results['errors'] as $error) {
                    $this->error("  - Recurring Invoice #{$error['recurring_invoice_id']}: {$error['error']}");
                }
            }

            $this->info('Recurring invoice generation completed!');

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Failed to generate recurring invoices: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
