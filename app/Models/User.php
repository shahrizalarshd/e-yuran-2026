<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'language_preference',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    // Relationships
    public function resident(): HasOne
    {
        return $this->hasOne(Resident::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(SystemNotification::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    // Role checks
    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isTreasurer(): bool
    {
        return $this->role === 'treasurer';
    }

    public function isAuditor(): bool
    {
        return $this->role === 'auditor';
    }

    public function isResident(): bool
    {
        return $this->role === 'resident';
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, ['super_admin', 'treasurer', 'auditor']);
    }

    public function hasRole(string|array $roles): bool
    {
        if (is_string($roles)) {
            return $this->role === $roles;
        }
        return in_array($this->role, $roles);
    }

    public function canManageHouses(): bool
    {
        return $this->isSuperAdmin();
    }

    public function canManageUsers(): bool
    {
        return $this->isSuperAdmin();
    }

    public function canManageFees(): bool
    {
        return $this->isSuperAdmin();
    }

    public function canEditBills(): bool
    {
        return in_array($this->role, ['super_admin', 'treasurer']);
    }

    public function canViewReports(): bool
    {
        return in_array($this->role, ['super_admin', 'treasurer', 'auditor']);
    }

    public function canViewAuditLogs(): bool
    {
        return in_array($this->role, ['super_admin', 'auditor']);
    }

    public function canManageSettings(): bool
    {
        return $this->isSuperAdmin();
    }

    public function canVerifyUsers(): bool
    {
        return in_array($this->role, ['super_admin', 'treasurer']);
    }

    // Helper methods
    public function unreadNotifications()
    {
        return $this->notifications()->unread();
    }

    public function getUnreadNotificationCountAttribute(): int
    {
        return $this->unreadNotifications()->count();
    }

    public function getRoleBadgeClassAttribute(): string
    {
        return match ($this->role) {
            'super_admin' => 'bg-purple-100 text-purple-800',
            'treasurer' => 'bg-yellow-100 text-yellow-800',
            'auditor' => 'bg-blue-100 text-blue-800',
            'resident' => 'bg-green-100 text-green-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getRoleDisplayNameAttribute(): string
    {
        return match ($this->role) {
            'super_admin' => __('messages.super_admin'),
            'treasurer' => __('messages.treasurer'),
            'auditor' => __('messages.auditor'),
            'resident' => __('messages.resident'),
            default => $this->role,
        };
    }
}
