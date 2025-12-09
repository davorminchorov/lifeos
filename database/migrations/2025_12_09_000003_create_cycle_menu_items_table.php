<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cycle_menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cycle_menu_day_id')->constrained('cycle_menu_days')->cascadeOnDelete();
            $table->string('title');
            $table->string('meal_type')->default('other');
            $table->time('time_of_day')->nullable();
            $table->string('quantity')->nullable();
            $table->unsignedBigInteger('recipe_id')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->index(['cycle_menu_day_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cycle_menu_items');
    }
};
