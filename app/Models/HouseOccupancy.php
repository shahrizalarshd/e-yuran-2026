<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HouseOccupancy extends Model
{
    use HasFactory;

    protected $fillable = [
        'house_id',
        'resident_id',
        'role',
        'start_date',
        'end_date',
        'is_payer',
        // Membership fields (MODEL HIBRID)
        'is_member',
        'membership_fee_paid_at',
        'membership_fee_amount',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_payer' => 'boolean',
        'is_member' => 'boolean',
        'membership_fee_paid_at' => 'date',
        'membership_fee_amount' => 'decimal:2',
    ];

    // Relationships
    public function house(): BelongsTo
    {
        return $this->belongsTo(House::class);
    }

    public function resident(): BelongsTo
    {
        return $this->belongsTo(Resident::class);
    }

    /**
     * Membership bills for this occupancy (Yuran Keahlian)
     * MODEL HIBRID: Yuran keahlian attach ke occupancy
     */
    public function membershipFees(): HasMany
    {
        return $this->hasMany(MembershipFee::class, 'house_occupancy_id');
    }

    /**
     * Bills paid by this occupancy (untuk audit trail)
     */
    public function paidBills(): HasMany
    {
        return $this->hasMany(Bill::class, 'paid_by_occupancy_id');
    }

    /**
     * Legacy payments linked to this occupancy (untuk yuran keahlian)
     */
    public function legacyMembershipPayments(): HasMany
    {
        return $this->hasMany(LegacyPayment::class, 'linked_to_occupancy_id')
            ->where('payment_type', 'membership');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->whereNull('end_date');
    }

    public function scopeOwners($query)
    {
        return $query->where('role', 'owner');
    }

    public function scopeTenants($query)
    {
        return $query->where('role', 'tenant');
    }

    public function scopePayers($query)
    {
        return $query->where('is_payer', true);
    }

    /**
     * Occupancy yang berdaftar sebagai ahli PPTT
     */
    public function scopeMember($query)
    {
        return $query->where('is_member', true);
    }

    /**
     * Occupancy yang belum daftar ahli
     */
    public function scopeNotMember($query)
    {
        return $query->where('is_member', false);
    }

    // Accessors
    public function getIsActiveAttribute(): bool
    {
        return is_null($this->end_date);
    }

    /**
     * Check if membership fee has been paid
     */
    public function getMembershipPaidAttribute(): bool
    {
        return !is_null($this->membership_fee_paid_at);
    }

    // Business rules

    /**
     * Register as PPTT member and pay membership fee
     * MODEL HIBRID: Yuran keahlian per occupancy
     */
    public function registerAsMember(float $amount): void
    {
        $this->update([
            'is_member' => true,
            'membership_fee_paid_at' => now(),
            'membership_fee_amount' => $amount,
        ]);

        // Log audit
        AuditLog::logAction(
            'membership_registered',
            "Occupancy {$this->id} registered as member. Amount: RM " . number_format($amount, 2),
            null,
            $this
        );
    }

    /**
     * End this occupancy (bila owner jual rumah)
     * MODEL HIBRID: Keahlian RESET bila occupancy tamat
     */
    public function endOccupancy(?\DateTime $endDate = null): void
    {
        $this->update([
            'end_date' => $endDate ?? now(),
            'is_payer' => false,
        ]);

        // Log audit
        AuditLog::logAction(
            'occupancy_ended',
            "Occupancy {$this->id} ended. Membership status was: " . ($this->is_member ? 'Active' : 'Not member'),
            null,
            $this
        );
    }

    /**
     * Set payer for house
     */
    public static function setPayerForHouse(House $house, Resident $resident): void
    {
        // Remove payer status from all current occupancies
        self::where('house_id', $house->id)
            ->whereNull('end_date')
            ->update(['is_payer' => false]);

        // Set new payer
        self::where('house_id', $house->id)
            ->where('resident_id', $resident->id)
            ->whereNull('end_date')
            ->update(['is_payer' => true]);
    }

    /**
     * Get membership payment history for this occupancy
     * MODEL HIBRID: Gabungkan legacy + new
     */
    public function getMembershipHistory()
    {
        $legacyPayments = $this->legacyMembershipPayments()
            ->get()
            ->map(function ($payment) {
                return [
                    'amount' => $payment->amount,
                    'paid_at' => $payment->payment_date,
                    'source' => 'legacy',
                    'owner_name' => $payment->owner_name,
                ];
            });

        $newPayments = collect();
        if ($this->membership_fee_paid_at) {
            $newPayments->push([
                'amount' => $this->membership_fee_amount,
                'paid_at' => $this->membership_fee_paid_at,
                'source' => 'system',
                'owner_name' => $this->resident?->name,
            ]);
        }

        return $legacyPayments->concat($newPayments)
            ->sortByDesc('paid_at')
            ->values();
    }
}
