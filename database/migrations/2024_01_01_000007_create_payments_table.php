<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('house_id')->constrained()->cascadeOnDelete();
            $table->foreignId('resident_id')->nullable()->constrained()->nullOnDelete();
            $table->string('payment_no')->unique();
            $table->decimal('amount', 10, 2);
            $table->enum('status', ['pending', 'success', 'failed', 'cancelled'])->default('pending');
            $table->enum('payment_type', ['current_month', 'selected_months', 'yearly'])->default('current_month');
            
            // ToyyibPay fields
            $table->string('toyyibpay_billcode')->nullable();
            $table->string('toyyibpay_ref')->nullable();
            $table->string('toyyibpay_transaction_id')->nullable();
            $table->text('toyyibpay_response')->nullable();
            
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            
            $table->index(['house_id', 'status']);
            $table->index(['status', 'created_at']);
            $table->index('toyyibpay_billcode');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};

