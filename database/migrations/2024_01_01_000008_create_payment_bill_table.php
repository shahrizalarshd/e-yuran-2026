<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Pivot table for payments and bills (many-to-many)
        Schema::create('payment_bill', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bill_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->timestamps();
            
            $table->unique(['payment_id', 'bill_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_bill');
    }
};

