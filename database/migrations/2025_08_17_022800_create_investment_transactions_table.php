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
        Schema::create('investment_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('investment_id')->constrained()->cascadeOnDelete();
            $table->enum('transaction_type', ['buy', 'sell', 'dividend_reinvestment', 'stock_split', 'stock_dividend', 'merger', 'spinoff', 'transfer_in', 'transfer_out'])->default('buy');
            $table->decimal('quantity', 15, 8); // shares or units transacted
            $table->decimal('price_per_share', 15, 8); // price per share/unit
            $table->decimal('total_amount', 15, 8); // total transaction amount
            $table->decimal('fees', 10, 2)->default(0); // transaction fees
            $table->decimal('taxes', 10, 2)->default(0); // taxes paid on transaction
            $table->date('transaction_date'); // date of transaction
            $table->date('settlement_date')->nullable(); // settlement date
            $table->string('order_id')->nullable(); // broker order ID
            $table->string('confirmation_number')->nullable(); // confirmation number
            $table->string('account_number')->nullable(); // account where transaction occurred
            $table->string('broker')->nullable(); // broker/platform
            $table->string('currency', 3)->default('USD'); // currency code
            $table->decimal('exchange_rate', 10, 6)->nullable(); // if foreign currency
            $table->enum('order_type', ['market', 'limit', 'stop', 'stop_limit'])->nullable();
            $table->decimal('limit_price', 15, 8)->nullable(); // limit price if applicable
            $table->decimal('stop_price', 15, 8)->nullable(); // stop price if applicable
            $table->text('notes')->nullable();
            $table->json('tax_lot_info')->nullable(); // tax lot information for sales
            $table->timestamps();

            // Indexes for better performance
            $table->index(['investment_id', 'transaction_date']);
            $table->index('transaction_type');
            $table->index('transaction_date');
            $table->index(['broker', 'account_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('investment_transactions');
    }
};
