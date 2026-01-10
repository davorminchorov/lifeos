<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cycle_menu_items', function (Blueprint $table) {
            $table->foreign('recipe_id')->references('id')->on('recipes')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('cycle_menu_items', function (Blueprint $table) {
            $table->dropForeign(['recipe_id']);
        });
    }
};
