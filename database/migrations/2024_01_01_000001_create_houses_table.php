<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('houses', function (Blueprint $table) {
            $table->id();
            $table->string('house_no', 20);
            $table->string('street_name', 100);
            $table->boolean('is_registered')->default(false);
            $table->boolean('is_active')->default(true);
            $table->enum('status', ['occupied', 'vacant'])->default('vacant');
            $table->timestamps();
            
            $table->unique(['house_no', 'street_name']);
            $table->index(['is_registered', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('houses');
    }
};

