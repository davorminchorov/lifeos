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
        Schema::create('investments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('investment_type', ['stocks', 'bonds', 'etf', 'mutual_fund', 'crypto', 'real_estate', 'commodities', 'cash']);
            $table->string('symbol_identifier')->nullable(); // ticker symbol or identifier
            $table->string('name'); // investment name/description
            $table->decimal('quantity', 15, 8); // support crypto decimals
            $table->date('purchase_date');
            $table->decimal('purchase_price', 15, 8);
            $table->decimal('current_value', 15, 8)->nullable(); // updated from market data
            $table->decimal('total_dividends_received', 12, 2)->default(0);
            $table->decimal('total_fees_paid', 10, 2)->default(0);
            $table->json('investment_goals')->nullable(); // retirement, growth, income, etc.
            $table->enum('risk_tolerance', ['conservative', 'moderate', 'aggressive'])->default('moderate');
            $table->string('account_broker')->nullable(); // broker/platform name
            $table->string('account_number')->nullable();
            $table->json('transaction_history')->nullable(); // buy/sell transactions
            $table->json('tax_lots')->nullable(); // for tax reporting
            $table->decimal('target_allocation_percentage', 5, 2)->nullable(); // portfolio allocation target
            $table->date('last_price_update')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['active', 'sold', 'pending'])->default('active');

            // Additional fields for advanced functionality
            $table->date('sale_date')->nullable();
            $table->decimal('sale_price', 15, 8)->nullable();
            $table->decimal('sale_proceeds', 15, 8)->nullable();
            $table->decimal('realized_gain_loss', 15, 8)->nullable();
            $table->decimal('quantity_sold', 15, 8)->nullable();
            $table->integer('holding_period_days')->nullable();
            $table->json('dividend_history')->nullable();
            $table->decimal('qualified_dividends', 12, 2)->nullable();
            $table->decimal('non_qualified_dividends', 12, 2)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('investments');
    }
};
