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
        Schema::create('event_store', function (Blueprint $table) {
            $table->id();
            $table->uuid('event_id')->unique();
            $table->uuid('aggregate_id');
            $table->string('aggregate_type', 100);
            $table->string('event_type', 100);
            $table->timestamp('occurred_at', 6);
            $table->unsignedInteger('version');
            $table->json('payload');
            $table->index('aggregate_id');
            $table->index('aggregate_type');
            $table->index('event_type');
            $table->index('occurred_at');
            // We don't need timestamps for events
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_store');
    }
};
