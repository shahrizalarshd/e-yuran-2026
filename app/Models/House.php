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
        'is_registered',
        'is_active',
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

    public function bills(): HasMany
    {
        return $this->hasMany(Bill::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    // Scopes
    public function scopeRegistered($query)
    {
        return $query->where('is_registered', true);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeBillable($query)
    {
        return $query->where('is_registered', true)->where('is_active', true);
    }

    // Accessors
    public function getFullAddressAttribute(): string
    {
        return $this->house_no . ', ' . $this->street_name;
    }

    public function getIsBillableAttribute(): bool
    {
        return $this->is_registered && $this->is_active;
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
}

