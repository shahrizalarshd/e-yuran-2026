<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * LegacyPayment Model - Data Pembayaran Lama (2017-2024)
 * 
 * Untuk import data dari Excel dan link dengan entiti baru
 * Data ini adalah READ-ONLY selepas import
 */
class LegacyPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'house_no',
        'payment_type',    // 'membership' atau 'annual'
        'year',            // null untuk membership
        'amount',
        'payment_date',
        'owner_name',
        'notes',
        'imported_at',
        'linked_to_house_id',        // Untuk yuran tahunan
        'linked_to_occupancy_id',    // Untuk yuran keahlian
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
        'imported_at' => 'datetime',
        'year' => 'integer',
    ];

    // Relationships

    /**
     * Linked house (untuk yuran tahunan)
     */
    public function linkedHouse(): BelongsTo
    {
        return $this->belongsTo(House::class, 'linked_to_house_id');
    }

    /**
     * Linked occupancy (untuk yuran keahlian)
     */
    public function linkedOccupancy(): BelongsTo
    {
        return $this->belongsTo(HouseOccupancy::class, 'linked_to_occupancy_id');
    }

    // Scopes

    /**
     * Filter untuk yuran keahlian
     */
    public function scopeMembership($query)
    {
        return $query->where('payment_type', 'membership');
    }

    /**
     * Filter untuk yuran tahunan
     */
    public function scopeAnnual($query)
    {
        return $query->where('payment_type', 'annual');
    }

    /**
     * Filter yang sudah linked
     */
    public function scopeLinked($query)
    {
        return $query->where(function ($q) {
            $q->whereNotNull('linked_to_house_id')
                ->orWhereNotNull('linked_to_occupancy_id');
        });
    }

    /**
     * Filter yang belum linked
     */
    public function scopeUnlinked($query)
    {
        return $query->whereNull('linked_to_house_id')
            ->whereNull('linked_to_occupancy_id');
    }

    /**
     * Filter by house_no
     */
    public function scopeForHouseNo($query, string $houseNo)
    {
        return $query->where('house_no', $houseNo);
    }

    /**
     * Filter by year
     */
    public function scopeForYear($query, int $year)
    {
        return $query->where('year', $year);
    }

    // Accessors

    public function getIsLinkedAttribute(): bool
    {
        return !is_null($this->linked_to_house_id) || !is_null($this->linked_to_occupancy_id);
    }

    public function getPaymentTypeDisplayAttribute(): string
    {
        return match ($this->payment_type) {
            'membership' => 'Yuran Keahlian',
            'annual' => 'Yuran Tahunan',
            default => $this->payment_type,
        };
    }

    // Helper methods

    /**
     * Link payment to house (untuk yuran tahunan)
     */
    public function linkToHouse(House $house): void
    {
        if ($this->payment_type !== 'annual') {
            throw new \Exception('Only annual payments can be linked to house');
        }

        $this->update(['linked_to_house_id' => $house->id]);
    }

    /**
     * Link payment to occupancy (untuk yuran keahlian)
     */
    public function linkToOccupancy(HouseOccupancy $occupancy): void
    {
        if ($this->payment_type !== 'membership') {
            throw new \Exception('Only membership payments can be linked to occupancy');
        }

        $this->update(['linked_to_occupancy_id' => $occupancy->id]);
    }

    /**
     * Import from array (untuk batch import dari Excel)
     */
    public static function importFromArray(array $data): self
    {
        return self::create([
            'house_no' => $data['house_no'],
            'payment_type' => $data['payment_type'],
            'year' => $data['year'] ?? null,
            'amount' => $data['amount'],
            'payment_date' => $data['payment_date'] ?? null,
            'owner_name' => $data['owner_name'] ?? null,
            'notes' => $data['notes'] ?? null,
            'imported_at' => now(),
        ]);
    }

    /**
     * Auto-link annual payments to houses by house_no
     */
    public static function autoLinkAnnualPaymentsToHouses(): int
    {
        $linked = 0;

        $unlinkedPayments = self::annual()
            ->whereNull('linked_to_house_id')
            ->get();

        foreach ($unlinkedPayments as $payment) {
            $house = House::where('house_no', $payment->house_no)->first();
            if ($house) {
                $payment->update(['linked_to_house_id' => $house->id]);
                $linked++;
            }
        }

        return $linked;
    }

    /**
     * Get summary by house_no
     */
    public static function getSummaryByHouseNo(string $houseNo): array
    {
        $payments = self::forHouseNo($houseNo)->get();

        return [
            'house_no' => $houseNo,
            'membership' => $payments->where('payment_type', 'membership')->values(),
            'annual' => $payments->where('payment_type', 'annual')
                ->sortByDesc('year')
                ->values(),
            'total_membership' => $payments->where('payment_type', 'membership')->sum('amount'),
            'total_annual' => $payments->where('payment_type', 'annual')->sum('amount'),
        ];
    }
}

