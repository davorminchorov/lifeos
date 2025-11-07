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
        // Make purchase_price nullable to support project investments
        // Assumes MySQL / MariaDB. If using a different driver, adjust accordingly.
        DB::statement('ALTER TABLE investments MODIFY COLUMN purchase_price DECIMAL(20, 8) NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Set placeholder for any null purchase_price before making NOT NULL
        DB::table('investments')
            ->whereNull('purchase_price')
            ->update(['purchase_price' => 0]);

        // Revert purchase_price to NOT NULL
        DB::statement('ALTER TABLE investments MODIFY COLUMN purchase_price DECIMAL(20, 8) NOT NULL');
    }
};
