<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->boolean('agents_writes_disabled')->default(false)->after('default_country');
            $table->json('tool_auto_apply')->nullable()->after('agents_writes_disabled');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['agents_writes_disabled', 'tool_auto_apply']);
        });
    }
};
