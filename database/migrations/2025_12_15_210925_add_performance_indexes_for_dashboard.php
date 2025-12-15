<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Performance indexes for dashboard analytics
 * These indexes optimize year-based queries for long-term scalability
 */
return new class extends Migration
{
    public function up(): void
    {
        // Add index on paid_at for year-based queries on payments
        Schema::table('payments', function (Blueprint $table) {
            $table->index('paid_at', 'payments_paid_at_index');
            $table->index(['status', 'paid_at'], 'payments_status_paid_at_index');
        });

        // Add index on bill_year for year-based queries on bills
        Schema::table('bills', function (Blueprint $table) {
            $table->index('bill_year', 'bills_bill_year_index');
            $table->index(['bill_year', 'status'], 'bills_bill_year_status_index');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex('payments_paid_at_index');
            $table->dropIndex('payments_status_paid_at_index');
        });

        Schema::table('bills', function (Blueprint $table) {
            $table->dropIndex('bills_bill_year_index');
            $table->dropIndex('bills_bill_year_status_index');
        });
    }
};
