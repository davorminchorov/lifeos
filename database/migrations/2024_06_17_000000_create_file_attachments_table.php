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
        Schema::create('file_attachments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('entity_id');
            $table->string('entity_type');
            $table->string('file_name');
            $table->string('original_name');
            $table->string('file_path');
            $table->string('mime_type');
            $table->unsignedBigInteger('size');
            $table->string('disk')->default('local');
            $table->timestamps();

            // Index for fast lookup by entity
            $table->index(['entity_type', 'entity_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_attachments');
    }
};
