<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SystemSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'description',
    ];

    // Cache key prefix
    private const CACHE_PREFIX = 'system_setting_';
    private const CACHE_TTL = 3600; // 1 hour

    // Helper methods
    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::remember(self::CACHE_PREFIX . $key, self::CACHE_TTL, function () use ($key, $default) {
            $setting = self::where('key', $key)->first();
            
            if (!$setting) {
                return $default;
            }

            return self::castValue($setting->value, $setting->type);
        });
    }

    public static function set(string $key, mixed $value, string $type = 'string', string $group = 'general', ?string $description = null): void
    {
        $stringValue = is_array($value) ? json_encode($value) : (string) $value;

        self::updateOrCreate(
            ['key' => $key],
            [
                'value' => $stringValue,
                'type' => $type,
                'group' => $group,
                'description' => $description,
            ]
        );

        Cache::forget(self::CACHE_PREFIX . $key);
    }

    public static function getByGroup(string $group): array
    {
        return self::where('group', $group)
            ->get()
            ->mapWithKeys(function ($setting) {
                return [$setting->key => self::castValue($setting->value, $setting->type)];
            })
            ->toArray();
    }

    private static function castValue(?string $value, string $type): mixed
    {
        if ($value === null) {
            return null;
        }

        return match ($type) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $value,
            'float' => (float) $value,
            'json', 'array' => json_decode($value, true),
            default => $value,
        };
    }

    // ToyyibPay settings
    public static function getToyyibPaySecretKey(): ?string
    {
        return self::get('toyyibpay_secret_key');
    }

    public static function getToyyibPayCategoryCode(): ?string
    {
        return self::get('toyyibpay_category_code');
    }

    public static function isToyyibPaySandbox(): bool
    {
        return self::get('toyyibpay_sandbox', true);
    }

    // Telegram settings
    public static function getTelegramBotToken(): ?string
    {
        return self::get('telegram_bot_token');
    }

    public static function getTelegramChatId(): ?string
    {
        return self::get('telegram_chat_id');
    }

    public static function isTelegramEnabled(): bool
    {
        return self::get('telegram_enabled', false);
    }
}

