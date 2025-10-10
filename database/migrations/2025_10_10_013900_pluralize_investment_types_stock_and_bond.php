<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            // 1) Temporarily allow both singular and plural to avoid invalid enum errors during data update
            DB::statement("ALTER TABLE `investments` MODIFY `investment_type` ENUM('stock','stocks','bond','bonds','etf','mutual_fund','crypto','real_estate','commodities','cash','project') NOT NULL");
        }

        // 2) Normalize existing rows to pluralized values
        DB::table('investments')->where('investment_type', 'stock')->update(['investment_type' => 'stocks']);
        DB::table('investments')->where('investment_type', 'bond')->update(['investment_type' => 'bonds']);

        if ($driver === 'mysql') {
            // 3) Restrict enum to plural-only set
            DB::statement("ALTER TABLE `investments` MODIFY `investment_type` ENUM('stocks','bonds','etf','mutual_fund','crypto','real_estate','commodities','cash','project') NOT NULL");
        }
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            // Allow both while reverting data
            DB::statement("ALTER TABLE `investments` MODIFY `investment_type` ENUM('stock','stocks','bond','bonds','etf','mutual_fund','crypto','real_estate','commodities','cash','project') NOT NULL");
        }

        // Revert to singular values
        DB::table('investments')->where('investment_type', 'stocks')->update(['investment_type' => 'stock']);
        DB::table('investments')->where('investment_type', 'bonds')->update(['investment_type' => 'bond']);

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE `investments` MODIFY `investment_type` ENUM('stock','bond','etf','mutual_fund','crypto','real_estate','commodities','cash') NOT NULL");
        }
    }
};
