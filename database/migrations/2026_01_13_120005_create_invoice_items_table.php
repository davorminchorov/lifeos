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
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();

            // Line Item Details
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('quantity', 10, 2)->default(1);
            $table->bigInteger('unit_amount');
            $table->string('currency', 3);

            // Tax
            $table->foreignId('tax_rate_id')->nullable()->constrained()->nullOnDelete();
            $table->bigInteger('tax_amount')->default(0);

            // Discount
            $table->foreignId('discount_id')->nullable()->constrained()->nullOnDelete();
            $table->bigInteger('discount_amount')->default(0);

            // Totals
            $table->bigInteger('amount')->default(0);
            $table->bigInteger('total_amount')->default(0);

            // Metadata
            $table->json('metadata')->nullable();
            $table->integer('sort_order')->default(0);

            $table->timestamps();

            // Indexes
            $table->index(['invoice_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
    }
};
