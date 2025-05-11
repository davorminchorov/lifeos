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
        // Create the tags table
        Schema::create('subscription_tags', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('color')->default('#3B82F6'); // Default to a blue color
            $table->timestamps();
        });

        // Create the subscription_tag pivot table
        Schema::create('subscription_tag', function (Blueprint $table) {
            $table->uuid('subscription_id');
            $table->uuid('tag_id');
            $table->timestamps();

            $table->primary(['subscription_id', 'tag_id']);

            $table->foreign('subscription_id')
                ->references('id')
                ->on('subscriptions')
                ->onDelete('cascade');

            $table->foreign('tag_id')
                ->references('id')
                ->on('subscription_tags')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_tag');
        Schema::dropIfExists('subscription_tags');
    }
};
