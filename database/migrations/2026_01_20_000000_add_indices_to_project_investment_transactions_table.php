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
            // Add indices if they don't exist (Laravel handles duplicate index prevention)
            try {
                $table->index(['project_investment_id', 'transaction_date'], 'pit_project_id_date_idx');
            } catch (\Exception $e) {
                // Index already exists, skip
            }

            try {
                $table->index('user_id', 'pit_user_id_idx');
            } catch (\Exception $e) {
                // Index already exists, skip
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
};
