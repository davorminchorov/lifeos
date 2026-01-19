<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // For each existing project investment, create an initial transaction
        // with the current investment_amount
        $projectInvestments = DB::table('project_investments')->get();

        foreach ($projectInvestments as $project) {
            // Use start_date if available, otherwise use created_at
            $transactionDate = $project->start_date ?? $project->created_at;

            DB::table('project_investment_transactions')->insert([
                'project_investment_id' => $project->id,
                'user_id' => $project->user_id,
                'amount' => $project->investment_amount,
                'currency' => $project->currency,
                'transaction_date' => $transactionDate,
                'notes' => 'Initial investment (migrated from investment_amount field)',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Delete all transactions that were created during migration
        DB::table('project_investment_transactions')
            ->where('notes', 'Initial investment (migrated from investment_amount field)')
            ->delete();
    }
};
