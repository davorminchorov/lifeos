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
        Schema::create('recurring_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();

            // Recurring details
            $table->string('name'); // e.g., "Monthly Web Hosting"
            $table->text('description')->nullable();
            $table->string('billing_interval'); // daily, weekly, monthly, quarterly, yearly
            $table->integer('interval_count')->default(1); // e.g., every 2 months
            $table->string('status'); // active, paused, cancelled, completed

            // Pricing
            $table->string('currency', 3)->default('USD');
            $table->string('tax_behavior')->default('exclusive');
            $table->integer('net_terms_days')->default(14);

            // Schedule
            $table->date('start_date');
            $table->date('end_date')->nullable(); // null = ongoing
            $table->date('next_billing_date');
            $table->integer('billing_day_of_month')->nullable(); // for monthly/yearly (1-31)
            $table->integer('occurrences_limit')->nullable(); // null = unlimited
            $table->integer('occurrences_count')->default(0);

            // Invoice generation settings
            $table->integer('auto_send_email')->default(1); // boolean
            $table->integer('days_before_due')->nullable(); // send reminder X days before due

            // Metadata
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();

            // Timestamps for tracking
            $table->timestamp('last_generated_at')->nullable();
            $table->timestamp('paused_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['user_id', 'status']);
            $table->index('next_billing_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recurring_invoices');
    }
};
