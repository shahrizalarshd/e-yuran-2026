<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Update schema to Hybrid Model:
 * - Yuran Keahlian: per OCCUPANCY (reset bila owner tukar)
 * - Yuran Tahunan: per RUMAH (inherit bila owner tukar)
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. Update house_occupancies - tambah membership fields
        Schema::table('house_occupancies', function (Blueprint $table) {
            $table->boolean('is_member')->default(false)->after('is_payer');
            $table->date('membership_fee_paid_at')->nullable()->after('is_member');
            $table->decimal('membership_fee_amount', 10, 2)->nullable()->after('membership_fee_paid_at');
        });

        // 2. Update bills table - tambah paid_by_occupancy_id untuk audit trail
        Schema::table('bills', function (Blueprint $table) {
            $table->foreignId('paid_by_occupancy_id')->nullable()->after('paid_at')
                ->constrained('house_occupancies')->nullOnDelete();
        });

        // 3. Update membership_fees table - tukar house_id ke house_occupancy_id
        // Kita perlu buat column baru sebab ada data lama
        Schema::table('membership_fees', function (Blueprint $table) {
            $table->foreignId('house_occupancy_id')->nullable()->after('house_id')
                ->constrained('house_occupancies')->nullOnDelete();
        });

        // 4. Create legacy_payments table untuk import data lama
        Schema::create('legacy_payments', function (Blueprint $table) {
            $table->id();
            $table->string('house_no', 20);
            $table->enum('payment_type', ['membership', 'annual']);
            $table->year('year')->nullable(); // null untuk membership
            $table->decimal('amount', 10, 2);
            $table->date('payment_date')->nullable();
            $table->string('owner_name')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('imported_at')->useCurrent();
            
            // Linking fields
            $table->foreignId('linked_to_house_id')->nullable()
                ->constrained('houses')->nullOnDelete();
            $table->foreignId('linked_to_occupancy_id')->nullable()
                ->constrained('house_occupancies')->nullOnDelete();
            
            $table->timestamps();
            
            $table->index('house_no');
            $table->index('payment_type');
            $table->index(['house_no', 'payment_type']);
        });

        // 5. Add index untuk performance
        Schema::table('house_occupancies', function (Blueprint $table) {
            $table->index(['house_id', 'is_member', 'end_date']);
        });
    }

    public function down(): void
    {
        // Drop legacy_payments table
        Schema::dropIfExists('legacy_payments');

        // Remove house_occupancy_id from membership_fees
        Schema::table('membership_fees', function (Blueprint $table) {
            $table->dropConstrainedForeignId('house_occupancy_id');
        });

        // Remove paid_by_occupancy_id from bills
        Schema::table('bills', function (Blueprint $table) {
            $table->dropConstrainedForeignId('paid_by_occupancy_id');
        });

        // Remove membership fields from house_occupancies
        Schema::table('house_occupancies', function (Blueprint $table) {
            $table->dropIndex(['house_id', 'is_member', 'end_date']);
            $table->dropColumn(['is_member', 'membership_fee_paid_at', 'membership_fee_amount']);
        });
    }
};

