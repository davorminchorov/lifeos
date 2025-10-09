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

        // Ensure symbol_identifier is not null going forward; set placeholder for existing nulls
        DB::table('investments')->whereNull('symbol_identifier')->update(['symbol_identifier' => 'UNKNOWN']);

        // Modify enum values to singular set and enforce NOT NULL for symbol_identifier using raw SQL
        // Assumes MySQL / MariaDB. If using a different driver, adjust accordingly.
        DB::statement("ALTER TABLE investments MODIFY COLUMN investment_type ENUM('stock','bond','etf','mutual_fund','crypto','real_estate','commodities','cash') NOT NULL");
        DB::statement("ALTER TABLE investments MODIFY COLUMN symbol_identifier VARCHAR(255) NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert enum back to plural where applicable
        DB::table('investments')->where('investment_type', 'stock')->update(['investment_type' => 'stocks']);
        DB::table('investments')->where('investment_type', 'bond')->update(['investment_type' => 'bonds']);

        // Allow symbol_identifier to be nullable again
        DB::statement("ALTER TABLE investments MODIFY COLUMN investment_type ENUM('stocks','bonds','etf','mutual_fund','crypto','real_estate','commodities','cash') NOT NULL");
        DB::statement("ALTER TABLE investments MODIFY COLUMN symbol_identifier VARCHAR(255) NULL");
    }
};
