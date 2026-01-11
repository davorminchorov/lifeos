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
        Schema::create('project_investments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('project_type')->nullable(); // SaaS, Mobile App, Marketplace, etc.
            $table->string('stage')->nullable(); // idea, prototype, mvp, growth, mature
            $table->string('business_model')->nullable(); // subscription, ads, one-time, freemium
            $table->string('website_url')->nullable();
            $table->string('repository_url')->nullable();
            $table->decimal('equity_percentage', 5, 2)->nullable();
            $table->decimal('investment_amount', 15, 2)->default(0);
            $table->string('currency', 3)->default('USD');
            $table->decimal('current_value', 15, 2)->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('status')->default('active'); // active, completed, sold, abandoned
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('stage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_investments');
    }
};
