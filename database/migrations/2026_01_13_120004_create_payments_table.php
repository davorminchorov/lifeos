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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();

            // Payment Provider
            $table->string('provider')->default('manual');
            $table->string('provider_payment_id')->nullable();

            // Amount
            $table->bigInteger('amount');
            $table->string('currency', 3);

            // Status
            $table->enum('status', [
                'pending',
                'succeeded',
                'failed',
                'refunded',
                'partially_refunded'
            ])->default('pending');

            // Timestamps
            $table->timestamp('attempted_at')->nullable();
            $table->timestamp('succeeded_at')->nullable();
            $table->timestamp('failed_at')->nullable();

            // Failure Info
            $table->string('failure_code')->nullable();
            $table->text('failure_message')->nullable();

            // Payment Method
            $table->string('payment_method')->nullable();
            $table->json('payment_method_details')->nullable();

            // Metadata
            $table->json('metadata')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['invoice_id', 'status']);
            $table->index(['provider', 'provider_payment_id']);
            $table->index(['succeeded_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
