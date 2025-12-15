<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Bill extends Model
{
    use HasFactory;

    protected $fillable = [
        'house_id',
        'fee_configuration_id',
        'bill_no',
        'bill_year',
        'bill_month',
        'amount',
        'status',
        'paid_amount',
        'due_date',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'due_date' => 'date',
        'paid_at' => 'date',
    ];

    // Relationships
    public function house(): BelongsTo
    {
        return $this->belongsTo(House::class);
    }

    public function feeConfiguration(): BelongsTo
    {
        return $this->belongsTo(FeeConfiguration::class);
    }

    public function payments(): BelongsToMany
    {
        return $this->belongsToMany(Payment::class, 'payment_bill')
            ->withPivot('amount')
            ->withTimestamps();
    }

    // Scopes
    public function scopeUnpaid($query)
    {
        return $query->where('status', 'unpaid');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeProcessing($query)
    {
        return $query->where('status', 'processing');
    }

    public function scopeForYear($query, $year)
    {
        return $query->where('bill_year', $year);
    }

    public function scopeForMonth($query, $month)
    {
        return $query->where('bill_month', $month);
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'unpaid')
            ->where('due_date', '<', now());
    }

    // Accessors
    public function getOutstandingAmountAttribute(): float
    {
        return max(0, $this->amount - $this->paid_amount);
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->status === 'unpaid' && $this->due_date < now();
    }

    public function getBillPeriodAttribute(): string
    {
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Mac', 4 => 'April',
            5 => 'Mei', 6 => 'Jun', 7 => 'Julai', 8 => 'Ogos',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Disember'
        ];
        return $months[$this->bill_month] . ' ' . $this->bill_year;
    }

    public function getBillPeriodEnAttribute(): string
    {
        $months = [
            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
            5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
        ];
        return $months[$this->bill_month] . ' ' . $this->bill_year;
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'unpaid' => 'bg-red-100 text-red-800',
            'paid' => 'bg-green-100 text-green-800',
            'processing' => 'bg-yellow-100 text-yellow-800',
            'partial' => 'bg-orange-100 text-orange-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    // Helper methods
    public static function generateBillNo(int $year, int $month, int $houseId): string
    {
        return sprintf('BIL-%04d%02d-%05d', $year, $month, $houseId);
    }

    public function markAsPaid(): void
    {
        $this->update([
            'status' => 'paid',
            'paid_amount' => $this->amount,
            'paid_at' => now(),
        ]);
    }

    public function markAsProcessing(): void
    {
        $this->update(['status' => 'processing']);
    }

    public function resetToUnpaid(): void
    {
        $this->update([
            'status' => 'unpaid',
            'paid_amount' => 0,
            'paid_at' => null,
        ]);
    }
}

