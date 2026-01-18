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
        Schema::create('tax_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Tax Details
            $table->string('name');
            $table->string('code')->nullable();
            $table->integer('percentage_basis_points');

            // Jurisdiction
            $table->string('country', 2)->nullable();
            $table->string('region')->nullable();

            // Behavior
            $table->boolean('inclusive')->default(false);
            $table->boolean('active')->default(true);

            // Validity Period
            $table->date('valid_from')->nullable();
            $table->date('valid_to')->nullable();

            // Description
            $table->text('description')->nullable();

            // Metadata
            $table->json('metadata')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['user_id', 'active']);
            $table->index(['country', 'active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tax_rates');
    }
};
