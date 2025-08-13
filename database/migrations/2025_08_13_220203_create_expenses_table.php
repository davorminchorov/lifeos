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
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('USD');
            $table->string('category'); // food, transport, entertainment, etc.
            $table->string('subcategory')->nullable(); // restaurants, gas, movies, etc.
            $table->date('expense_date');
            $table->text('description');
            $table->string('merchant')->nullable();
            $table->string('payment_method')->nullable(); // credit card, cash, debit, etc.
            $table->json('receipt_attachments')->nullable(); // file paths for receipts
            $table->json('tags')->nullable(); // flexible tagging system
            $table->string('location')->nullable(); // where the expense occurred
            $table->boolean('is_tax_deductible')->default(false);
            $table->enum('expense_type', ['business', 'personal'])->default('personal');
            $table->boolean('is_recurring')->default(false);
            $table->string('recurring_schedule')->nullable(); // daily, weekly, monthly, yearly
            $table->decimal('budget_allocated', 10, 2)->nullable(); // budget category amount
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'confirmed', 'reimbursed'])->default('confirmed');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
