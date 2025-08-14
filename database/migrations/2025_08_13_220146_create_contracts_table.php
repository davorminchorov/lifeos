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
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('contract_type'); // lease, employment, service, etc.
            $table->string('title');
            $table->string('counterparty'); // other party name
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->integer('notice_period_days')->nullable();
            $table->boolean('auto_renewal')->default(false);
            $table->decimal('contract_value', 12, 2)->nullable();
            $table->string('payment_terms')->nullable();
            $table->text('key_obligations')->nullable();
            $table->text('penalties')->nullable();
            $table->text('termination_clauses')->nullable();
            $table->json('document_attachments')->nullable(); // file paths
            $table->integer('performance_rating')->nullable(); // 1-5 rating
            $table->json('renewal_history')->nullable(); // track renewals
            $table->json('amendments')->nullable(); // track changes
            $table->text('notes')->nullable();
            $table->enum('status', ['active', 'expired', 'terminated', 'pending'])->default('active');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
