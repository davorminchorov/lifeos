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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('service_name');
            $table->text('description')->nullable();
            $table->string('category');
            $table->decimal('cost', 10, 2);
            $table->string('billing_cycle'); // monthly, yearly, weekly, custom
            $table->integer('billing_cycle_days')->nullable(); // for custom cycles
            $table->string('currency', 3)->default('USD');
            $table->date('start_date');
            $table->date('next_billing_date');
            $table->date('cancellation_date')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('merchant_info')->nullable();
            $table->boolean('auto_renewal')->default(true);
            $table->integer('cancellation_difficulty')->nullable(); // 1-5 rating
            $table->json('price_history')->nullable(); // track price changes
            $table->text('notes')->nullable();
            $table->json('tags')->nullable();
            $table->enum('status', ['active', 'cancelled', 'paused'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
