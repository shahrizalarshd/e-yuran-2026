<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('house_id')->constrained()->cascadeOnDelete();
            $table->foreignId('fee_configuration_id')->nullable()->constrained()->nullOnDelete();
            $table->string('bill_no')->unique();
            $table->year('bill_year');
            $table->unsignedTinyInteger('bill_month'); // 1-12
            $table->decimal('amount', 10, 2);
            $table->enum('status', ['unpaid', 'paid', 'processing', 'partial'])->default('unpaid');
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->date('due_date');
            $table->date('paid_at')->nullable();
            $table->timestamps();
            
            $table->unique(['house_id', 'bill_year', 'bill_month']);
            $table->index(['status', 'due_date']);
            $table->index(['house_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bills');
    }
};

