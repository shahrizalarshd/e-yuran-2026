<x-app-layout>
    <x-slot name="title">{{ __('messages.settings') }}</x-slot>

    <div class="max-w-2xl mx-auto space-y-6">
        <!-- ToyyibPay Settings -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="p-4 lg:p-6 border-b border-gray-100">
                <h2 class="text-lg font-semibold text-gray-900">{{ __('messages.toyyibpay_settings') }}</h2>
                <p class="text-sm text-gray-500 mt-1">{{ __('Konfigurasi payment gateway ToyyibPay') }}</p>
            </div>

            <form action="{{ route('admin.settings.toyyibpay') }}" method="POST" class="p-4 lg:p-6 space-y-6">
                @csrf

                <div>
                    <label for="secret_key" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.secret_key') }} <span class="text-red-500">*</span></label>
                    <input type="password" name="secret_key" id="secret_key" value="{{ $toyyibpaySettings['toyyibpay_secret_key'] ?? '' }}" required class="w-full rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500" placeholder="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx">
                </div>

                <div>
                    <label for="category_code" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.category_code') }} <span class="text-red-500">*</span></label>
                    <input type="text" name="category_code" id="category_code" value="{{ $toyyibpaySettings['toyyibpay_category_code'] ?? '' }}" required class="w-full rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500" placeholder="xxxxxxxx">
                </div>

                <label class="flex items-center gap-3 p-4 bg-gray-50 rounded-lg cursor-pointer hover:bg-gray-100 min-h-touch">
                    <input type="checkbox" name="sandbox" value="1" {{ ($toyyibpaySettings['toyyibpay_sandbox'] ?? true) ? 'checked' : '' }} class="w-5 h-5 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                    <div>
                        <p class="font-medium text-gray-900">{{ __('messages.sandbox_mode') }}</p>
                        <p class="text-sm text-gray-500">{{ __('Gunakan persekitaran sandbox untuk ujian') }}</p>
                    </div>
                </label>

                <button type="submit" class="w-full py-3 bg-primary-600 text-white font-medium rounded-lg hover:bg-primary-700 transition min-h-touch">
                    {{ __('messages.save') }}
                </button>
            </form>
        </div>

        <!-- Telegram Settings -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="p-4 lg:p-6 border-b border-gray-100">
                <h2 class="text-lg font-semibold text-gray-900">{{ __('messages.telegram_settings') }}</h2>
                <p class="text-sm text-gray-500 mt-1">{{ __('Konfigurasi notifikasi ralat melalui Telegram') }}</p>
            </div>

            <form action="{{ route('admin.settings.telegram') }}" method="POST" class="p-4 lg:p-6 space-y-6">
                @csrf

                <div>
                    <label for="bot_token" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.bot_token') }}</label>
                    <input type="password" name="bot_token" id="bot_token" value="{{ $telegramSettings['telegram_bot_token'] ?? '' }}" class="w-full rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500" placeholder="123456789:ABCdefGHIjklMNOpqrSTUvwxYZ">
                </div>

                <div>
                    <label for="chat_id" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.chat_id') }}</label>
                    <input type="text" name="chat_id" id="chat_id" value="{{ $telegramSettings['telegram_chat_id'] ?? '' }}" class="w-full rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500" placeholder="-123456789">
                </div>

                <label class="flex items-center gap-3 p-4 bg-gray-50 rounded-lg cursor-pointer hover:bg-gray-100 min-h-touch">
                    <input type="checkbox" name="enabled" value="1" {{ ($telegramSettings['telegram_enabled'] ?? false) ? 'checked' : '' }} class="w-5 h-5 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                    <div>
                        <p class="font-medium text-gray-900">{{ __('messages.enable_telegram') }}</p>
                        <p class="text-sm text-gray-500">{{ __('Aktifkan notifikasi ralat ke Telegram') }}</p>
                    </div>
                </label>

                <div class="flex gap-3">
                    <button type="submit" class="flex-1 py-3 bg-primary-600 text-white font-medium rounded-lg hover:bg-primary-700 transition min-h-touch">
                        {{ __('messages.save') }}
                    </button>
                </div>
            </form>

            <div class="px-4 lg:px-6 pb-4 lg:pb-6">
                <form action="{{ route('admin.settings.telegram.test') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full py-3 bg-gray-100 text-gray-700 font-medium rounded-lg hover:bg-gray-200 transition min-h-touch">
                        {{ __('messages.test_connection') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

