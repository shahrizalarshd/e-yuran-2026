<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('membership_fee_configurations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('amount', 10, 2)->default(20.00);
            $table->date('effective_from');
            $table->date('effective_until')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['is_active', 'effective_from', 'effective_until'], 'membership_fee_active_dates_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('membership_fee_configurations');
    }
};

