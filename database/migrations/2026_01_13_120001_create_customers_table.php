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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Basic Info
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('company_name')->nullable();

            // Billing Address
            $table->json('billing_address')->nullable();

            // Tax Info
            $table->string('tax_id')->nullable();
            $table->string('tax_country', 2)->nullable();

            // Payment Defaults
            $table->string('currency', 3)->default('MKD');
            $table->string('default_payment_method_id')->nullable();

            // Metadata
            $table->json('metadata')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['user_id', 'email']);
            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
