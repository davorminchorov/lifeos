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
        // Make symbol_identifier nullable to support project investments
        // Only execute for MySQL/MariaDB (SQLite handles this differently)
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE investments MODIFY COLUMN symbol_identifier VARCHAR(255) NULL');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Set placeholder for any null symbol_identifiers before making NOT NULL
        DB::table('investments')
            ->whereNull('symbol_identifier')
            ->update(['symbol_identifier' => 'UNKNOWN']);

        // Revert symbol_identifier to NOT NULL
        // Only execute for MySQL/MariaDB (SQLite handles this differently)
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE investments MODIFY COLUMN symbol_identifier VARCHAR(255) NOT NULL');
        }
    }
};
