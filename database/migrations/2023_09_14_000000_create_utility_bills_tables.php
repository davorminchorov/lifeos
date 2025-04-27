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
        // Main utility bills table
        Schema::create('utility_bills', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('provider');
            $table->decimal('amount', 10, 2);
            $table->date('due_date');
            $table->string('category');
            $table->boolean('is_recurring')->default(false);
            $table->string('recurrence_period')->nullable();
            $table->text('notes')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();
        });

        // Bill payments table
        Schema::create('bill_payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('bill_id');
            $table->date('payment_date');
            $table->decimal('payment_amount', 10, 2);
            $table->string('payment_method');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('bill_id')
                ->references('id')
                ->on('utility_bills')
                ->onDelete('cascade');
        });

        // Bill reminders table
        Schema::create('bill_reminders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('bill_id');
            $table->date('reminder_date');
            $table->text('reminder_message');
            $table->string('status')->default('scheduled');
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->foreign('bill_id')
                ->references('id')
                ->on('utility_bills')
                ->onDelete('cascade');
        });

        // Pending bills view (denormalized)
        Schema::create('pending_bills', function (Blueprint $table) {
            $table->uuid('bill_id')->primary();
            $table->string('name');
            $table->string('provider');
            $table->decimal('amount', 10, 2);
            $table->date('due_date');
            $table->string('category');
            $table->timestamps();

            $table->foreign('bill_id')
                ->references('id')
                ->on('utility_bills')
                ->onDelete('cascade');
        });

        // Payment history view (denormalized)
        Schema::create('payment_history', function (Blueprint $table) {
            $table->id();
            $table->uuid('bill_id');
            $table->string('bill_name');
            $table->string('provider');
            $table->date('payment_date');
            $table->decimal('payment_amount', 10, 2);
            $table->string('payment_method');
            $table->string('category');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('bill_id')
                ->references('id')
                ->on('utility_bills')
                ->onDelete('cascade');
        });

        // Upcoming reminders view (denormalized)
        Schema::create('upcoming_reminders', function (Blueprint $table) {
            $table->id();
            $table->uuid('bill_id');
            $table->string('bill_name');
            $table->string('provider');
            $table->date('due_date');
            $table->decimal('amount', 10, 2);
            $table->date('reminder_date');
            $table->text('reminder_message');
            $table->timestamps();

            $table->foreign('bill_id')
                ->references('id')
                ->on('utility_bills')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('upcoming_reminders');
        Schema::dropIfExists('payment_history');
        Schema::dropIfExists('pending_bills');
        Schema::dropIfExists('bill_reminders');
        Schema::dropIfExists('bill_payments');
        Schema::dropIfExists('utility_bills');
    }
};
