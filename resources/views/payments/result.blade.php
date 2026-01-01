<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $success ? __('messages.payment_success') : __('messages.payment_failed') }} - {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full">
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            {{-- Header with status icon --}}
            <div class="p-8 text-center {{ $success === true ? 'bg-green-500' : ($success === false ? 'bg-red-500' : 'bg-yellow-500') }}">
                @if($success === true)
                    <div class="w-20 h-20 mx-auto bg-white rounded-full flex items-center justify-center">
                        <svg class="w-12 h-12 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <h1 class="mt-4 text-2xl font-bold text-white">{{ __('messages.payment_success') }}</h1>
                @elseif($success === false)
                    <div class="w-20 h-20 mx-auto bg-white rounded-full flex items-center justify-center">
                        <svg class="w-12 h-12 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </div>
                    <h1 class="mt-4 text-2xl font-bold text-white">{{ __('messages.payment_failed') }}</h1>
                @else
                    <div class="w-20 h-20 mx-auto bg-white rounded-full flex items-center justify-center">
                        <svg class="w-12 h-12 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h1 class="mt-4 text-2xl font-bold text-white">{{ __('Dalam Proses') }}</h1>
                @endif
            </div>

            {{-- Payment details --}}
            <div class="p-6">
                <p class="text-center text-gray-600 mb-6">{{ $message }}</p>

                @if($payment)
                    <div class="bg-gray-50 rounded-lg p-4 mb-6">
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-gray-500">{{ __('No. Pembayaran') }}</span>
                                <span class="font-medium text-gray-900">{{ $payment->payment_no }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">{{ __('Jumlah') }}</span>
                                <span class="font-bold text-lg text-gray-900">RM {{ number_format($payment->amount, 2) }}</span>
                            </div>
                            @if($payment->transaction_ref)
                                <div class="flex justify-between">
                                    <span class="text-gray-500">{{ __('No. Transaksi') }}</span>
                                    <span class="font-medium text-gray-900">{{ $payment->transaction_ref }}</span>
                                </div>
                            @endif
                            <div class="flex justify-between">
                                <span class="text-gray-500">{{ __('Tarikh') }}</span>
                                <span class="font-medium text-gray-900">{{ $payment->paid_at ? $payment->paid_at->format('d/m/Y H:i') : now()->format('d/m/Y H:i') }}</span>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Action buttons --}}
                <div class="space-y-3">
                    @auth
                        <a href="{{ route('resident.payments.index') }}" 
                           class="block w-full text-center px-6 py-3 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 transition">
                            {{ __('Lihat Sejarah Pembayaran') }}
                        </a>
                        <a href="{{ route('resident.dashboard') }}" 
                           class="block w-full text-center px-6 py-3 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300 transition">
                            {{ __('Kembali ke Papan Pemuka') }}
                        </a>
                    @else
                        <a href="{{ route('login') }}" 
                           class="block w-full text-center px-6 py-3 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 transition">
                            {{ __('Log Masuk untuk Lihat Butiran') }}
                        </a>
                        <a href="{{ route('home') }}" 
                           class="block w-full text-center px-6 py-3 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300 transition">
                            {{ __('Kembali ke Laman Utama') }}
                        </a>
                    @endauth
                </div>
            </div>

            {{-- Footer --}}
            <div class="px-6 py-4 bg-gray-50 border-t text-center">
                <p class="text-sm text-gray-500">
                    {{ config('app.name') }} &copy; {{ date('Y') }}
                </p>
            </div>
        </div>
    </div>
</body>
</html>


