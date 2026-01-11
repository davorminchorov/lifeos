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
        // Get all project-type investments from the investments table
        $projects = DB::table('investments')
            ->where('investment_type', 'project')
            ->get();

        // Migrate each project to the new project_investments table
        foreach ($projects as $project) {
            DB::table('project_investments')->insert([
                'user_id' => $project->user_id,
                'name' => $project->name,
                'project_type' => $project->project_type,
                'stage' => $project->project_stage,
                'business_model' => $project->project_business_model,
                'website_url' => $project->project_website,
                'repository_url' => $project->project_repository,
                'equity_percentage' => $project->equity_percentage,
                'investment_amount' => $project->project_amount ?? $project->purchase_price ?? 0,
                'currency' => $project->project_currency ?? $project->currency ?? 'USD',
                'current_value' => $project->current_value,
                'start_date' => $project->project_start_date,
                'end_date' => $project->project_end_date,
                'status' => $project->status ?? 'active',
                'notes' => $project->project_notes ?? $project->notes,
                'created_at' => $project->created_at,
                'updated_at' => $project->updated_at,
            ]);
        }

        // Delete the migrated projects from the investments table
        DB::table('investments')
            ->where('investment_type', 'project')
            ->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Get all project investments from the new table
        $projects = DB::table('project_investments')->get();

        // Migrate back to investments table
        foreach ($projects as $project) {
            DB::table('investments')->insert([
                'user_id' => $project->user_id,
                'investment_type' => 'project',
                'name' => $project->name,
                'project_type' => $project->project_type,
                'project_stage' => $project->stage,
                'project_business_model' => $project->business_model,
                'project_website' => $project->website_url,
                'project_repository' => $project->repository_url,
                'equity_percentage' => $project->equity_percentage,
                'project_amount' => $project->investment_amount,
                'project_currency' => $project->currency,
                'current_value' => $project->current_value,
                'project_start_date' => $project->start_date,
                'project_end_date' => $project->end_date,
                'status' => $project->status,
                'project_notes' => $project->notes,
                'created_at' => $project->created_at,
                'updated_at' => $project->updated_at,
            ]);
        }

        // Delete from project_investments table
        DB::table('project_investments')->truncate();
    }
};
