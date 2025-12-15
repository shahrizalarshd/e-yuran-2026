<?php

namespace App\Services;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class TelegramService
{
    private ?string $botToken;
    private ?string $chatId;
    private bool $isEnabled;

    public function __construct()
    {
        $this->botToken = SystemSetting::getTelegramBotToken();
        $this->chatId = SystemSetting::getTelegramChatId();
        $this->isEnabled = SystemSetting::isTelegramEnabled();
    }

    public function isConfigured(): bool
    {
        return $this->isEnabled && !empty($this->botToken) && !empty($this->chatId);
    }

    public function sendMessage(string $message, ?string $parseMode = 'HTML'): bool
    {
        if (!$this->isConfigured()) {
            return false;
        }

        try {
            $url = "https://api.telegram.org/bot{$this->botToken}/sendMessage";

            $response = Http::post($url, [
                'chat_id' => $this->chatId,
                'text' => $message,
                'parse_mode' => $parseMode,
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Telegram sendMessage failed', [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    public function sendErrorNotification(Throwable $exception): bool
    {
        if (!$this->isConfigured()) {
            return false;
        }

        $message = $this->formatErrorMessage($exception);
        return $this->sendMessage($message);
    }

    private function formatErrorMessage(Throwable $exception): string
    {
        $appName = config('app.name', 'e-Yuran');
        $environment = config('app.env', 'production');
        $timestamp = now()->format('Y-m-d H:i:s');

        $message = "🚨 <b>ERROR - {$appName}</b>\n\n";
        $message .= "<b>Environment:</b> {$environment}\n";
        $message .= "<b>Time:</b> {$timestamp}\n\n";
        $message .= "<b>Exception:</b> " . get_class($exception) . "\n";
        $message .= "<b>Message:</b> " . $this->truncate($exception->getMessage(), 500) . "\n";
        $message .= "<b>File:</b> " . $exception->getFile() . "\n";
        $message .= "<b>Line:</b> " . $exception->getLine() . "\n\n";
        
        // Add request info if available
        if (request()) {
            $message .= "<b>URL:</b> " . request()->fullUrl() . "\n";
            $message .= "<b>Method:</b> " . request()->method() . "\n";
            $message .= "<b>IP:</b> " . request()->ip() . "\n";
            
            if (auth()->check()) {
                $message .= "<b>User:</b> " . auth()->user()->email . "\n";
            }
        }

        return $message;
    }

    private function truncate(string $text, int $length): string
    {
        if (strlen($text) <= $length) {
            return $text;
        }
        return substr($text, 0, $length) . '...';
    }

    public function testConnection(): array
    {
        if (empty($this->botToken)) {
            return ['success' => false, 'message' => 'Bot token not configured'];
        }

        try {
            $url = "https://api.telegram.org/bot{$this->botToken}/getMe";
            $response = Http::get($url);

            if ($response->successful()) {
                $data = $response->json();
                if ($data['ok'] ?? false) {
                    // Try sending a test message
                    $testResult = $this->sendMessage("✅ Test connection successful!\n\nTimestamp: " . now()->format('Y-m-d H:i:s'));
                    
                    if ($testResult) {
                        return ['success' => true, 'message' => 'Connection successful', 'bot' => $data['result']];
                    } else {
                        return ['success' => false, 'message' => 'Bot connected but failed to send message. Check chat ID.'];
                    }
                }
            }

            return ['success' => false, 'message' => 'Invalid bot token'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}

