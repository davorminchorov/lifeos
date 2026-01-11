<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Only add the foreign key if the recipes table exists
        if (Schema::hasTable('recipes')) {
            // Check if the foreign key constraint already exists
            $foreignKeys = \DB::select("
                SELECT CONSTRAINT_NAME
                FROM information_schema.TABLE_CONSTRAINTS
                WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = 'cycle_menu_items'
                AND CONSTRAINT_TYPE = 'FOREIGN KEY'
                AND CONSTRAINT_NAME = 'cycle_menu_items_recipe_id_foreign'
            ");

            if (empty($foreignKeys)) {
                Schema::table('cycle_menu_items', function (Blueprint $table) {
                    $table->foreign('recipe_id')->references('id')->on('recipes')->nullOnDelete();
                });
            }
        }
        // If recipes table doesn't exist yet, this migration will be a no-op
        // The foreign key can be added later when the recipes table is created
    }

    public function down(): void
    {
        // Check if the foreign key exists before dropping it
        $foreignKeys = \DB::select("
            SELECT CONSTRAINT_NAME
            FROM information_schema.TABLE_CONSTRAINTS
            WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = 'cycle_menu_items'
            AND CONSTRAINT_TYPE = 'FOREIGN KEY'
            AND CONSTRAINT_NAME = 'cycle_menu_items_recipe_id_foreign'
        ");

        if (!empty($foreignKeys)) {
            Schema::table('cycle_menu_items', function (Blueprint $table) {
                $table->dropForeign(['recipe_id']);
            });
        }
    }
};
