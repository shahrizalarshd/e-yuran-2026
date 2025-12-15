<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use App\Models\AuditLog;
use App\Services\TelegramService;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $toyyibpaySettings = SystemSetting::getByGroup('toyyibpay');
        $telegramSettings = SystemSetting::getByGroup('telegram');

        return view('admin.settings.index', compact('toyyibpaySettings', 'telegramSettings'));
    }

    public function updateToyyibPay(Request $request)
    {
        $validated = $request->validate([
            'secret_key' => 'required|string',
            'category_code' => 'required|string',
            'sandbox' => 'boolean',
        ]);

        SystemSetting::set('toyyibpay_secret_key', $validated['secret_key'], 'string', 'toyyibpay', 'ToyyibPay Secret Key');
        SystemSetting::set('toyyibpay_category_code', $validated['category_code'], 'string', 'toyyibpay', 'ToyyibPay Category Code');
        SystemSetting::set('toyyibpay_sandbox', $request->boolean('sandbox') ? 'true' : 'false', 'boolean', 'toyyibpay', 'Use Sandbox Mode');

        AuditLog::logAction('update_toyyibpay_settings', 'ToyyibPay settings updated');

        return back()->with('success', __('messages.saved_successfully'));
    }

    public function updateTelegram(Request $request)
    {
        $validated = $request->validate([
            'bot_token' => 'nullable|string',
            'chat_id' => 'nullable|string',
            'enabled' => 'boolean',
        ]);

        SystemSetting::set('telegram_bot_token', $validated['bot_token'] ?? '', 'string', 'telegram', 'Telegram Bot Token');
        SystemSetting::set('telegram_chat_id', $validated['chat_id'] ?? '', 'string', 'telegram', 'Telegram Chat ID');
        SystemSetting::set('telegram_enabled', $request->boolean('enabled') ? 'true' : 'false', 'boolean', 'telegram', 'Enable Telegram Notifications');

        AuditLog::logAction('update_telegram_settings', 'Telegram settings updated');

        return back()->with('success', __('messages.saved_successfully'));
    }

    public function testTelegram()
    {
        $telegramService = new TelegramService();
        $result = $telegramService->testConnection();

        if ($result['success']) {
            return back()->with('success', 'Telegram connection successful!');
        }

        return back()->with('error', 'Telegram test failed: ' . $result['message']);
    }
}

