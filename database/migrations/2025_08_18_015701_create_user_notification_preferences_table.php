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
        Schema::create('user_notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('notification_type'); // subscription_renewal, contract_expiration, etc.
            $table->boolean('email_enabled')->default(true);
            $table->boolean('database_enabled')->default(true);
            $table->boolean('push_enabled')->default(false);
            $table->json('settings')->nullable(); // For additional settings like days_before array
            $table->timestamps();

            $table->unique(['user_id', 'notification_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_notification_preferences');
    }
};
