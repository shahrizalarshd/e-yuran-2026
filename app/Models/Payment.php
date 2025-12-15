<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'house_id',
        'resident_id',
        'payment_no',
        'amount',
        'status',
        'payment_type',
        'toyyibpay_billcode',
        'toyyibpay_ref',
        'toyyibpay_transaction_id',
        'toyyibpay_response',
        'paid_at',
        'is_legacy',
        'payment_method',
        'legacy_reference',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'is_legacy' => 'boolean',
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

    public function bills(): BelongsToMany
    {
        return $this->belongsToMany(Bill::class, 'payment_bill')
            ->withPivot('amount')
            ->withTimestamps();
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeSuccess($query)
    {
        return $query->where('status', 'success');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeForHouse($query, $houseId)
    {
        return $query->where('house_id', $houseId);
    }

    public function scopeLegacy($query)
    {
        return $query->where('is_legacy', true);
    }

    public function scopeNotLegacy($query)
    {
        return $query->where('is_legacy', false);
    }

    // Accessors
    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'bg-yellow-100 text-yellow-800',
            'success' => 'bg-green-100 text-green-800',
            'failed' => 'bg-red-100 text-red-800',
            'cancelled' => 'bg-gray-100 text-gray-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getPaymentTypeTextAttribute(): string
    {
        return match ($this->payment_type) {
            'current_month' => __('Bulan Semasa'),
            'selected_months' => __('Bulan Terpilih'),
            'yearly' => __('Setahun'),
            default => $this->payment_type,
        };
    }

    // Helper methods
    public static function generatePaymentNo(): string
    {
        $date = now()->format('Ymd');
        $random = strtoupper(substr(md5(uniqid()), 0, 6));
        return sprintf('PAY-%s-%s', $date, $random);
    }

    public function markAsSuccess(string $transactionId, string $response = null): void
    {
        $this->update([
            'status' => 'success',
            'toyyibpay_transaction_id' => $transactionId,
            'toyyibpay_response' => $response,
            'paid_at' => now(),
        ]);

        // Mark all associated bills as paid
        foreach ($this->bills as $bill) {
            $bill->markAsPaid();
        }
    }

    public function markAsFailed(string $response = null): void
    {
        $this->update([
            'status' => 'failed',
            'toyyibpay_response' => $response,
        ]);

        // Reset bills to unpaid
        foreach ($this->bills as $bill) {
            if ($bill->status === 'processing') {
                $bill->resetToUnpaid();
            }
        }
    }

    public function markAsCancelled(): void
    {
        $this->update(['status' => 'cancelled']);

        // Reset bills to unpaid
        foreach ($this->bills as $bill) {
            if ($bill->status === 'processing') {
                $bill->resetToUnpaid();
            }
        }
    }
}

