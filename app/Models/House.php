<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class House extends Model
{
    use HasFactory;

    protected $fillable = [
        'house_no',
        'street_name',
        'is_registered', // Keep for backward compatibility, akan deprecated
        'is_active',     // Keep for backward compatibility, akan deprecated
        'status',
    ];

    protected $casts = [
        'is_registered' => 'boolean',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function occupancies(): HasMany
    {
        return $this->hasMany(HouseOccupancy::class);
    }

    public function members(): HasMany
    {
        return $this->hasMany(HouseMember::class);
    }

    /**
     * Yuran Tahunan - bills attach ke rumah (MODEL HIBRID)
     */
    public function bills(): HasMany
    {
        return $this->hasMany(Bill::class);
    }

    /**
     * Annual bills alias untuk clarity
     */
    public function annualBills(): HasMany
    {
        return $this->bills();
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Legacy payments linked to this house (untuk yuran tahunan)
     */
    public function legacyPayments(): HasMany
    {
        return $this->hasMany(LegacyPayment::class, 'linked_to_house_id');
    }

    // Scopes

    /**
     * Rumah yang ada ahli aktif (occupancy dengan is_member = true)
     */
    public function scopeWithActiveMember($query)
    {
        return $query->whereHas('occupancies', function ($q) {
            $q->active()->member();
        });
    }

    /**
     * @deprecated Use scopeWithActiveMember instead
     */
    public function scopeRegistered($query)
    {
        return $query->where('is_registered', true);
    }

    /**
     * @deprecated Use scopeWithActiveMember instead
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Rumah yang boleh dijana bil (ada ahli aktif)
     * MODEL HIBRID: Bil tahunan dijana untuk rumah yang ada occupancy is_member = true
     */
    public function scopeBillable($query)
    {
        return $query->whereHas('occupancies', function ($q) {
            $q->active()->member();
        });
    }

    // Accessors
    public function getFullAddressAttribute(): string
    {
        return $this->house_no . ', ' . $this->street_name;
    }

    /**
     * Check if house has active member (derived from occupancy)
     * MODEL HIBRID: is_member derived dari occupancy aktif
     */
    public function getIsMemberAttribute(): bool
    {
        return $this->occupancies()
            ->active()
            ->member()
            ->exists();
    }

    /**
     * @deprecated Use getIsMemberAttribute instead
     */
    public function getIsBillableAttribute(): bool
    {
        return $this->is_member;
    }

    // Helper methods
    public function currentOwner()
    {
        return $this->occupancies()
            ->where('role', 'owner')
            ->whereNull('end_date')
            ->with('resident')
            ->first();
    }

    public function currentTenant()
    {
        return $this->occupancies()
            ->where('role', 'tenant')
            ->whereNull('end_date')
            ->with('resident')
            ->first();
    }

    public function currentPayer()
    {
        return $this->occupancies()
            ->where('is_payer', true)
            ->whereNull('end_date')
            ->with('resident')
            ->first();
    }

    /**
     * Get active member occupancy (occupancy with is_member = true)
     */
    public function activeMemberOccupancy()
    {
        return $this->occupancies()
            ->active()
            ->member()
            ->with('resident')
            ->first();
    }

    public function activeMembers()
    {
        return $this->members()->where('status', 'active')->with('resident');
    }

    public function pendingMembers()
    {
        return $this->members()->where('status', 'pending')->with('resident');
    }

    public function unpaidBills()
    {
        return $this->bills()->where('status', 'unpaid')->orderBy('bill_year')->orderBy('bill_month');
    }

    public function getOutstandingAmountAttribute(): float
    {
        return $this->bills()
            ->whereIn('status', ['unpaid', 'partial'])
            ->sum(\DB::raw('amount - paid_amount'));
    }

    /**
     * Get all annual payment history (legacy + new)
     * MODEL HIBRID: Gabungkan legacy payments dengan bills
     */
    public function getAnnualPaymentHistory()
    {
        $legacyPayments = $this->legacyPayments()
            ->where('payment_type', 'annual')
            ->get()
            ->map(function ($payment) {
                return [
                    'year' => $payment->year,
                    'amount' => $payment->amount,
                    'status' => 'paid',
                    'paid_at' => $payment->payment_date,
                    'source' => 'legacy',
                    'owner_name' => $payment->owner_name,
                ];
            });

        $newBills = $this->bills()
            ->get()
            ->map(function ($bill) {
                return [
                    'year' => $bill->bill_year,
                    'amount' => $bill->amount,
                    'status' => $bill->status,
                    'paid_at' => $bill->paid_at,
                    'source' => 'system',
                    'owner_name' => null,
                ];
            });

        return $legacyPayments->concat($newBills)
            ->sortByDesc('year')
            ->values();
    }
}
