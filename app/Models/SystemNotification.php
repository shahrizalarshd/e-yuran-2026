<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SystemNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'message',
        'type',
        'action_url',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }

    public function scopeRecent($query, int $limit = 10)
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }

    // Helper methods
    public function markAsRead(): void
    {
        if (!$this->is_read) {
            $this->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
        }
    }

    public static function notify(User $user, string $title, string $message, string $type = 'info', ?string $actionUrl = null): self
    {
        return self::create([
            'user_id' => $user->id,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'action_url' => $actionUrl,
        ]);
    }

    public static function notifySuccess(User $user, string $title, string $message, ?string $actionUrl = null): self
    {
        return self::notify($user, $title, $message, 'success', $actionUrl);
    }

    public static function notifyWarning(User $user, string $title, string $message, ?string $actionUrl = null): self
    {
        return self::notify($user, $title, $message, 'warning', $actionUrl);
    }

    public static function notifyError(User $user, string $title, string $message, ?string $actionUrl = null): self
    {
        return self::notify($user, $title, $message, 'error', $actionUrl);
    }

    // Accessors
    public function getTypeIconAttribute(): string
    {
        return match ($this->type) {
            'success' => 'check-circle',
            'warning' => 'exclamation-triangle',
            'error' => 'x-circle',
            default => 'information-circle',
        };
    }

    public function getTypeBgClassAttribute(): string
    {
        return match ($this->type) {
            'success' => 'bg-green-50',
            'warning' => 'bg-yellow-50',
            'error' => 'bg-red-50',
            default => 'bg-blue-50',
        };
    }
}

