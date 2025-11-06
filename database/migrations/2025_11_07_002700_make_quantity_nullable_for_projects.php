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
        // Make quantity nullable to support project investments
        // Assumes MySQL / MariaDB. If using a different driver, adjust accordingly.
        DB::statement("ALTER TABLE investments MODIFY COLUMN quantity DECIMAL(20, 8) NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Set quantity to 0 for any null values before making NOT NULL
        DB::table('investments')
            ->whereNull('quantity')
            ->update(['quantity' => 0]);

        // Revert quantity to NOT NULL
        DB::statement("ALTER TABLE investments MODIFY COLUMN quantity DECIMAL(20, 8) NOT NULL");
    }
};
