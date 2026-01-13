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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();

            // Invoice Number
            $table->string('number')->nullable()->unique();
            $table->integer('sequence_year')->nullable();
            $table->integer('sequence_no')->nullable();
            $table->string('hash', 8)->nullable();

            // Status
            $table->enum('status', [
                'draft',
                'issued',
                'paid',
                'partially_paid',
                'past_due',
                'void',
                'written_off',
                'archived'
            ])->default('draft');

            // Currency
            $table->string('currency', 3);

            // Amounts (stored in cents as integers for precision)
            $table->bigInteger('subtotal')->default(0);
            $table->bigInteger('discount_total')->default(0);
            $table->bigInteger('tax_total')->default(0);
            $table->bigInteger('total')->default(0);
            $table->bigInteger('amount_due')->default(0);
            $table->bigInteger('amount_paid')->default(0);

            // Tax Behavior
            $table->enum('tax_behavior', ['inclusive', 'exclusive'])->default('exclusive');

            // Dates
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('due_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('voided_at')->nullable();

            // Terms & Notes
            $table->integer('net_terms_days')->default(14);
            $table->text('notes')->nullable();
            $table->text('internal_notes')->nullable();

            // PDF Storage
            $table->string('pdf_path')->nullable();

            // Subscription Link (optional)
            $table->foreignId('subscription_id')->nullable()->constrained()->nullOnDelete();

            // Metadata
            $table->json('metadata')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'customer_id']);
            $table->index(['status', 'due_at']);
            $table->index(['issued_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
