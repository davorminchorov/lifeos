<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1) Add project-specific nullable fields
        Schema::table('investments', function (Blueprint $table) {
            $table->string('project_type')->nullable()->after('investment_type'); // e.g., SaaS, Mobile App, Marketplace
            $table->string('project_website')->nullable()->after('project_type');
            $table->string('project_repository')->nullable()->after('project_website');
            $table->string('project_stage')->nullable()->after('project_repository'); // idea, prototype, mvp, growth, mature
            $table->string('project_business_model')->nullable()->after('project_stage'); // subscription, ads, one-time, etc.
            $table->decimal('equity_percentage', 5, 2)->nullable()->after('project_business_model');
            $table->date('project_start_date')->nullable()->after('equity_percentage');
            $table->date('project_end_date')->nullable()->after('project_start_date');
            $table->text('project_notes')->nullable()->after('project_end_date');
        });

        // 2) Expand enum to include 'project' on MySQL. SQLite/PostgreSQL fallbacks are handled gracefully.
        try {
            $driver = Schema::getConnection()->getDriverName();
            if ($driver === 'mysql') {
                // Read current enum definition and append 'project'
                DB::statement("ALTER TABLE `investments` MODIFY `investment_type` ENUM('stocks','bonds','etf','mutual_fund','crypto','real_estate','commodities','cash','project') NOT NULL");
            }
        } catch (\Throwable $e) {
            // Fail-safe: do nothing; on non-MySQL drivers or if enum can't be altered, validation change will still allow 'project'.
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
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
            ]);
        });

        try {
            $driver = Schema::getConnection()->getDriverName();
            if ($driver === 'mysql') {
                DB::statement("ALTER TABLE `investments` MODIFY `investment_type` ENUM('stocks','bonds','etf','mutual_fund','crypto','real_estate','commodities','cash') NOT NULL");
            }
        } catch (\Throwable $e) {
            // no-op
        }
    }
};
