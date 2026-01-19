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
        Schema::table('investments', function (Blueprint $table) {
            $table->dropColumn([
                'project_type',
                'project_website',
                'project_repository',
                'project_stage',
                'project_business_model',
                'equity_percentage',
                'project_start_date',
                'project_end_date',
                'project_notes',
                'project_amount',
                'project_currency',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('investments', function (Blueprint $table) {
            $table->string('project_type')->nullable();
            $table->string('project_website')->nullable();
            $table->string('project_repository')->nullable();
            $table->string('project_stage')->nullable();
            $table->string('project_business_model')->nullable();
            $table->decimal('equity_percentage', 5, 2)->nullable();
            $table->date('project_start_date')->nullable();
            $table->date('project_end_date')->nullable();
            $table->text('project_notes')->nullable();
            $table->decimal('project_amount', 15, 2)->nullable();
            $table->string('project_currency', 3)->nullable();
        });
    }
};
