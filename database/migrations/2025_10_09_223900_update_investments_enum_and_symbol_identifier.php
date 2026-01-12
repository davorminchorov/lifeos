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
        // Normalize existing data to singular enum values to avoid invalid enum during alter
        DB::table('investments')->where('investment_type', 'stocks')->update(['investment_type' => 'stock']);
        DB::table('investments')->where('investment_type', 'bonds')->update(['investment_type' => 'bond']);

        // Modify enum values to singular set including 'project'
        // Only execute for MySQL/MariaDB (SQLite doesn't use ENUM)
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE investments MODIFY COLUMN investment_type ENUM('stock','bond','etf','mutual_fund','crypto','real_estate','commodities','cash','project') NOT NULL");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert enum back to plural where applicable
        DB::table('investments')->where('investment_type', 'stock')->update(['investment_type' => 'stocks']);
        DB::table('investments')->where('investment_type', 'bond')->update(['investment_type' => 'bonds']);

        // Revert enum to include plural forms and 'project'
        // Only execute for MySQL/MariaDB (SQLite doesn't use ENUM)
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE investments MODIFY COLUMN investment_type ENUM('stocks','bonds','etf','mutual_fund','crypto','real_estate','commodities','cash','project') NOT NULL");
        }
    }
};
