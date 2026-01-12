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
        Schema::create('investor_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('browserless_connection_id')->constrained()->cascadeOnDelete();
            $table->string('portal_name')->default('investor.wvpfondovi.mk');
            $table->json('raw_data')->nullable(); // Store the full JSON response
            $table->json('tables')->nullable(); // Extracted table data
            $table->json('funds')->nullable(); // Extracted fund data
            $table->text('screenshot')->nullable(); // Base64 screenshot for debugging
            $table->timestamp('crawled_at');
            $table->timestamps();

            // Index for faster queries
            $table->index(['user_id', 'crawled_at']);
            $table->index('browserless_connection_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('investor_data');
    }
};
