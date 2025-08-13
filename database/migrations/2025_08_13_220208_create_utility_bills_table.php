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
        Schema::create('utility_bills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('utility_type', ['electricity', 'gas', 'water', 'internet', 'phone', 'cable_tv', 'trash', 'sewer', 'other']);
            $table->string('service_provider');
            $table->string('account_number');
            $table->string('service_address');
            $table->decimal('bill_amount', 10, 2);
            $table->decimal('usage_amount', 12, 4)->nullable(); // kWh, cubic meters, GB, etc.
            $table->string('usage_unit')->nullable(); // kWh, m³, GB, minutes, etc.
            $table->decimal('rate_per_unit', 10, 6)->nullable(); // price per unit
            $table->date('bill_period_start');
            $table->date('bill_period_end');
            $table->date('due_date');
            $table->enum('payment_status', ['pending', 'paid', 'overdue', 'disputed'])->default('pending');
            $table->date('payment_date')->nullable();
            $table->json('meter_readings')->nullable(); // current and previous readings with photos
            $table->json('bill_attachments')->nullable(); // file paths for bill PDFs/images
            $table->string('service_plan')->nullable(); // plan details
            $table->text('contract_terms')->nullable();
            $table->boolean('auto_pay_enabled')->default(false);
            $table->json('usage_history')->nullable(); // track historical usage patterns
            $table->decimal('budget_alert_threshold', 10, 2)->nullable(); // alert when bill exceeds this
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('utility_bills');
    }
};
