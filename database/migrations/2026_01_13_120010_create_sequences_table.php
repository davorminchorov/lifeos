<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sequences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Sequence Scope
            $table->enum('scope', ['invoice', 'credit_note']);
            $table->integer('year');

            // Current Value
            $table->integer('current_value')->default(0);

            // Prefix (optional override)
            $table->string('prefix')->nullable();

            $table->timestamps();

            // Unique constraint: one sequence per user, scope, year
            $table->unique(['user_id', 'scope', 'year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sequences');
    }
};
