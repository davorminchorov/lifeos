<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cycle_menu_days', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cycle_menu_id')->constrained('cycle_menus')->cascadeOnDelete();
            $table->unsignedInteger('day_index');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['cycle_menu_id', 'day_index']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cycle_menu_days');
    }
};
