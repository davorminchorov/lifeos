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
        Schema::create('invoice_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();

            // Reminder details
            $table->integer('days_after_due'); // e.g., 7 = sent 7 days after due date
            $table->string('reminder_type'); // first, second, final, custom
            $table->text('message')->nullable(); // Custom message if any

            // Tracking
            $table->timestamp('sent_at');
            $table->boolean('email_sent')->default(false);
            $table->text('email_error')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['invoice_id', 'sent_at']);
            $table->index(['user_id', 'sent_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_reminders');
    }
};
