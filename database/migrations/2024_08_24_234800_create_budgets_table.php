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
        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('category');
            $table->enum('budget_period', ['monthly', 'quarterly', 'yearly', 'custom'])->default('monthly');
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('MKD');
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_active')->default(true);
            $table->boolean('rollover_unused')->default(false);
            $table->integer('alert_threshold')->default(80)->comment('Percentage threshold for alerts');
            $table->text('notes')->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->index(['user_id', 'category']);
            $table->index(['user_id', 'is_active']);
            $table->index(['start_date', 'end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budgets');
    }
};
