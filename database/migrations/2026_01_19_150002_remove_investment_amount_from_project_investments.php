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
        Schema::table('project_investments', function (Blueprint $table) {
            $table->dropColumn(['investment_amount', 'currency']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_investments', function (Blueprint $table) {
            $table->decimal('investment_amount', 15, 2)->default(0);
            $table->string('currency', 3)->default('USD');
        });
    }
};
