<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * MembershipFee Model - Yuran Keahlian
 * MODEL HIBRID: Bil attach ke OCCUPANCY (house_occupancy_id)
 * 
 * Bila owner tukar, keahlian RESET - owner baru perlu daftar semula
 */
class MembershipFee extends Model
{
    use HasFactory;

    protected $fillable = [
        'house_id',              // Legacy field, akan deprecated
        'house_occupancy_id',    // MODEL HIBRID: Link ke occupancy
        'resident_id',
        'amount',
        'status',
        'paid_at',
        'is_legacy',
        'legacy_owner_name',
        'fee_year',
        'payment_reference',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'date',
        'is_legacy' => 'boolean',
        'fee_year' => 'integer',
    ];

    // Relationships

    /**
     * @deprecated Use houseOccupancy() instead
     */
    public function house(): BelongsTo
    {
        return $this->belongsTo(House::class);
    }

    /**
     * Occupancy yang bayar yuran keahlian ini
     * MODEL HIBRID: Yuran keahlian per occupancy
     */
    public function houseOccupancy(): BelongsTo
    {
        return $this->belongsTo(HouseOccupancy::class);
    }

    public function resident(): BelongsTo
    {
        return $this->belongsTo(Resident::class);
    }

    // Scopes
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeUnpaid($query)
    {
        return $query->where('status', 'unpaid');
    }

    public function scopeLegacy($query)
    {
        return $query->where('is_legacy', true);
    }

    public function scopeForYear($query, $year)
    {
        return $query->where('fee_year', $year);
    }

    /**
     * Filter by occupancy
     */
    public function scopeForOccupancy($query, $occupancyId)
    {
        return $query->where('house_occupancy_id', $occupancyId);
    }

    // Accessors
    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'paid' => 'bg-green-100 text-green-800',
            'unpaid' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getOwnerNameAttribute(): string
    {
        // First try to get from occupancy's resident
        if ($this->houseOccupancy && $this->houseOccupancy->resident) {
            return $this->houseOccupancy->resident->name;
        }
        // Then try resident directly
        if ($this->resident) {
            return $this->resident->name;
        }
        // Finally use legacy owner name
        return $this->legacy_owner_name ?? 'Unknown';
    }

    /**
     * Get the house through occupancy
     */
    public function getHouseViaOccupancyAttribute(): ?House
    {
        return $this->houseOccupancy?->house;
    }

    // Helper methods
    public function markAsPaid(?string $reference = null): void
    {
        $this->update([
            'status' => 'paid',
            'paid_at' => now(),
            'payment_reference' => $reference,
        ]);

        // Also update the occupancy's membership status
        if ($this->houseOccupancy) {
            $this->houseOccupancy->update([
                'is_member' => true,
                'membership_fee_paid_at' => now(),
                'membership_fee_amount' => $this->amount,
            ]);
        }
    }

    /**
     * Create membership fee for an occupancy
     * MODEL HIBRID: Yuran keahlian per occupancy
     */
    public static function createForOccupancy(HouseOccupancy $occupancy, float $amount): self
    {
        return self::create([
            'house_id' => $occupancy->house_id,
            'house_occupancy_id' => $occupancy->id,
            'resident_id' => $occupancy->resident_id,
            'amount' => $amount,
            'status' => 'unpaid',
            'fee_year' => now()->year,
            'is_legacy' => false,
        ]);
    }
}
