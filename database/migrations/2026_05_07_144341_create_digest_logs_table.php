<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * digest_logs records every weekly-digest email actually sent. The unique
     * (tenant_id, week_starts_on) constraint is what stops the same week from
     * being emailed twice — even if the user approves two pending actions
     * back-to-back.
     */
    public function up(): void
    {
        Schema::create('digest_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('agent_run_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('pending_action_id')->nullable()->constrained()->nullOnDelete();

            $table->date('week_starts_on');
            $table->string('recipient_email');
            $table->string('subject');
            $table->longText('body_text');
            $table->longText('body_html')->nullable();
            $table->json('structured_summary')->nullable();
            $table->timestamp('sent_at');

            $table->timestamps();

            $table->unique(['tenant_id', 'week_starts_on']);
            $table->index('sent_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('digest_logs');
    }
};
