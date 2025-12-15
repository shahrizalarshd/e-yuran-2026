<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('messages.login') }} - e-Yuran</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-50">
    <div class="min-h-screen flex flex-col justify-center py-6 px-4">
        <div class="max-w-md w-full mx-auto">
            <!-- Logo -->
            <div class="text-center mb-8">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-3">
                    <div class="w-12 h-12 bg-primary-600 rounded-xl flex items-center justify-center shadow-lg">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                    </div>
                    <div class="text-left">
                        <h1 class="font-bold text-xl text-gray-900">e-Yuran</h1>
                        <p class="text-xs text-gray-500">Taman Tropika Kajang</p>
                    </div>
                </a>
            </div>

            <!-- Form Card -->
            <div class="bg-white rounded-2xl shadow-sm p-6 lg:p-8">
                <h2 class="text-xl font-bold text-gray-900 mb-1">{{ __('messages.login') }}</h2>
                <p class="text-gray-500 text-sm mb-6">{{ __('Log masuk ke akaun anda') }}</p>

                <!-- Session Status -->
                @if (session('status'))
                    <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg text-green-700 text-sm">
                        {{ session('status') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm">
                        {{ session('error') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="space-y-4">
                    @csrf

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.email') }}</label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus autocomplete="username" class="w-full rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500" placeholder="email@example.com">
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.password') }}</label>
                        <input type="password" name="password" id="password" required autocomplete="current-password" class="w-full rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500">
                        @error('password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Remember Me & Forgot Password -->
                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="remember" class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                            <span class="text-sm text-gray-600">{{ __('messages.remember_me') }}</span>
                        </label>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="text-sm text-primary-600 hover:text-primary-800">
                                {{ __('messages.forgot_password') }}
                            </a>
                        @endif
                    </div>

                    <button type="submit" class="w-full py-3 bg-primary-600 text-white font-semibold rounded-xl hover:bg-primary-700 transition min-h-touch">
                        {{ __('messages.login') }}
                    </button>
                </form>

                <p class="text-center text-gray-500 text-sm mt-6">
                    {{ __('Belum mempunyai akaun?') }}
                    <a href="{{ route('register') }}" class="text-primary-600 font-medium hover:text-primary-800">{{ __('messages.register') }}</a>
                </p>
            </div>

            <!-- Demo Accounts -->
            <div class="mt-6 p-4 bg-amber-50 border border-amber-200 rounded-xl">
                <p class="text-sm font-medium text-amber-800 mb-3 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ __('Akaun Demo') }}
                </p>
                <div class="space-y-2">
                    <button type="button" onclick="document.getElementById('email').value='admin@tropika.my';document.getElementById('password').value='password';" class="w-full text-left p-2 bg-white rounded-lg border border-amber-200 hover:border-amber-400 hover:bg-amber-50 transition text-xs">
                        <span class="inline-block w-20 font-semibold text-amber-900">Admin</span>
                        <span class="text-amber-700">admin@tropika.my</span>
                    </button>
                    <button type="button" onclick="document.getElementById('email').value='bendahari@tropika.my';document.getElementById('password').value='password';" class="w-full text-left p-2 bg-white rounded-lg border border-amber-200 hover:border-amber-400 hover:bg-amber-50 transition text-xs">
                        <span class="inline-block w-20 font-semibold text-amber-900">Bendahari</span>
                        <span class="text-amber-700">bendahari@tropika.my</span>
                    </button>
                    <button type="button" onclick="document.getElementById('email').value='ahmad1@gmail.com';document.getElementById('password').value='password';" class="w-full text-left p-2 bg-white rounded-lg border border-amber-200 hover:border-amber-400 hover:bg-amber-50 transition text-xs">
                        <span class="inline-block w-20 font-semibold text-amber-900">Penduduk</span>
                        <span class="text-amber-700">ahmad1@gmail.com</span>
                    </button>
                </div>
                <p class="text-xs text-amber-600 mt-2">{{ __('Klik untuk auto-fill. Password: password') }}</p>
            </div>
        </div>
    </div>
</body>
</html>
