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
        // Add reminder fields to subscriptions table
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->integer('reminder_days_before')->nullable();
            $table->boolean('reminder_enabled')->default(false);
            $table->string('reminder_method')->nullable();
        });

        // Create the subscription_reminders table
        Schema::create('subscription_reminders', function (Blueprint $table) {
            $table->uuid('subscription_id')->primary();
            $table->string('subscription_name');
            $table->date('reminder_date');
            $table->date('payment_date');
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3);
            $table->string('method');
            $table->boolean('sent')->default(false);
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->foreign('subscription_id')
                ->references('id')
                ->on('subscriptions')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_reminders');

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn([
                'reminder_days_before',
                'reminder_enabled',
                'reminder_method'
            ]);
        });
    }
};
