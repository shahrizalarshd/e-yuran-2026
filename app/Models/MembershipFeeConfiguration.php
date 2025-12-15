<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MembershipFeeConfiguration extends Model
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
    public static function getCurrentFee(): ?self
    {
        return self::active()
            ->effectiveOn(now())
            ->orderBy('effective_from', 'desc')
            ->first();
    }

    public static function getCurrentAmount(): float
    {
        $config = self::getCurrentFee();
        return $config ? (float) $config->amount : 20.00; // Default RM20
    }
}

