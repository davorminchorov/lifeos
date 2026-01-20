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
        Schema::table('project_investment_transactions', function (Blueprint $table) {
            // Check if indices don't already exist before adding them
            if (!$this->indexExists('project_investment_transactions', 'pit_project_id_date_idx')) {
                $table->index(['project_investment_id', 'transaction_date'], 'pit_project_id_date_idx');
            }

            if (!$this->indexExists('project_investment_transactions', 'pit_user_id_idx')) {
                $table->index('user_id', 'pit_user_id_idx');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_investment_transactions', function (Blueprint $table) {
            $table->dropIndex('pit_project_id_date_idx');
            $table->dropIndex('pit_user_id_idx');
        });
    }

    /**
     * Check if an index exists on a table.
     */
    private function indexExists(string $table, string $index): bool
    {
        $connection = Schema::getConnection();
        $doctrineSchemaManager = $connection->getDoctrineSchemaManager();
        $doctrineTable = $doctrineSchemaManager->introspectTable($table);

        return $doctrineTable->hasIndex($index);
    }
};
