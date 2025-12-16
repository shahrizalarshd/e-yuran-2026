<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/favicon.png') }}">

    <title>{{ __('Sahkan Emel') }} - e-Yuran</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-50">
    <div class="min-h-screen flex flex-col justify-center py-6 px-4">
        <div class="max-w-md w-full mx-auto">
            <!-- Logo -->
            <div class="text-center mb-8">
                <a href="{{ route('home') }}" class="inline-block">
                    <img src="{{ asset('images/logo.png') }}" alt="Persatuan Penduduk Taman Tropika Kajang" class="h-24 w-auto mx-auto">
                </a>
            </div>

            <!-- Card -->
            <div class="bg-white rounded-2xl shadow-sm p-6 lg:p-8">
                <!-- Icon -->
                <div class="text-center mb-6">
                    <div class="w-16 h-16 bg-primary-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <h2 class="text-xl font-bold text-gray-900 mb-2">{{ __('Sahkan Alamat Emel Anda') }}</h2>
                    <p class="text-gray-500 text-sm">
                        {{ __('Terima kasih kerana mendaftar! Sila sahkan alamat emel anda dengan mengklik pautan yang kami hantar. Jika tidak menerimanya, kami boleh hantar semula.') }}
                    </p>
    </div>

    @if (session('status') == 'verification-link-sent')
                    <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-xl">
                        <div class="flex items-center gap-3">
                            <div class="flex-shrink-0">
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <p class="text-sm text-green-700">
                                {{ __('Pautan pengesahan baru telah dihantar ke alamat emel anda.') }}
                            </p>
                        </div>
        </div>
    @endif

                <!-- Actions -->
                <div class="space-y-3">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
                        <button type="submit" class="w-full py-3 bg-primary-600 text-white font-semibold rounded-xl hover:bg-primary-700 transition flex items-center justify-center gap-2 min-h-touch">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                            </svg>
                            {{ __('Hantar Semula Emel Pengesahan') }}
                        </button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
                        <button type="submit" class="w-full py-3 bg-white text-gray-700 font-medium rounded-xl border border-gray-300 hover:bg-gray-50 transition flex items-center justify-center gap-2 min-h-touch">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            {{ __('Log Keluar') }}
            </button>
        </form>
                </div>

                <!-- Help Text -->
                <div class="mt-6 p-4 bg-gray-50 rounded-xl">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-gray-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div class="text-sm text-gray-600">
                            <p class="font-medium text-gray-700 mb-1">{{ __('Tidak terima emel?') }}</p>
                            <ul class="list-disc list-inside space-y-1 text-xs">
                                <li>{{ __('Semak folder spam atau junk') }}</li>
                                <li>{{ __('Pastikan alamat emel betul') }}</li>
                                <li>{{ __('Cuba hantar semula selepas beberapa minit') }}</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
