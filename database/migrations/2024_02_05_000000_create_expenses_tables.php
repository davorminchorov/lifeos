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
        // Main expenses table (read model)
        Schema::create('expenses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('USD');
            $table->uuid('category_id')->nullable();
            $table->date('date');
            $table->string('payment_method')->nullable();
            $table->text('notes')->nullable();
            $table->string('receipt_url')->nullable();
            $table->timestamps();

            $table->foreign('category_id')
                ->references('category_id')
                ->on('expense_categories')
                ->onDelete('set null');
        });

        // Budget table (read model)
        Schema::create('budgets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('USD');
            $table->string('period'); // monthly, weekly, etc.
            $table->uuid('category_id')->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->timestamps();

            $table->foreign('category_id')
                ->references('category_id')
                ->on('expense_categories')
                ->onDelete('set null');
        });

        // Monthly spending summary projection
        Schema::create('monthly_spending', function (Blueprint $table) {
            $table->id();
            $table->string('year_month'); // Format: YYYY-MM
            $table->decimal('total_amount', 12, 2);
            $table->string('currency', 3);
            $table->integer('expense_count');
            $table->timestamps();

            $table->unique(['year_month', 'currency']);
        });

        // Category spending projection
        Schema::create('category_spending', function (Blueprint $table) {
            $table->id();
            $table->uuid('category_id');
            $table->string('year_month'); // Format: YYYY-MM
            $table->decimal('total_amount', 12, 2);
            $table->string('currency', 3);
            $table->integer('expense_count');
            $table->timestamps();

            $table->foreign('category_id')
                ->references('category_id')
                ->on('expense_categories')
                ->onDelete('cascade');

            $table->unique(['category_id', 'year_month', 'currency']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('category_spending');
        Schema::dropIfExists('monthly_spending');
        Schema::dropIfExists('budgets');
        Schema::dropIfExists('expenses');
    }
};
