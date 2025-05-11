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
        // First, check if both tables exist
        if (Schema::hasTable('subscriptions') && Schema::hasColumn('subscriptions', 'id') && Schema::getColumnType('subscriptions', 'id') === 'bigint') {
            // The original migration with auto-incrementing ID has run

            // Drop the tables created by the newer migration if they exist
            // We need to drop in reverse order due to foreign key constraints
            Schema::dropIfExists('upcoming_payments');

            // Add a new next_payment_date column to the original subscriptions table if it doesn't exist
            if (!Schema::hasColumn('subscriptions', 'next_payment_date')) {
                Schema::table('subscriptions', function (Blueprint $table) {
                    $table->date('next_payment_date')->nullable();
                });
            }

            // Check if we need to modify the payments table
            if (Schema::hasColumn('payments', 'id') && Schema::getColumnType('payments', 'id') === 'bigint' && !Schema::hasColumn('payments', 'payment_method')) {
                // Add payment_method to the original payments table if it doesn't exist
                Schema::table('payments', function (Blueprint $table) {
                    $table->string('payment_method')->default('credit_card')->after('payment_date');
                });
            }

            // Create a new subscription_events table for event sourcing
            Schema::create('subscription_events', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->unsignedBigInteger('subscription_id');
                $table->string('event_type');
                $table->json('payload');
                $table->timestamp('occurred_at');
                $table->timestamps();

                $table->index('subscription_id');
                $table->index('event_type');

                $table->foreign('subscription_id')
                    ->references('id')
                    ->on('subscriptions')
                    ->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_events');
    }
};
