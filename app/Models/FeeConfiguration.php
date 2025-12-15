<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FeeConfiguration extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'amount',
        'effective_from',
        'effective_until',
        'description',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'effective_from' => 'date',
        'effective_until' => 'date',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function bills(): HasMany
    {
        return $this->hasMany(Bill::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeEffectiveOn($query, $date)
    {
        return $query->where('effective_from', '<=', $date)
            ->where(function ($q) use ($date) {
                $q->whereNull('effective_until')
                    ->orWhere('effective_until', '>=', $date);
            });
    }

    // Helper methods
    public static function getCurrentFee()
    {
        return self::active()
            ->effectiveOn(now())
            ->orderBy('effective_from', 'desc')
            ->first();
    }

    public static function getFeeForDate($date)
    {
        return self::active()
            ->effectiveOn($date)
            ->orderBy('effective_from', 'desc')
            ->first();
    }
}

