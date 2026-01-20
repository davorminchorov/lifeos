<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckTenancyStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenants:check-status {--user-id= : Check specific user ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check the current state of tenancy setup and data assignment';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('=== Tenancy Status Check ===');
        $this->newLine();

        // Check users
        $totalUsers = DB::table('users')->count();
        $usersWithTenant = DB::table('users')->whereNotNull('current_tenant_id')->count();
        $usersWithoutTenant = $totalUsers - $usersWithTenant;

        $this->info("Users:");
        $this->line("  Total users: {$totalUsers}");
        $this->line("  Users with current_tenant_id: {$usersWithTenant}");
        if ($usersWithoutTenant > 0) {
            $this->warn("  Users WITHOUT current_tenant_id: {$usersWithoutTenant}");
        }
        $this->newLine();

        // Check tenants
        $totalTenants = DB::table('tenants')->count();
        $this->info("Tenants:");
        $this->line("  Total tenants: {$totalTenants}");
        $this->newLine();

        // Check tenant members
        $totalMembers = DB::table('tenant_members')->count();
        $this->info("Tenant Members:");
        $this->line("  Total memberships: {$totalMembers}");
        $this->newLine();

        // Check data tables for NULL tenant_id
        $tablesWithTenantId = [
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

        $this->info("Data Tables with NULL tenant_id:");
        $foundNulls = false;

        foreach ($tablesWithTenantId as $table) {
            $nullCount = DB::table($table)->whereNull('tenant_id')->count();
            $totalCount = DB::table($table)->count();

            if ($nullCount > 0) {
                $this->warn("  {$table}: {$nullCount}/{$totalCount} records have NULL tenant_id");
                $foundNulls = true;
            }
        }

        if (!$foundNulls) {
            $this->info("  ✓ All data tables have tenant_id assigned!");
        }
        $this->newLine();

        // If specific user requested, show their details
        if ($userId = $this->option('user-id')) {
            $this->checkUserDetails($userId);
        }

        // Recommendations
        $this->info('=== Recommendations ===');
        if ($usersWithoutTenant > 0) {
            $this->warn("⚠ Some users don't have current_tenant_id set.");
            $this->line("  Run: php artisan tinker");
            $this->line("       User::whereNull('current_tenant_id')->each(fn(\$u) => \$u->update(['current_tenant_id' => \$u->ownedTenants()->first()?->id ?? \$u->tenants()->first()?->id]))");
        }

        if ($foundNulls) {
            $this->warn("⚠ Some data records have NULL tenant_id.");
            $this->line("  Run: php artisan tenants:assign-missing --dry-run  (to preview)");
            $this->line("       php artisan tenants:assign-missing              (to fix)");
        }

        if (!$foundNulls && $usersWithoutTenant === 0) {
            $this->info("✓ Tenancy appears to be properly configured!");
        }

        return Command::SUCCESS;
    }

    protected function checkUserDetails(int $userId): void
    {
        $this->info("=== User #{$userId} Details ===");

        $user = DB::table('users')->where('id', $userId)->first();
        if (!$user) {
            $this->error("User not found!");
            return;
        }

        $this->line("Name: {$user->name}");
        $this->line("Email: {$user->email}");
        $this->line("Current Tenant ID: " . ($user->current_tenant_id ?? 'NULL'));
        $this->newLine();

        // Owned tenants
        $ownedTenants = DB::table('tenants')->where('owner_id', $userId)->get();
        $this->info("Owned Tenants: " . $ownedTenants->count());
        foreach ($ownedTenants as $tenant) {
            $this->line("  - [{$tenant->id}] {$tenant->name}");
        }
        $this->newLine();

        // Member of tenants
        $memberTenants = DB::table('tenant_members')
            ->where('user_id', $userId)
            ->join('tenants', 'tenants.id', '=', 'tenant_members.tenant_id')
            ->select('tenants.*', 'tenant_members.role')
            ->get();
        $this->info("Member of Tenants: " . $memberTenants->count());
        foreach ($memberTenants as $tenant) {
            $this->line("  - [{$tenant->id}] {$tenant->name} (role: {$tenant->role})");
        }
        $this->newLine();

        // Data counts for this user
        $this->info("Data Records:");
        $expenses = DB::table('expenses')->where('user_id', $userId)->count();
        $expensesWithTenant = DB::table('expenses')->where('user_id', $userId)->whereNotNull('tenant_id')->count();
        $this->line("  Expenses: {$expenses} total, {$expensesWithTenant} with tenant_id");

        $invoices = DB::table('invoices')->where('user_id', $userId)->count();
        $invoicesWithTenant = DB::table('invoices')->where('user_id', $userId)->whereNotNull('tenant_id')->count();
        $this->line("  Invoices: {$invoices} total, {$invoicesWithTenant} with tenant_id");

        $customers = DB::table('customers')->where('user_id', $userId)->count();
        $customersWithTenant = DB::table('customers')->where('user_id', $userId)->whereNotNull('tenant_id')->count();
        $this->line("  Customers: {$customers} total, {$customersWithTenant} with tenant_id");

        $this->newLine();
    }
}
