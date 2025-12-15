<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#16a34a">
    <title>e-Yuran - Taman Tropika Kajang</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gradient-to-br from-primary-700 via-primary-600 to-primary-800 min-h-screen">
    <div class="min-h-screen flex flex-col">
        <!-- Header -->
        <header class="p-4 lg:p-6">
            <div class="max-w-7xl mx-auto flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center shadow-lg">
                        <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                    </div>
                    <div class="text-white">
                        <h1 class="font-bold text-lg leading-tight">e-Yuran</h1>
                        <p class="text-xs text-primary-200 leading-tight">Taman Tropika Kajang</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('language.switch', 'bm') }}" class="px-3 py-1.5 rounded-lg text-sm {{ app()->getLocale() === 'bm' ? 'bg-white text-primary-700 font-medium' : 'text-white/80 hover:text-white' }}">BM</a>
                    <a href="{{ route('language.switch', 'en') }}" class="px-3 py-1.5 rounded-lg text-sm {{ app()->getLocale() === 'en' ? 'bg-white text-primary-700 font-medium' : 'text-white/80 hover:text-white' }}">EN</a>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1 flex items-center justify-center p-4 lg:p-6">
            <div class="max-w-md w-full">
                <!-- Hero -->
                <div class="text-center text-white mb-8">
                    <div class="w-24 h-24 bg-white/20 backdrop-blur-sm rounded-3xl flex items-center justify-center mx-auto mb-6 shadow-2xl">
                        <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h2 class="text-3xl lg:text-4xl font-extrabold mb-3">{{ __('Sistem e-Yuran') }}</h2>
                    <p class="text-primary-100 text-lg">{{ __('Kutipan yuran perumahan yang telus dan mudah') }}</p>
                </div>

                <!-- Action Cards -->
                <div class="space-y-4">
                    <a href="{{ route('login') }}" class="block bg-white rounded-2xl p-6 shadow-xl hover:shadow-2xl transition transform hover:-translate-y-1">
                        <div class="flex items-center gap-4">
                            <div class="w-14 h-14 bg-primary-100 rounded-xl flex items-center justify-center flex-shrink-0">
                                <svg class="w-7 h-7 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-lg font-bold text-gray-900">{{ __('messages.login') }}</h3>
                                <p class="text-gray-500 text-sm">{{ __('Log masuk ke akaun anda') }}</p>
                            </div>
                            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </div>
                    </a>

                    <a href="{{ route('register') }}" class="block bg-white/10 backdrop-blur-sm border-2 border-white/30 rounded-2xl p-6 hover:bg-white/20 transition">
                        <div class="flex items-center gap-4">
                            <div class="w-14 h-14 bg-white/20 rounded-xl flex items-center justify-center flex-shrink-0">
                                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-lg font-bold text-white">{{ __('messages.register') }}</h3>
                                <p class="text-primary-200 text-sm">{{ __('Daftar sebagai penduduk baru') }}</p>
                            </div>
                            <svg class="w-6 h-6 text-white/60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </div>
                    </a>
                </div>

                <!-- Features -->
                <div class="mt-10 grid grid-cols-3 gap-4 text-center text-white">
                    <div>
                        <div class="w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center mx-auto mb-2">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                        </div>
                        <p class="text-xs text-primary-200">{{ __('Selamat') }}</p>
                    </div>
                    <div>
                        <div class="w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center mx-auto mb-2">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <p class="text-xs text-primary-200">{{ __('24/7') }}</p>
                    </div>
                    <div>
                        <div class="w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center mx-auto mb-2">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <p class="text-xs text-primary-200">{{ __('Telus') }}</p>
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="p-4 lg:p-6 text-center text-primary-200 text-sm">
            <p>&copy; {{ date('Y') }} Taman Tropika Kajang. {{ __('Hak Cipta Terpelihara.') }}</p>
        </footer>
    </div>
</body>
</html>
