<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Resident extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'email',
        'phone',
        'ic_number',
        'language_preference',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function occupancies(): HasMany
    {
        return $this->hasMany(HouseOccupancy::class);
    }

    public function houseMemberships(): HasMany
    {
        return $this->hasMany(HouseMember::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    // Scopes
    public function scopeWithActiveOccupancy($query)
    {
        return $query->whereHas('occupancies', function ($q) {
            $q->whereNull('end_date');
        });
    }

    // Helper methods
    public function currentOccupancy()
    {
        return $this->occupancies()
            ->whereNull('end_date')
            ->with('house')
            ->first();
    }

    public function activeMemberships()
    {
        return $this->houseMemberships()
            ->where('status', 'active')
            ->with('house');
    }

    public function getHousesAttribute()
    {
        return $this->houseMemberships()
            ->where('status', 'active')
            ->with('house')
            ->get()
            ->pluck('house');
    }

    public function canViewBillsFor(House $house): bool
    {
        $membership = $this->houseMemberships()
            ->where('house_id', $house->id)
            ->where('status', 'active')
            ->first();

        return $membership && $membership->can_view_bills;
    }

    public function canPayFor(House $house): bool
    {
        $membership = $this->houseMemberships()
            ->where('house_id', $house->id)
            ->where('status', 'active')
            ->first();

        return $membership && $membership->can_pay;
    }
}

