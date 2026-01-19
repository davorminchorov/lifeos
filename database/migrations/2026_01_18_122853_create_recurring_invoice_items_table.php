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
        Schema::create('recurring_invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('recurring_invoice_id')->constrained()->cascadeOnDelete();

            // Item details
            $table->string('description', 500);
            $table->decimal('quantity', 10, 3)->default(1);
            $table->integer('unit_amount'); // in cents

            // Pricing modifiers
            $table->foreignId('tax_rate_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('discount_id')->nullable()->constrained()->nullOnDelete();

            // Display order
            $table->integer('sort_order')->default(0);

            $table->timestamps();

            // Indexes
            $table->index(['recurring_invoice_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recurring_invoice_items');
    }
};
