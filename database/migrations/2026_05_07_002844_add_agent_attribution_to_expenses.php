<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->string('source', 16)->default('user')->after('status');
            $table->foreignId('created_by_agent_token_id')->nullable()
                ->after('source')
                ->constrained('agent_tokens')->nullOnDelete();

            $table->index(['tenant_id', 'source']);
        });
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropForeign(['created_by_agent_token_id']);
            $table->dropIndex(['tenant_id', 'source']);
            $table->dropColumn(['source', 'created_by_agent_token_id']);
        });
    }
};
