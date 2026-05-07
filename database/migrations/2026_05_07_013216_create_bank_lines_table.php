<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by_agent_token_id')->nullable()
                ->constrained('agent_tokens')->nullOnDelete();

            $table->string('account');
            $table->date('posted_at');
            // Signed integer cents: negative = debit (money leaving), positive = credit.
            $table->integer('amount_cents');
            $table->string('currency', 3);
            $table->string('merchant_raw')->nullable();
            $table->text('description')->nullable();
            $table->integer('balance_after_cents')->nullable();

            $table->string('statement_id')->nullable();
            $table->integer('statement_row')->nullable();

            // Deterministic idempotency key over natural fields. Re-importing the
            // same line collapses to the same row (unique within tenant).
            $table->string('fingerprint', 64);

            $table->foreignId('matched_expense_id')->nullable()
                ->constrained('expenses')->nullOnDelete();
            $table->foreignId('matched_pending_action_id')->nullable()
                ->constrained('pending_actions')->nullOnDelete();

            // unmatched | matched | matched_pending | ignored
            $table->string('match_status', 24)->default('unmatched');
            $table->decimal('match_confidence', 3, 2)->nullable();
            $table->json('match_candidates')->nullable();

            $table->string('source', 16)->default('agent');

            $table->timestamps();

            $table->unique(['tenant_id', 'fingerprint']);
            $table->index(['tenant_id', 'posted_at']);
            $table->index(['tenant_id', 'match_status']);
            $table->index(['tenant_id', 'account', 'posted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_lines');
    }
};
