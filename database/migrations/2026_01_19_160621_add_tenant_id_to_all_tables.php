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
            Schema::table($table, function (Blueprint $table) {
                $table->foreignId('tenant_id')->nullable()->after('id')->constrained()->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
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
            Schema::table($table, function (Blueprint $table) {
                $table->dropForeign(['tenant_id']);
                $table->dropColumn('tenant_id');
            });
        }
    }
};
