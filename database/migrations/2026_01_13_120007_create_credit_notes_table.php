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
        Schema::create('credit_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();

            // Credit Note Number
            $table->string('number')->nullable()->unique();

            // Status
            $table->enum('status', ['draft', 'issued', 'applied', 'void'])->default('draft');

            // Currency
            $table->string('currency', 3);

            // Amounts (stored in cents)
            $table->bigInteger('subtotal')->default(0);
            $table->bigInteger('tax_total')->default(0);
            $table->bigInteger('total')->default(0);
            $table->bigInteger('amount_remaining')->default(0);

            // Reason
            $table->enum('reason', [
                'duplicate',
                'fraudulent',
                'requested_by_customer',
                'product_unsatisfactory',
                'order_canceled',
                'other'
            ])->nullable();
            $table->text('reason_notes')->nullable();

            // Dates
            $table->timestamp('issued_at')->nullable();

            // PDF Storage
            $table->string('pdf_path')->nullable();

            // Metadata
            $table->json('metadata')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['user_id', 'status']);
            $table->index(['customer_id', 'issued_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credit_notes');
    }
};
