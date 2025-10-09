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
        Schema::table('investments', function (Blueprint $table) {
            // Explicit amount and currency for project-based investments
            $table->decimal('project_amount', 15, 2)->nullable()->after('project_notes');
            $table->string('project_currency', 3)->nullable()->after('project_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('investments', function (Blueprint $table) {
            $table->dropColumn(['project_amount', 'project_currency']);
        });
    }
};
