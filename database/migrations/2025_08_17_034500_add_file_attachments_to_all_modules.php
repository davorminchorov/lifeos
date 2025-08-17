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
        // Add file attachments to subscriptions table
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->json('file_attachments')->nullable()->after('notes');
        });

        // Add file attachments to contracts table
        Schema::table('contracts', function (Blueprint $table) {
            $table->json('file_attachments')->nullable()->after('document_attachments');
        });

        // Add file attachments to warranties table
        Schema::table('warranties', function (Blueprint $table) {
            $table->json('file_attachments')->nullable()->after('proof_of_purchase_attachments');
        });

        // Add file attachments to expenses table
        Schema::table('expenses', function (Blueprint $table) {
            $table->json('file_attachments')->nullable()->after('receipt_attachments');
        });

        // Add file attachments to utility bills table
        Schema::table('utility_bills', function (Blueprint $table) {
            $table->json('file_attachments')->nullable()->after('bill_attachments');
        });

        // Add file attachments to investments table
        Schema::table('investments', function (Blueprint $table) {
            $table->json('file_attachments')->nullable()->after('notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn('file_attachments');
        });

        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn('file_attachments');
        });

        Schema::table('warranties', function (Blueprint $table) {
            $table->dropColumn('file_attachments');
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn('file_attachments');
        });

        Schema::table('utility_bills', function (Blueprint $table) {
            $table->dropColumn('file_attachments');
        });

        Schema::table('investments', function (Blueprint $table) {
            $table->dropColumn('file_attachments');
        });
    }
};
