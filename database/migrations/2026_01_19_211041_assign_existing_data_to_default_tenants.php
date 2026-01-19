<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This migration creates a default tenant for each existing user
     * and assigns all their existing data to that tenant.
     */
    public function up(): void
    {
        // Get all existing users
        $users = DB::table('users')->get();

        foreach ($users as $user) {
            // Create a default tenant for this user
            $tenantId = DB::table('tenants')->insertGetId([
                'name' => "{$user->name}'s Personal Account",
                'slug' => Str::slug($user->name).'-personal-'.Str::random(6),
                'owner_id' => $user->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Add the user as an admin member of their tenant
            DB::table('tenant_members')->insert([
                'tenant_id' => $tenantId,
                'user_id' => $user->id,
                'role' => 'admin',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Set user's current_tenant_id
            DB::table('users')
                ->where('id', $user->id)
                ->update(['current_tenant_id' => $tenantId]);

            // Assign all existing data to this tenant
            $this->assignDataToTenant($user->id, $tenantId);
        }
    }

    /**
     * Assign all existing user data to their default tenant.
     */
    protected function assignDataToTenant(int $userId, int $tenantId): void
    {
        $tables = [
            'budgets',
            'subscriptions',
            'contracts',
            'warranties',
            'investments',
            'investment_goals',
            'investment_dividends',
            'investment_transactions',
            'expenses',
            'utility_bills',
            'ious',
            'job_applications',
            'job_application_status_histories',
            'job_application_interviews',
            'job_application_offers',
            'cycle_menus',
            'cycle_menu_days',
            'cycle_menu_items',
            'project_investments',
            'project_investment_transactions',
            'gmail_connections',
            'processed_emails',
            'customers',
            'invoices',
            'tax_rates',
            'discounts',
            'invoice_items',
            'payments',
            'credit_notes',
            'credit_note_applications',
            'refunds',
            'sequences',
            'recurring_invoices',
            'recurring_invoice_items',
            'invoice_reminders',
        ];

        foreach ($tables as $table) {
            DB::table($table)
                ->where('user_id', $userId)
                ->update(['tenant_id' => $tenantId]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Set all tenant_id values back to null
        $tables = [
            'budgets',
            'subscriptions',
            'contracts',
            'warranties',
            'investments',
            'investment_goals',
            'investment_dividends',
            'investment_transactions',
            'expenses',
            'utility_bills',
            'ious',
            'job_applications',
            'job_application_status_histories',
            'job_application_interviews',
            'job_application_offers',
            'cycle_menus',
            'cycle_menu_days',
            'cycle_menu_items',
            'project_investments',
            'project_investment_transactions',
            'gmail_connections',
            'processed_emails',
            'customers',
            'invoices',
            'tax_rates',
            'discounts',
            'invoice_items',
            'payments',
            'credit_notes',
            'credit_note_applications',
            'refunds',
            'sequences',
            'recurring_invoices',
            'recurring_invoice_items',
            'invoice_reminders',
        ];

        foreach ($tables as $table) {
            DB::table($table)->update(['tenant_id' => null]);
        }

        // Clear users' current_tenant_id
        DB::table('users')->update(['current_tenant_id' => null]);

        // Delete tenant members
        DB::table('tenant_members')->truncate();

        // Delete tenants
        DB::table('tenants')->truncate();
    }
};
