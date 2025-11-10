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
        Schema::create('job_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Company information
            $table->string('company_name');
            $table->string('company_website')->nullable();

            // Job details
            $table->string('job_title');
            $table->text('job_description')->nullable();
            $table->string('job_url')->nullable();
            $table->string('location')->nullable();
            $table->boolean('remote')->default(false);

            // Salary information
            $table->decimal('salary_min', 10, 2)->nullable();
            $table->decimal('salary_max', 10, 2)->nullable();
            $table->string('currency', 3)->default('USD');

            // Application details
            $table->string('status');
            $table->string('source');
            $table->date('applied_at')->nullable();
            $table->datetime('next_action_at')->nullable();
            $table->tinyInteger('priority')->default(0);

            // Contact information
            $table->string('contact_name')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();

            // Additional information
            $table->text('notes')->nullable();
            $table->json('tags')->nullable();
            $table->json('file_attachments')->nullable();

            // Soft archive
            $table->timestamp('archived_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'applied_at']);
            $table->index(['user_id', 'next_action_at']);
            $table->index(['user_id', 'priority']);
            $table->index(['user_id', 'archived_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_applications');
    }
};
