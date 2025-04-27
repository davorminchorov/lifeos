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
        // Main investments table
        Schema::create('investment_list', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('type'); // stock, bond, mutual_fund, etf, real_estate, retirement, life_insurance, other
            $table->string('institution');
            $table->string('account_number')->nullable();
            $table->decimal('initial_investment', 15, 2);
            $table->decimal('current_value', 15, 2);
            $table->decimal('roi', 8, 2)->default(0); // Return on investment percentage
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->text('description')->nullable();
            $table->decimal('total_invested', 15, 2);
            $table->decimal('total_withdrawn', 15, 2)->default(0);
            $table->date('last_valuation_date');
            $table->timestamps();
        });

        // Transactions table
        Schema::create('investment_transaction_list', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('investment_id');
            $table->string('type'); // deposit, withdrawal, dividend, fee, interest
            $table->decimal('amount', 15, 2);
            $table->date('date');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('investment_id')
                ->references('id')
                ->on('investment_list')
                ->onDelete('cascade');
        });

        // Valuations table
        Schema::create('investment_valuation_list', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('investment_id');
            $table->decimal('value', 15, 2);
            $table->date('date');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('investment_id')
                ->references('id')
                ->on('investment_list')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('investment_valuation_list');
        Schema::dropIfExists('investment_transaction_list');
        Schema::dropIfExists('investment_list');
    }
};
