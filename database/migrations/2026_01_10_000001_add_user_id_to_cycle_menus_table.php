<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // First, add the column without foreign key constraint
        Schema::table('cycle_menus', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->after('id')->nullable();
        });

        // Clean up orphaned records: delete cycle_menus that don't have a valid user_id
        \DB::statement('DELETE FROM cycle_menus WHERE user_id IS NULL OR user_id NOT IN (SELECT id FROM users)');

        // Now add the foreign key constraint
        Schema::table('cycle_menus', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('cycle_menus', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};
