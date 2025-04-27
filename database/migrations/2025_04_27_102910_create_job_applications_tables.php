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
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('company_name');
            $table->string('position');
            $table->date('application_date');
            $table->text('job_description')->nullable();
            $table->string('application_url')->nullable();
            $table->string('salary_range')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('contact_email')->nullable();
            $table->enum('status', ['applied', 'interviewing', 'offered', 'rejected', 'withdrawn'])->default('applied');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('job_application_interviews', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('application_id');
            $table->date('interview_date');
            $table->string('interview_time');
            $table->enum('interview_type', ['phone', 'video', 'in-person']);
            $table->string('with_person');
            $table->string('location')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('application_id')
                ->references('id')
                ->on('job_applications')
                ->onDelete('cascade');
        });

        Schema::create('job_application_outcomes', function (Blueprint $table) {
            $table->uuid('application_id')->primary();
            $table->enum('outcome', ['offered', 'rejected', 'withdrawn']);
            $table->date('outcome_date');
            $table->string('salary_offered')->nullable();
            $table->text('feedback')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('application_id')
                ->references('id')
                ->on('job_applications')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_application_outcomes');
        Schema::dropIfExists('job_application_interviews');
        Schema::dropIfExists('job_applications');
    }
};
