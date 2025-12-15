<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->boolean('is_legacy')->default(false)->after('paid_at');
            $table->string('payment_method', 50)->default('toyyibpay')->after('is_legacy');
            $table->string('legacy_reference')->nullable()->after('payment_method');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['is_legacy', 'payment_method', 'legacy_reference']);
        });
    }
};

