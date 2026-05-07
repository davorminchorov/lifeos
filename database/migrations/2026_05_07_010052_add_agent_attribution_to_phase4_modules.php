<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Phase 4 extends agent attribution to the modules the email-ingestion agent
     * proposes writes against. Same shape as the columns already on `expenses`
     * (Phase 2 migration).
     */
    /**
     * Note: job_applications already has a `source` column (job-board source —
     * LinkedIn, Indeed, etc.) that means something different. We trace agent
     * attribution for status updates / interview adds via the PendingAction
     * row itself rather than a column on the JobApplication.
     */
    private array $tables = [
        'subscriptions',
        'contracts',
        'warranties',
        'ious',
        'utility_bills',
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
