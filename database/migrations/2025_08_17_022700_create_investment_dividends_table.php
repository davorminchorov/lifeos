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
        Schema::create('investment_dividends', function (Blueprint $table) {
            $table->id();
            $table->foreignId('investment_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 12, 2); // dividend amount
            $table->date('record_date'); // date when dividend was recorded
            $table->date('payment_date'); // date when dividend was/will be paid
            $table->date('ex_dividend_date')->nullable(); // ex-dividend date
            $table->enum('dividend_type', ['ordinary', 'qualified', 'special', 'return_of_capital'])->default('ordinary');
            $table->enum('frequency', ['monthly', 'quarterly', 'semi_annual', 'annual', 'special'])->default('quarterly');
            $table->decimal('dividend_per_share', 15, 8); // dividend amount per share
            $table->decimal('shares_held', 15, 8); // number of shares held at record date
            $table->decimal('tax_withheld', 12, 2)->default(0); // tax withheld if any
            $table->string('currency', 3)->default('MKD'); // currency code
            $table->boolean('reinvested')->default(false); // whether dividend was reinvested
            $table->text('notes')->nullable();
            $table->timestamps();

            // Indexes for better performance
            $table->index(['investment_id', 'record_date']);
            $table->index('payment_date');
            $table->index('dividend_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('investment_dividends');
    }
};
