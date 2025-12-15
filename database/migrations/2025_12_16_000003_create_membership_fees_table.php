<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('membership_fees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('house_id')->constrained()->cascadeOnDelete();
            $table->foreignId('resident_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('amount', 10, 2)->default(20.00);
            $table->enum('status', ['unpaid', 'paid'])->default('unpaid');
            $table->date('paid_at')->nullable();
            $table->boolean('is_legacy')->default(false);
            $table->string('legacy_owner_name')->nullable();
            $table->year('fee_year');
            $table->string('payment_reference')->nullable();
            $table->timestamps();

            $table->index(['house_id', 'status']);
            $table->index(['fee_year', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('membership_fees');
    }
};

