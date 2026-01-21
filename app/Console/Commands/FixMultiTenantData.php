<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class FixMultiTenantData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenants:fix-multi-tenant-data
                            {--dry-run : Show what would be fixed without making changes}
                            {--user-id= : Fix data for specific user only}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix data that has been incorrectly assigned to multiple tenants for the same user';

    /**
     * Tables that have tenant_id and user_id columns
     */
    protected array $tables = [
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
        'cycle_menus',
        'project_investments',
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
    ];

    /**
     * Nested tables that need to be updated through parent relationships
     */
    protected array $nestedTables = [
        'investment_transactions' => ['parent_table' => 'investments', 'parent_column' => 'investment_id'],
        'investment_dividends' => ['parent_table' => 'investments', 'parent_column' => 'investment_id'],
        'cycle_menu_days' => ['parent_table' => 'cycle_menus', 'parent_column' => 'cycle_menu_id'],
        'cycle_menu_items' => ['parent_table' => 'cycle_menu_days', 'parent_column' => 'cycle_menu_day_id'],
        'invoice_items' => ['parent_table' => 'invoices', 'parent_column' => 'invoice_id'],
        'invoice_reminders' => ['parent_table' => 'invoices', 'parent_column' => 'invoice_id'],
        'credit_note_applications' => ['parent_table' => 'credit_notes', 'parent_column' => 'credit_note_id'],
        'refunds' => ['parent_table' => 'payments', 'parent_column' => 'payment_id'],
        'job_application_status_histories' => ['parent_table' => 'job_applications', 'parent_column' => 'job_application_id'],
        'job_application_interviews' => ['parent_table' => 'job_applications', 'parent_column' => 'job_application_id'],
        'job_application_offers' => ['parent_table' => 'job_applications', 'parent_column' => 'job_application_id'],
        'project_investment_transactions' => ['parent_table' => 'project_investments', 'parent_column' => 'project_investment_id'],
        'recurring_invoice_items' => ['parent_table' => 'recurring_invoices', 'parent_column' => 'recurring_invoice_id'],
    ];

    protected int $totalIssues = 0;
    protected int $totalFixed = 0;

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $userId = $this->option('user-id');

        if ($dryRun) {
            $this->warn('🔍 DRY RUN MODE - No changes will be made');
            $this->newLine();
        }

        $this->info('🔧 Analyzing multi-tenant data assignment issues...');
        $this->newLine();

        // Get users with data issues
        $usersWithIssues = $this->findUsersWithMultiTenantData($userId);

        if ($usersWithIssues->isEmpty()) {
            $this->info('✅ No multi-tenant data issues found!');
            return Command::SUCCESS;
        }

        $this->warn("Found {$usersWithIssues->count()} user(s) with data across multiple tenants");
        $this->newLine();

        foreach ($usersWithIssues as $user) {
            $this->processUser($user, $dryRun);
        }

        $this->newLine();
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info("📊 Summary:");
        $this->line("   Total issues found: {$this->totalIssues}");

        if ($dryRun) {
            $this->warn("   Would fix: {$this->totalFixed} records");
            $this->newLine();
            $this->info('💡 Run without --dry-run to apply changes');
        } else {
            $this->info("   ✅ Fixed: {$this->totalFixed} records");
        }
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        return Command::SUCCESS;
    }

    /**
     * Find users who have data across multiple tenants
     */
    protected function findUsersWithMultiTenantData(?int $userId): \Illuminate\Support\Collection
    {
        $query = User::query()
            ->whereNotNull('current_tenant_id');

        if ($userId) {
            $query->where('id', $userId);
        }

        return $query->get()->filter(function (User $user) {
            // Check if user has data in tenants other than their current tenant
            foreach ($this->tables as $table) {
                $wrongTenantCount = DB::table($table)
                    ->where('user_id', $user->id)
                    ->where('tenant_id', '!=', $user->current_tenant_id)
                    ->count();

                if ($wrongTenantCount > 0) {
                    return true;
                }
            }
            return false;
        });
    }

    /**
     * Process a single user and fix their data
     */
    protected function processUser(User $user, bool $dryRun): void
    {
        $this->line("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->info("👤 User: {$user->name} (ID: {$user->id})");
        $this->line("   Email: {$user->email}");
        $this->line("   Correct Tenant ID: {$user->current_tenant_id}");
        $this->newLine();

        $userIssues = 0;
        $userFixed = 0;

        // Fix direct tables (those with user_id)
        foreach ($this->tables as $table) {
            $result = $this->fixTableData($table, $user, $dryRun);
            $userIssues += $result['issues'];
            $userFixed += $result['fixed'];
        }

        // Fix nested tables (those without user_id but linked through parent)
        foreach ($this->nestedTables as $table => $config) {
            $result = $this->fixNestedTableData($table, $config, $user, $dryRun);
            $userIssues += $result['issues'];
            $userFixed += $result['fixed'];
        }

        $this->totalIssues += $userIssues;
        $this->totalFixed += $userFixed;

        if ($userIssues > 0) {
            $this->newLine();
            if ($dryRun) {
                $this->warn("   📋 Found {$userIssues} records that would be fixed");
            } else {
                $this->info("   ✅ Fixed {$userFixed} records");
            }
        } else {
            $this->line("   ✅ No issues found for this user");
        }
    }

    /**
     * Fix data in a single table
     */
    protected function fixTableData(string $table, User $user, bool $dryRun): array
    {
        // Get records with wrong tenant_id
        $wrongRecords = DB::table($table)
            ->where('user_id', $user->id)
            ->where('tenant_id', '!=', $user->current_tenant_id)
            ->get();

        if ($wrongRecords->isEmpty()) {
            return ['issues' => 0, 'fixed' => 0];
        }

        $count = $wrongRecords->count();

        // Group by current (wrong) tenant_id for reporting
        $wrongTenantIds = $wrongRecords->pluck('tenant_id')->unique()->implode(', ');

        if ($dryRun) {
            $this->line("   📦 {$table}: {$count} records (currently in tenants: {$wrongTenantIds})");
        } else {
            DB::table($table)
                ->where('user_id', $user->id)
                ->where('tenant_id', '!=', $user->current_tenant_id)
                ->update(['tenant_id' => $user->current_tenant_id]);

            $this->line("   ✓ {$table}: Fixed {$count} records");
        }

        return ['issues' => $count, 'fixed' => $count];
    }

    /**
     * Fix data in nested tables (without direct user_id)
     */
    protected function fixNestedTableData(string $table, array $config, User $user, bool $dryRun): array
    {
        $parentTable = $config['parent_table'];
        $parentColumn = $config['parent_column'];

        // Find records where parent has wrong tenant_id
        $wrongRecords = DB::table($table)
            ->join($parentTable, "{$table}.{$parentColumn}", '=', "{$parentTable}.id")
            ->where("{$parentTable}.user_id", $user->id)
            ->where("{$table}.tenant_id", '!=', $user->current_tenant_id)
            ->select("{$table}.*")
            ->get();

        if ($wrongRecords->isEmpty()) {
            return ['issues' => 0, 'fixed' => 0];
        }

        $count = $wrongRecords->count();
        $wrongTenantIds = $wrongRecords->pluck('tenant_id')->unique()->implode(', ');

        if ($dryRun) {
            $this->line("   📦 {$table}: {$count} records (currently in tenants: {$wrongTenantIds})");
        } else {
            // Update using subquery
            DB::table($table)
                ->whereIn('id', $wrongRecords->pluck('id'))
                ->update(['tenant_id' => $user->current_tenant_id]);

            $this->line("   ✓ {$table}: Fixed {$count} records");
        }

        return ['issues' => $count, 'fixed' => $count];
    }
}
