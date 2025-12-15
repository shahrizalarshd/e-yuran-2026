<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('house_occupancies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('house_id')->constrained()->cascadeOnDelete();
            $table->foreignId('resident_id')->constrained()->cascadeOnDelete();
            $table->enum('role', ['owner', 'tenant']);
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->boolean('is_payer')->default(false);
            $table->timestamps();
            
            $table->index(['house_id', 'role', 'end_date']);
            $table->index(['resident_id', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('house_occupancies');
    }
};

