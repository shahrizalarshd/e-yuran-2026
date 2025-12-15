<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_payer' => 'boolean',
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

    // Accessors
    public function getIsActiveAttribute(): bool
    {
        return is_null($this->end_date);
    }

    // Business rules
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
}

