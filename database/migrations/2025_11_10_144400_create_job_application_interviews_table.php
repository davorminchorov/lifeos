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
        Schema::create('job_application_interviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('job_application_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->datetime('scheduled_at');
            $table->integer('duration_minutes')->nullable();
            $table->string('location')->nullable();
            $table->string('video_link')->nullable();
            $table->string('interviewer_name')->nullable();
            $table->text('notes')->nullable();
            $table->text('feedback')->nullable();
            $table->string('outcome')->nullable();
            $table->boolean('completed')->default(false);
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['job_application_id']);
            $table->index(['user_id', 'scheduled_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_application_interviews');
    }
};
