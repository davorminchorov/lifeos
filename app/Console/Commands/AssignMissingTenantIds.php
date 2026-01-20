<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AssignMissingTenantIds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenants:assign-missing
                            {--dry-run : Show what would be updated without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Assign tenant_id to records that still have NULL tenant_id';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $isDryRun = $this->option('dry-run');

        if ($isDryRun) {
            $this->info('Running in DRY RUN mode - no changes will be made');
        }

        // Tables with direct user_id column
        $tablesWithUserId = [
            'budgets',
            'subscriptions',
            'contracts',
            'warranties',
            'investments',
            'investment_goals',
            'expenses',
            'utility_bills',
            'ious',
            'job_applications',
            'job_application_status_histories',
            'job_application_interviews',
            'job_application_offers',
            'cycle_menus',
            'project_investments',
            'project_investment_transactions',
            'gmail_connections',
            'processed_emails',
            'customers',
            'invoices',
            'tax_rates',
            'discounts',
            'payments',
            'credit_notes',
            'sequences',
            'recurring_invoices',
            'recurring_invoice_items',
            'invoice_reminders',
        ];

        $totalUpdated = 0;

        foreach ($tablesWithUserId as $table) {
            $count = DB::table($table)->whereNull('tenant_id')->count();

            if ($count > 0) {
                $this->info("Found {$count} records in {$table} with NULL tenant_id");

                if (!$isDryRun) {
                    // Get users with their current tenant
                    $updated = DB::table($table)
                        ->whereNull('tenant_id')
                        ->whereNotNull('user_id')
                        ->update([
                            'tenant_id' => DB::raw('(SELECT current_tenant_id FROM users WHERE users.id = ' . $table . '.user_id LIMIT 1)')
                        ]);

                    $this->info("Updated {$updated} records in {$table}");
                    $totalUpdated += $updated;
                }
            }
        }

        // Handle investment_transactions separately - related through investment_id
        $count = DB::table('investment_transactions')->whereNull('tenant_id')->count();
        if ($count > 0) {
            $this->info("Found {$count} records in investment_transactions with NULL tenant_id");

            if (!$isDryRun) {
                $updated = DB::table('investment_transactions')
                    ->whereNull('tenant_id')
                    ->update([
                        'tenant_id' => DB::raw('(SELECT tenant_id FROM investments WHERE investments.id = investment_transactions.investment_id LIMIT 1)')
                    ]);

                $this->info("Updated {$updated} records in investment_transactions");
                $totalUpdated += $updated;
            }
        }

        // Handle investment_dividends separately - related through investment_id
        $count = DB::table('investment_dividends')->whereNull('tenant_id')->count();
        if ($count > 0) {
            $this->info("Found {$count} records in investment_dividends with NULL tenant_id");

            if (!$isDryRun) {
                $updated = DB::table('investment_dividends')
                    ->whereNull('tenant_id')
                    ->update([
                        'tenant_id' => DB::raw('(SELECT tenant_id FROM investments WHERE investments.id = investment_dividends.investment_id LIMIT 1)')
                    ]);

                $this->info("Updated {$updated} records in investment_dividends");
                $totalUpdated += $updated;
            }
        }

        // Handle cycle_menu_days - related through cycle_menu_id
        $count = DB::table('cycle_menu_days')->whereNull('tenant_id')->count();
        if ($count > 0) {
            $this->info("Found {$count} records in cycle_menu_days with NULL tenant_id");

            if (!$isDryRun) {
                $updated = DB::table('cycle_menu_days')
                    ->whereNull('tenant_id')
                    ->update([
                        'tenant_id' => DB::raw('(SELECT tenant_id FROM cycle_menus WHERE cycle_menus.id = cycle_menu_days.cycle_menu_id LIMIT 1)')
                    ]);

                $this->info("Updated {$updated} records in cycle_menu_days");
                $totalUpdated += $updated;
            }
        }

        // Handle cycle_menu_items - related through cycle_menu_day_id
        $count = DB::table('cycle_menu_items')->whereNull('tenant_id')->count();
        if ($count > 0) {
            $this->info("Found {$count} records in cycle_menu_items with NULL tenant_id");

            if (!$isDryRun) {
                $updated = DB::table('cycle_menu_items')
                    ->whereNull('tenant_id')
                    ->update([
                        'tenant_id' => DB::raw('(SELECT tenant_id FROM cycle_menu_days WHERE cycle_menu_days.id = cycle_menu_items.cycle_menu_day_id LIMIT 1)')
                    ]);

                $this->info("Updated {$updated} records in cycle_menu_items");
                $totalUpdated += $updated;
            }
        }

        // Handle invoice_items - related through invoice_id
        $count = DB::table('invoice_items')->whereNull('tenant_id')->count();
        if ($count > 0) {
            $this->info("Found {$count} records in invoice_items with NULL tenant_id");

            if (!$isDryRun) {
                $updated = DB::table('invoice_items')
                    ->whereNull('tenant_id')
                    ->update([
                        'tenant_id' => DB::raw('(SELECT tenant_id FROM invoices WHERE invoices.id = invoice_items.invoice_id LIMIT 1)')
                    ]);

                $this->info("Updated {$updated} records in invoice_items");
                $totalUpdated += $updated;
            }
        }

        // Handle credit_note_applications - related through credit_note_id
        $count = DB::table('credit_note_applications')->whereNull('tenant_id')->count();
        if ($count > 0) {
            $this->info("Found {$count} records in credit_note_applications with NULL tenant_id");

            if (!$isDryRun) {
                $updated = DB::table('credit_note_applications')
                    ->whereNull('tenant_id')
                    ->update([
                        'tenant_id' => DB::raw('(SELECT tenant_id FROM credit_notes WHERE credit_notes.id = credit_note_applications.credit_note_id LIMIT 1)')
                    ]);

                $this->info("Updated {$updated} records in credit_note_applications");
                $totalUpdated += $updated;
            }
        }

        // Handle refunds - related through payment_id
        $count = DB::table('refunds')->whereNull('tenant_id')->count();
        if ($count > 0) {
            $this->info("Found {$count} records in refunds with NULL tenant_id");

            if (!$isDryRun) {
                $updated = DB::table('refunds')
                    ->whereNull('tenant_id')
                    ->update([
                        'tenant_id' => DB::raw('(SELECT tenant_id FROM payments WHERE payments.id = refunds.payment_id LIMIT 1)')
                    ]);

                $this->info("Updated {$updated} records in refunds");
                $totalUpdated += $updated;
            }
        }

        if ($isDryRun) {
            $this->info("\nDry run completed. Run without --dry-run to apply changes.");
        } else {
            $this->info("\nTotal records updated: {$totalUpdated}");
            $this->info('All NULL tenant_ids have been assigned!');
        }

        return Command::SUCCESS;
    }
}
