<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action',
        'model_type',
        'model_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'description',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Helper methods
    public static function log(
        string $action,
        ?Model $model = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $description = null
    ): self {
        return self::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'model_type' => $model ? get_class($model) : null,
            'model_id' => $model?->id,
            'old_values' => $oldValues ? json_encode($oldValues) : null,
            'new_values' => $newValues ? json_encode($newValues) : null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'description' => $description,
        ]);
    }

    public static function logCreate(Model $model, ?string $description = null): self
    {
        return self::log('create', $model, null, $model->toArray(), $description);
    }

    public static function logUpdate(Model $model, array $oldValues, ?string $description = null): self
    {
        return self::log('update', $model, $oldValues, $model->toArray(), $description);
    }

    public static function logDelete(Model $model, ?string $description = null): self
    {
        return self::log('delete', $model, $model->toArray(), null, $description);
    }

    public static function logAction(string $action, ?string $description = null): self
    {
        return self::log($action, null, null, null, $description);
    }

    // Scopes
    public function scopeForModel($query, string $modelType, int $modelId)
    {
        return $query->where('model_type', $modelType)->where('model_id', $modelId);
    }

    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    // Accessors
    public function getModelNameAttribute(): string
    {
        if (!$this->model_type) {
            return '-';
        }
        return class_basename($this->model_type);
    }
}

