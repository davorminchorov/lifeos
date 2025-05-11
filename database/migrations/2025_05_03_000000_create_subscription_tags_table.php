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
        // Skip this migration if the tables already exist from our adaptive migration
        if (Schema::hasTable('subscription_tags') && Schema::hasTable('subscription_tag')) {
            return;
        }

        // Check if the subscriptions table exists and has a UUID primary key
        $usingUuid = Schema::hasTable('subscriptions') &&
                     Schema::hasColumn('subscriptions', 'id') &&
                     Schema::getColumnType('subscriptions', 'id') !== 'bigint';

        Schema::create('subscription_tags', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('color', 7)->default('#6366F1');
            $table->timestamps();
        });

        Schema::create('subscription_tag', function (Blueprint $table) use ($usingUuid) {
            // Ensure the type matches the subscriptions.id column
            if ($usingUuid) {
                $table->uuid('subscription_id');
            } else {
                $table->unsignedBigInteger('subscription_id');
            }

            $table->unsignedBigInteger('tag_id');

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

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Skip this down migration if the adaptive migration has run
        if ((Schema::hasTable('subscription_tags') || Schema::hasTable('subscription_tag')) &&
            Schema::hasTable('subscriptions') &&
            Schema::hasColumn('subscriptions', 'id') &&
            Schema::getColumnType('subscriptions', 'id') === 'bigint') {
            return;
        }

        Schema::dropIfExists('subscription_tag');
        Schema::dropIfExists('subscription_tags');
    }
};
