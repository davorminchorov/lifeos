<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TenantStatistics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenants:statistics
                            {--tenant-id= : Show statistics for specific tenant ID only}
                            {--with-data : Include sample data records}
                            {--verbose : Show detailed information}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Display statistics for each tenant including record counts per table and tenant details';

    /**
     * Tables with tenant_id column.
     */
    protected array $tablesWithTenantId = [
        'budgets', 'subscriptions', 'contracts', 'warranties',
        'investments', 'investment_goals', 'investment_dividends', 'investment_transactions',
        'expenses', 'utility_bills', 'ious',
        'job_applications', 'job_application_status_histories', 'job_application_interviews', 'job_application_offers',
        'cycle_menus', 'cycle_menu_days', 'cycle_menu_items',
        'project_investments', 'project_investment_transactions',
        'gmail_connections', 'processed_emails',
        'customers', 'invoices', 'tax_rates', 'discounts', 'invoice_items',
        'payments', 'credit_notes', 'credit_note_applications', 'refunds',
        'sequences', 'recurring_invoices', 'recurring_invoice_items', 'invoice_reminders',
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('=== Tenant Statistics Report ===');
        $this->newLine();

        $tenantId = $this->option('tenant-id');

        if ($tenantId) {
            $tenants = Tenant::where('id', $tenantId)->get();
            if ($tenants->isEmpty()) {
                $this->error("Tenant with ID {$tenantId} not found!");
                return Command::FAILURE;
            }
        } else {
            $tenants = Tenant::with(['owner', 'members'])->get();
        }

        if ($tenants->isEmpty()) {
            $this->warn('No tenants found in the system.');
            return Command::SUCCESS;
        }

        $this->info("Total Tenants: " . $tenants->count());
        $this->newLine();

        foreach ($tenants as $tenant) {
            $this->displayTenantStatistics($tenant);
        }

        return Command::SUCCESS;
    }

    /**
     * Display statistics for a single tenant.
     */
    protected function displayTenantStatistics(Tenant $tenant): void
    {
        $this->info("┌─────────────────────────────────────────────────────────────────");
        $this->info("│ TENANT: {$tenant->name}");
        $this->info("└─────────────────────────────────────────────────────────────────");
        $this->newLine();

        // Tenant Details
        $this->displayTenantDetails($tenant);
        $this->newLine();

        // Table Statistics
        $this->displayTableStatistics($tenant);
        $this->newLine();

        // Sample Data (if requested)
        if ($this->option('with-data')) {
            $this->displaySampleData($tenant);
            $this->newLine();
        }

        $this->line(str_repeat('═', 70));
        $this->newLine();
    }

    /**
     * Display tenant details.
     */
    protected function displayTenantDetails(Tenant $tenant): void
    {
        $this->line("  <fg=cyan>Tenant ID:</>      {$tenant->id}");
        $this->line("  <fg=cyan>Name:</>           {$tenant->name}");
        $this->line("  <fg=cyan>Slug:</>           {$tenant->slug}");

        $owner = $tenant->owner;
        if ($owner) {
            $this->line("  <fg=cyan>Owner:</>          {$owner->name} ({$owner->email})");
        } else {
            $this->line("  <fg=cyan>Owner:</>          <fg=red>Not assigned</>");
        }

        $membersCount = $tenant->members()->count();
        $this->line("  <fg=cyan>Members Count:</>  {$membersCount}");

        if ($this->option('verbose') && $membersCount > 0) {
            $this->line("  <fg=cyan>Members:</>");
            $members = $tenant->members;
            foreach ($members as $member) {
                $role = $member->pivot->role ?? 'N/A';
                $this->line("    - {$member->name} ({$member->email}) - Role: {$role}");
            }
        }

        $this->line("  <fg=cyan>Created:</>        {$tenant->created_at->format('Y-m-d H:i:s')}");
        $this->line("  <fg=cyan>Updated:</>        {$tenant->updated_at->format('Y-m-d H:i:s')}");
    }

    /**
     * Display table statistics for tenant.
     */
    protected function displayTableStatistics(Tenant $tenant): void
    {
        $this->info('  Record Counts by Table:');
        $this->newLine();

        $totalRecords = 0;
        $tablesWithData = [];

        foreach ($this->tablesWithTenantId as $table) {
            $count = DB::table($table)
                ->where('tenant_id', $tenant->id)
                ->count();

            if ($count > 0) {
                $tablesWithData[] = [
                    'table' => $table,
                    'count' => $count,
                ];
                $totalRecords += $count;
            }
        }

        if (empty($tablesWithData)) {
            $this->line('    <fg=yellow>No records found for this tenant.</>');
        } else {
            // Sort by count descending
            usort($tablesWithData, fn($a, $b) => $b['count'] <=> $a['count']);

            foreach ($tablesWithData as $data) {
                $this->line(sprintf(
                    '    <fg=green>%-40s</> %s',
                    $data['table'],
                    number_format($data['count'])
                ));
            }

            $this->newLine();
            $this->info("  <fg=yellow>Total Records: " . number_format($totalRecords) . "</>");
        }
    }

    /**
     * Display sample data for key tables.
     */
    protected function displaySampleData(Tenant $tenant): void
    {
        $this->info('  Sample Data:');
        $this->newLine();

        // Show Expenses
        $this->displayExpensesSample($tenant);

        // Show Invoices
        $this->displayInvoicesSample($tenant);

        // Show Customers
        $this->displayCustomersSample($tenant);

        // Show Subscriptions
        $this->displaySubscriptionsSample($tenant);

        // Show Contracts
        $this->displayContractsSample($tenant);

        // Show Investments
        $this->displayInvestmentsSample($tenant);

        // Show Job Applications
        $this->displayJobApplicationsSample($tenant);
    }

    /**
     * Display expenses sample.
     */
    protected function displayExpensesSample(Tenant $tenant): void
    {
        $expenses = DB::table('expenses')
            ->where('tenant_id', $tenant->id)
            ->orderBy('date', 'desc')
            ->limit(5)
            ->get();

        if ($expenses->isNotEmpty()) {
            $this->line('    <fg=cyan>Expenses:</> (Latest 5)');
            foreach ($expenses as $expense) {
                $this->line("      - {$expense->date}: {$expense->description} - " . number_format($expense->amount, 2) . " {$expense->currency}");
            }
            $this->newLine();
        }
    }

    /**
     * Display invoices sample.
     */
    protected function displayInvoicesSample(Tenant $tenant): void
    {
        $invoices = DB::table('invoices')
            ->where('tenant_id', $tenant->id)
            ->orderBy('invoice_date', 'desc')
            ->limit(5)
            ->get();

        if ($invoices->isNotEmpty()) {
            $this->line('    <fg=cyan>Invoices:</> (Latest 5)');
            foreach ($invoices as $invoice) {
                $this->line("      - #{$invoice->invoice_number}: {$invoice->status} - Total: " . number_format($invoice->total, 2) . " {$invoice->currency}");
            }
            $this->newLine();
        }
    }

    /**
     * Display customers sample.
     */
    protected function displayCustomersSample(Tenant $tenant): void
    {
        $customers = DB::table('customers')
            ->where('tenant_id', $tenant->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        if ($customers->isNotEmpty()) {
            $this->line('    <fg=cyan>Customers:</> (Latest 5)');
            foreach ($customers as $customer) {
                $this->line("      - {$customer->name} ({$customer->email})");
            }
            $this->newLine();
        }
    }

    /**
     * Display subscriptions sample.
     */
    protected function displaySubscriptionsSample(Tenant $tenant): void
    {
        $subscriptions = DB::table('subscriptions')
            ->where('tenant_id', $tenant->id)
            ->orderBy('start_date', 'desc')
            ->limit(5)
            ->get();

        if ($subscriptions->isNotEmpty()) {
            $this->line('    <fg=cyan>Subscriptions:</> (Latest 5)');
            foreach ($subscriptions as $subscription) {
                $this->line("      - {$subscription->name}: {$subscription->status} - " . number_format($subscription->amount, 2) . " {$subscription->currency}/{$subscription->billing_cycle}");
            }
            $this->newLine();
        }
    }

    /**
     * Display contracts sample.
     */
    protected function displayContractsSample(Tenant $tenant): void
    {
        $contracts = DB::table('contracts')
            ->where('tenant_id', $tenant->id)
            ->orderBy('start_date', 'desc')
            ->limit(5)
            ->get();

        if ($contracts->isNotEmpty()) {
            $this->line('    <fg=cyan>Contracts:</> (Latest 5)');
            foreach ($contracts as $contract) {
                $this->line("      - {$contract->name}: {$contract->status} (${contract->start_date} - {$contract->end_date})");
            }
            $this->newLine();
        }
    }

    /**
     * Display investments sample.
     */
    protected function displayInvestmentsSample(Tenant $tenant): void
    {
        $investments = DB::table('investments')
            ->where('tenant_id', $tenant->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        if ($investments->isNotEmpty()) {
            $this->line('    <fg=cyan>Investments:</> (Latest 5)');
            foreach ($investments as $investment) {
                $this->line("      - {$investment->name} ({$investment->type}): " . number_format($investment->initial_investment, 2) . " {$investment->currency}");
            }
            $this->newLine();
        }
    }

    /**
     * Display job applications sample.
     */
    protected function displayJobApplicationsSample(Tenant $tenant): void
    {
        $applications = DB::table('job_applications')
            ->where('tenant_id', $tenant->id)
            ->orderBy('applied_at', 'desc')
            ->limit(5)
            ->get();

        if ($applications->isNotEmpty()) {
            $this->line('    <fg=cyan>Job Applications:</> (Latest 5)');
            foreach ($applications as $application) {
                $this->line("      - {$application->job_title} at {$application->company}: {$application->status}");
            }
            $this->newLine();
        }
    }
}
