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
        // Skip this migration if the table already exists from our adaptive migration
        if (Schema::hasTable('subscription_reminders')) {
            return;
        }

        // Check if the subscriptions table exists and has a UUID primary key
        $usingUuid = Schema::hasTable('subscriptions') &&
                    Schema::hasColumn('subscriptions', 'id') &&
                    Schema::getColumnType('subscriptions', 'id') !== 'bigint';

        Schema::create('subscription_reminders', function (Blueprint $table) use ($usingUuid) {
            $table->id();

            // Ensure the type matches the subscriptions.id column
            if ($usingUuid) {
                $table->uuid('subscription_id');
            } else {
                $table->unsignedBigInteger('subscription_id');
            }

            $table->integer('days_before')->default(3);
            $table->boolean('enabled')->default(true);
            $table->string('method')->default('email');
            $table->timestamp('last_sent_at')->nullable();
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
        // Skip this down migration if the adaptive migration has run
        if (Schema::hasTable('subscription_reminders') &&
            Schema::hasTable('subscriptions') &&
            Schema::hasColumn('subscriptions', 'id') &&
            Schema::getColumnType('subscriptions', 'id') === 'bigint') {
            return;
        }

        Schema::dropIfExists('subscription_reminders');
    }
};
