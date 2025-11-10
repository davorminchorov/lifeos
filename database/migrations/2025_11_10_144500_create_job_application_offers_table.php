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
        Schema::create('job_application_offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('job_application_id')->constrained()->cascadeOnDelete();
            $table->decimal('base_salary', 10, 2);
            $table->decimal('bonus', 10, 2)->nullable();
            $table->string('equity')->nullable();
            $table->string('currency', 3)->default('USD');
            $table->text('benefits')->nullable();
            $table->date('start_date')->nullable();
            $table->date('decision_deadline')->nullable();
            $table->string('status');
            $table->text('notes')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['job_application_id']);
            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_application_offers');
    }
};
