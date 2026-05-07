<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agent_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('agent_token_id')->nullable()->constrained('agent_tokens')->nullOnDelete();

            $table->string('agent_slug');
            $table->string('session_id')->nullable();
            $table->string('model')->nullable();
            $table->string('status')->default('running'); // running|completed|failed|cancelled

            $table->json('tools_called')->nullable();   // map<tool, count>
            $table->unsignedInteger('pending_actions_created')->default(0);
            $table->unsignedBigInteger('tokens_in')->default(0);
            $table->unsignedBigInteger('tokens_out')->default(0);
            $table->decimal('cost_usd', 10, 4)->default(0);

            $table->text('error')->nullable();

            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'agent_slug', 'status']);
            $table->index(['tenant_id', 'started_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_runs');
    }
};
