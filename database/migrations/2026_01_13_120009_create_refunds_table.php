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
        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained()->cascadeOnDelete();

            // Amount
            $table->bigInteger('amount');
            $table->string('currency', 3);

            // Provider
            $table->string('provider')->default('manual');
            $table->string('provider_refund_id')->nullable();

            // Status
            $table->enum('status', ['pending', 'succeeded', 'failed', 'canceled'])->default('pending');

            // Reason
            $table->enum('reason', [
                'duplicate',
                'fraudulent',
                'requested_by_customer',
                'product_unsatisfactory',
                'other'
            ])->nullable();
            $table->text('reason_notes')->nullable();

            // Timestamps
            $table->timestamp('processed_at')->nullable();

            // Metadata
            $table->json('metadata')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['payment_id', 'status']);
            $table->index(['provider', 'provider_refund_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refunds');
    }
};
