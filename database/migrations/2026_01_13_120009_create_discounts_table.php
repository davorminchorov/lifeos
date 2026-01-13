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
        Schema::create('discounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Discount Details
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();

            // Type & Value
            $table->enum('type', ['percent', 'fixed'])->default('percent');
            $table->integer('value');
            $table->string('currency', 3)->nullable();

            // Validity
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->boolean('active')->default(true);

            // Usage Limits
            $table->integer('max_redemptions')->nullable();
            $table->integer('current_redemptions')->default(0);
            $table->integer('max_redemptions_per_customer')->nullable();

            // Minimum Requirements
            $table->bigInteger('minimum_amount')->nullable();

            // Metadata
            $table->json('metadata')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['user_id', 'active']);
            $table->index(['code']);
            $table->index(['ends_at', 'active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discounts');
    }
};
