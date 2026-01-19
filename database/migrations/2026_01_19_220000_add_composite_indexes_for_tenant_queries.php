<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add composite indexes for frequently queried tenant+user combinations
        Schema::table('budgets', function (Blueprint $table) {
            $table->index(['tenant_id', 'user_id'], 'idx_budgets_tenant_user');
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->index(['tenant_id', 'user_id'], 'idx_subscriptions_tenant_user');
        });

        Schema::table('contracts', function (Blueprint $table) {
            $table->index(['tenant_id', 'user_id'], 'idx_contracts_tenant_user');
        });

        Schema::table('warranties', function (Blueprint $table) {
            $table->index(['tenant_id', 'user_id'], 'idx_warranties_tenant_user');
        });

        Schema::table('investments', function (Blueprint $table) {
            $table->index(['tenant_id', 'user_id'], 'idx_investments_tenant_user');
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->index(['tenant_id', 'user_id'], 'idx_expenses_tenant_user');
        });

        Schema::table('utility_bills', function (Blueprint $table) {
            $table->index(['tenant_id', 'user_id'], 'idx_utility_bills_tenant_user');
        });

        Schema::table('ious', function (Blueprint $table) {
            $table->index(['tenant_id', 'user_id'], 'idx_ious_tenant_user');
        });

        Schema::table('job_applications', function (Blueprint $table) {
            $table->index(['tenant_id', 'user_id'], 'idx_job_applications_tenant_user');
        });

        Schema::table('cycle_menus', function (Blueprint $table) {
            $table->index(['tenant_id', 'user_id'], 'idx_cycle_menus_tenant_user');
        });

        Schema::table('project_investments', function (Blueprint $table) {
            $table->index(['tenant_id', 'user_id'], 'idx_project_investments_tenant_user');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->index(['tenant_id', 'user_id'], 'idx_customers_tenant_user');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->index(['tenant_id', 'user_id'], 'idx_invoices_tenant_user');
        });

        Schema::table('tax_rates', function (Blueprint $table) {
            $table->index(['tenant_id', 'user_id'], 'idx_tax_rates_tenant_user');
        });

        Schema::table('recurring_invoices', function (Blueprint $table) {
            $table->index(['tenant_id', 'user_id'], 'idx_recurring_invoices_tenant_user');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('budgets', function (Blueprint $table) {
            $table->dropIndex('idx_budgets_tenant_user');
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropIndex('idx_subscriptions_tenant_user');
        });

        Schema::table('contracts', function (Blueprint $table) {
            $table->dropIndex('idx_contracts_tenant_user');
        });

        Schema::table('warranties', function (Blueprint $table) {
            $table->dropIndex('idx_warranties_tenant_user');
        });

        Schema::table('investments', function (Blueprint $table) {
            $table->dropIndex('idx_investments_tenant_user');
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->dropIndex('idx_expenses_tenant_user');
        });

        Schema::table('utility_bills', function (Blueprint $table) {
            $table->dropIndex('idx_utility_bills_tenant_user');
        });

        Schema::table('ious', function (Blueprint $table) {
            $table->dropIndex('idx_ious_tenant_user');
        });

        Schema::table('job_applications', function (Blueprint $table) {
            $table->dropIndex('idx_job_applications_tenant_user');
        });

        Schema::table('cycle_menus', function (Blueprint $table) {
            $table->dropIndex('idx_cycle_menus_tenant_user');
        });

        Schema::table('project_investments', function (Blueprint $table) {
            $table->dropIndex('idx_project_investments_tenant_user');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropIndex('idx_customers_tenant_user');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropIndex('idx_invoices_tenant_user');
        });

        Schema::table('tax_rates', function (Blueprint $table) {
            $table->dropIndex('idx_tax_rates_tenant_user');
        });

        Schema::table('recurring_invoices', function (Blueprint $table) {
            $table->dropIndex('idx_recurring_invoices_tenant_user');
        });
    }
};
