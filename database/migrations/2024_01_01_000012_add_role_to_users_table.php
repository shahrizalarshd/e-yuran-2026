<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['super_admin', 'treasurer', 'auditor', 'resident'])->default('resident')->after('email');
            $table->enum('language_preference', ['bm', 'en'])->default('bm')->after('role');
            $table->boolean('is_active')->default(true)->after('language_preference');
            
            $table->index('role');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role']);
            $table->dropColumn(['role', 'language_preference', 'is_active']);
        });
    }
};

