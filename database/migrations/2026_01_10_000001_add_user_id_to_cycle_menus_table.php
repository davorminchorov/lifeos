<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // First, add the column without foreign key constraint (if it doesn't exist)
        if (!Schema::hasColumn('cycle_menus', 'user_id')) {
            Schema::table('cycle_menus', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->after('id')->nullable();
            });
        }

        // Clean up orphaned records: delete cycle_menus that don't have a valid user_id
        \DB::statement('DELETE FROM cycle_menus WHERE user_id IS NULL OR user_id NOT IN (SELECT id FROM users)');

        // Now add the foreign key constraint (if it doesn't exist)
        // Use database-specific query to check for existing foreign key
        $foreignKeyExists = false;

        if (\DB::getDriverName() === 'mysql') {
            $foreignKeys = \DB::select("
                SELECT CONSTRAINT_NAME
                FROM information_schema.TABLE_CONSTRAINTS
                WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = 'cycle_menus'
                AND CONSTRAINT_TYPE = 'FOREIGN KEY'
                AND CONSTRAINT_NAME = 'cycle_menus_user_id_foreign'
            ");
            $foreignKeyExists = !empty($foreignKeys);
        } else {
            // For SQLite and other databases, we'll try to add the constraint
            // and catch any errors if it already exists
            try {
                Schema::table('cycle_menus', function (Blueprint $table) {
                    $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                });
                $foreignKeyExists = true; // Set to true to skip the second add attempt
            } catch (\Exception $e) {
                // Foreign key already exists or not supported, skip
                $foreignKeyExists = true;
            }
        }

        if (!$foreignKeyExists) {
            Schema::table('cycle_menus', function (Blueprint $table) {
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::table('cycle_menus', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};
