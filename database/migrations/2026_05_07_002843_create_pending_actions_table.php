<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pending_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('agent_token_id')->nullable()->constrained('agent_tokens')->nullOnDelete();

            $table->string('agent_slug')->nullable();
            $table->string('session_id')->nullable();
            $table->string('tool');
            $table->string('action');

            $table->nullableMorphs('target');
            $table->json('payload');
            $table->json('preview')->nullable();

            $table->string('idempotency_key', 64);

            $table->string('status')->default('pending');
            $table->json('applied_diff')->nullable();
            $table->string('failure_reason')->nullable();

            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('applied_at')->nullable();
            $table->foreignId('reverted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reverted_at')->nullable();
            $table->foreignId('reverted_pending_action_id')->nullable()
                ->constrained('pending_actions')->nullOnDelete();

            $table->timestamps();

            $table->unique(['tenant_id', 'tool', 'idempotency_key']);
            $table->index(['tenant_id', 'status', 'created_at']);
            $table->index(['tenant_id', 'agent_slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pending_actions');
    }
};
