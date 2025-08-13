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
        Schema::create('warranties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('product_name');
            $table->string('brand');
            $table->string('model')->nullable();
            $table->string('serial_number')->nullable();
            $table->date('purchase_date');
            $table->decimal('purchase_price', 10, 2);
            $table->string('retailer');
            $table->integer('warranty_duration_months'); // warranty length in months
            $table->enum('warranty_type', ['manufacturer', 'extended', 'both'])->default('manufacturer');
            $table->text('warranty_terms')->nullable();
            $table->date('warranty_expiration_date');
            $table->json('claim_history')->nullable(); // track warranty claims
            $table->json('receipt_attachments')->nullable(); // file paths for receipts
            $table->json('proof_of_purchase_attachments')->nullable(); // additional proof files
            $table->enum('current_status', ['active', 'expired', 'claimed', 'transferred'])->default('active');
            $table->json('transfer_history')->nullable(); // track transfers
            $table->json('maintenance_reminders')->nullable(); // maintenance schedule
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warranties');
    }
};
