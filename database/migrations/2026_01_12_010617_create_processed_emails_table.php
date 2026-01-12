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
        Schema::create('processed_emails', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('gmail_message_id')->unique();
            $table->foreignId('expense_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('processed_at')->nullable();
            $table->enum('processing_status', ['pending', 'processed', 'failed', 'skipped'])->default('pending');
            $table->text('failure_reason')->nullable();
            $table->json('email_data')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'processing_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('processed_emails');
    }
};
