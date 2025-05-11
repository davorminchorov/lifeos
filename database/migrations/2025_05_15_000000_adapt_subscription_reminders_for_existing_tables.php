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
        // Only run if subscription_reminders doesn't exist yet
        if (Schema::hasTable('subscription_reminders')) {
            return;
        }

        // Check if we're using the original tables with auto-incrementing ids
        $usingOriginalTables = Schema::hasTable('subscriptions') &&
                              Schema::hasColumn('subscriptions', 'id') &&
                              Schema::getColumnType('subscriptions', 'id') === 'bigint';

        // Create subscription_reminders table
        Schema::create('subscription_reminders', function (Blueprint $table) use ($usingOriginalTables) {
            $table->id();

            if ($usingOriginalTables) {
                $table->unsignedBigInteger('subscription_id');
            } else {
                $table->uuid('subscription_id');
            }

            $table->integer('days_before')->default(3);
            $table->boolean('enabled')->default(true);
            $table->string('method')->default('email');
            $table->timestamp('last_sent_at')->nullable();
            $table->timestamps();

            // Add foreign key constraint
            $table->foreign('subscription_id')
                ->references('id')
                ->on('subscriptions')
                ->onDelete('cascade');
        });

        // Only create other tables if they don't exist
        if (!Schema::hasTable('subscription_notifications')) {
            // Create subscription_notifications table
            Schema::create('subscription_notifications', function (Blueprint $table) use ($usingOriginalTables) {
                $table->id();

                if ($usingOriginalTables) {
                    $table->unsignedBigInteger('subscription_id');
                } else {
                    $table->uuid('subscription_id');
                }

                $table->string('type');
                $table->text('content');
                $table->timestamp('sent_at');
                $table->boolean('is_read')->default(false);
                $table->timestamp('read_at')->nullable();
                $table->timestamps();

                // Add foreign key constraint
                $table->foreign('subscription_id')
                    ->references('id')
                    ->on('subscriptions')
                    ->onDelete('cascade');
            });
        }

        // Create subscription_tags table if it doesn't exist
        if (!Schema::hasTable('subscription_tags')) {
            Schema::create('subscription_tags', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('color', 7)->default('#6366F1');
                $table->timestamps();
            });
        }

        // Create pivot table if it doesn't exist
        if (!Schema::hasTable('subscription_tag')) {
            Schema::create('subscription_tag', function (Blueprint $table) use ($usingOriginalTables) {
                if ($usingOriginalTables) {
                    $table->unsignedBigInteger('subscription_id');
                } else {
                    $table->uuid('subscription_id');
                }

                $table->unsignedBigInteger('tag_id');

                // Add foreign key constraints
                $table->foreign('subscription_id')
                    ->references('id')
                    ->on('subscriptions')
                    ->onDelete('cascade');

                $table->foreign('tag_id')
                    ->references('id')
                    ->on('subscription_tags')
                    ->onDelete('cascade');

                $table->primary(['subscription_id', 'tag_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_tag');
        Schema::dropIfExists('subscription_tags');
        Schema::dropIfExists('subscription_notifications');
        Schema::dropIfExists('subscription_reminders');
    }
};
