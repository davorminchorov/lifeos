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
        // Main subscriptions table (read model)
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->text('description');
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3);
            $table->string('billing_cycle');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->string('status');
            $table->string('website')->nullable();
            $table->string('category')->nullable();
            $table->timestamps();
        });

        // Payments table (read model)
        Schema::create('payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('subscription_id');
            $table->decimal('amount', 10, 2);
            $table->date('payment_date');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('subscription_id')
                ->references('id')
                ->on('subscriptions')
                ->onDelete('cascade');
        });

        // Upcoming payments projection
        Schema::create('upcoming_payments', function (Blueprint $table) {
            $table->uuid('subscription_id')->primary();
            $table->date('expected_date');
            $table->decimal('amount', 10, 2);
            $table->timestamps();

            $table->foreign('subscription_id')
                ->references('id')
                ->on('subscriptions')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('upcoming_payments');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('subscriptions');
    }
};
