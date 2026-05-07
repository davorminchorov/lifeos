<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Phase 5: agent attribution columns on the investments module's tables.
     * Same shape as the columns added in Phase 2 (expenses) and Phase 4
     * (subscriptions, contracts, warranties, ious, utility_bills).
     */
    private array $tables = [
        'investments',
        'investment_transactions',
        'investment_dividends',
    ];

    public function up(): void
    {
        foreach ($this->tables as $name) {
            if (! Schema::hasTable($name)) {
                continue;
            }

            Schema::table($name, function (Blueprint $table) use ($name): void {
                if (! Schema::hasColumn($name, 'source')) {
                    $table->string('source', 16)->default('user');
                }

                if (! Schema::hasColumn($name, 'created_by_agent_token_id')) {
                    $table->foreignId('created_by_agent_token_id')->nullable()
                        ->constrained('agent_tokens')->nullOnDelete();
                }
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $name) {
            if (! Schema::hasTable($name)) {
                continue;
            }

            Schema::table($name, function (Blueprint $table) use ($name): void {
                if (Schema::hasColumn($name, 'created_by_agent_token_id')) {
                    $table->dropForeign(['created_by_agent_token_id']);
                    $table->dropColumn('created_by_agent_token_id');
                }

                if (Schema::hasColumn($name, 'source')) {
                    $table->dropColumn('source');
                }
            });
        }
    }
};
