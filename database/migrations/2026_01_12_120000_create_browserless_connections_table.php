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
        Schema::create('browserless_connections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('portal_name')->default('investor.wvpfondovi.mk');
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamp('last_successful_sync_at')->nullable();
            $table->boolean('sync_enabled')->default(true);
            $table->text('last_error')->nullable();
            $table->integer('consecutive_failures')->default(0);
            $table->timestamps();

            // One connection per user per portal
            $table->unique(['user_id', 'portal_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('browserless_connections');
    }
};
