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
        Schema::create('ious', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['owe', 'owed']); // owe = I owe someone, owed = someone owes me
            $table->string('person_name'); // name of the person involved
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('MKD');
            $table->date('transaction_date');
            $table->date('due_date')->nullable();
            $table->text('description');
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'partially_paid', 'paid', 'cancelled'])->default('pending');
            $table->decimal('amount_paid', 10, 2)->default(0);
            $table->string('payment_method')->nullable(); // cash, bank transfer, etc.
            $table->string('category')->nullable(); // loan, borrowed item value, service, etc.
            $table->json('attachments')->nullable(); // receipts, contracts, etc.
            $table->boolean('is_recurring')->default(false);
            $table->string('recurring_schedule')->nullable(); // monthly, etc.
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ious');
    }
};
