<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agent_run_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_run_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('sequence');
            $table->string('type'); // tool_call | tool_result | text | error | system
            $table->json('payload')->nullable();
            $table->timestamp('occurred_at');

            $table->index(['agent_run_id', 'sequence']);
            $table->index('occurred_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_run_events');
    }
};
