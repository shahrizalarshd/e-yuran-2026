<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#16a34a">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/favicon.png') }}">

    <title>@hasSection('title')@yield('title')@else{{ $title ?? config('app.name', 'e-Yuran') }}@endif - Taman Tropika Kajang</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="font-sans antialiased bg-gray-50" x-data="{ sidebarOpen: false, mobileMenuOpen: false }">
    <div class="min-h-screen">
        <!-- Mobile Header -->
        <header class="lg:hidden fixed top-0 left-0 right-0 z-40 bg-primary-600 text-white safe-area-inset-top">
            <div class="flex items-center justify-between px-4 h-14">
                <button @click="mobileMenuOpen = true" class="p-2 -ml-2 rounded-lg hover:bg-primary-700 min-h-touch min-w-touch flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
                <h1 class="text-lg font-semibold">@hasSection('page-title')@yield('page-title')@else{{ $title ?? 'e-Yuran' }}@endif</h1>
                <a href="{{ route('notifications.index') }}" class="p-2 -mr-2 rounded-lg hover:bg-primary-700 min-h-touch min-w-touch flex items-center justify-center relative">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    @if(auth()->user()->unread_notification_count > 0)
                        <span class="absolute top-1 right-1 w-5 h-5 bg-red-500 rounded-full text-xs flex items-center justify-center">
                            {{ auth()->user()->unread_notification_count > 9 ? '9+' : auth()->user()->unread_notification_count }}
                        </span>
                    @endif
                </a>
            </div>
        </header>

        <!-- Mobile Slide Menu -->
        <div x-show="mobileMenuOpen" x-cloak class="lg:hidden fixed inset-0 z-50">
            <div x-show="mobileMenuOpen" x-transition:enter="transition-opacity ease-linear duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity ease-linear duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" @click="mobileMenuOpen = false" class="fixed inset-0 bg-gray-900/80"></div>
            
            <div x-show="mobileMenuOpen" x-transition:enter="transition ease-in-out duration-300 transform" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in-out duration-300 transform" x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full" class="fixed inset-y-0 left-0 w-full max-w-xs bg-white shadow-xl">
                @include('layouts.partials.mobile-menu')
            </div>
        </div>

        <!-- Desktop Sidebar -->
        <aside class="hidden lg:fixed lg:inset-y-0 lg:left-0 lg:z-40 lg:flex lg:w-64 lg:flex-col">
            @include('layouts.partials.sidebar')
        </aside>

        <!-- Main Content -->
        <main class="lg:pl-64 pt-14 lg:pt-0 pb-20 lg:pb-0 min-h-screen">
            <!-- Desktop Header -->
            <header class="hidden lg:block sticky top-0 z-30 bg-white border-b border-gray-200">
                <div class="flex items-center justify-between px-6 h-16">
                    <h1 class="text-xl font-semibold text-gray-900">@hasSection('page-title')@yield('page-title')@else{{ $title ?? 'Dashboard' }}@endif</h1>
                    <div class="flex items-center gap-4">
                        <!-- Language Switcher -->
                        <div class="flex items-center gap-1 text-sm">
                            <a href="{{ route('language.switch', 'bm') }}" class="px-2 py-1 rounded {{ app()->getLocale() === 'bm' ? 'bg-primary-100 text-primary-700 font-medium' : 'text-gray-500 hover:text-gray-700' }}">BM</a>
                            <a href="{{ route('language.switch', 'en') }}" class="px-2 py-1 rounded {{ app()->getLocale() === 'en' ? 'bg-primary-100 text-primary-700 font-medium' : 'text-gray-500 hover:text-gray-700' }}">EN</a>
                        </div>
                        <!-- Notifications -->
                        <a href="{{ route('notifications.index') }}" class="relative p-2 rounded-lg hover:bg-gray-100">
                            <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                            @if(auth()->user()->unread_notification_count > 0)
                                <span class="absolute top-0 right-0 w-5 h-5 bg-red-500 rounded-full text-xs text-white flex items-center justify-center">
                                    {{ auth()->user()->unread_notification_count > 9 ? '9+' : auth()->user()->unread_notification_count }}
                                </span>
                            @endif
                        </a>
                        <!-- User Menu -->
                        <div x-data="{ open: false }" class="relative">
                            <button @click="open = !open" class="flex items-center gap-2 p-2 rounded-lg hover:bg-gray-100">
                                <div class="w-8 h-8 bg-primary-100 rounded-full flex items-center justify-center">
                                    <span class="text-primary-700 font-medium text-sm">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                                </div>
                                <span class="text-sm font-medium text-gray-700">{{ auth()->user()->name }}</span>
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            <div x-show="open" @click.away="open = false" x-cloak class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-1">
                                <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">{{ __('messages.profile') }}</a>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">{{ __('messages.logout') }}</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <div class="p-4 lg:p-6">
                <!-- Flash Messages -->
                @if(session('success'))
                    <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg text-green-800" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)">
                        {{ session('success') }}
                    </div>
                @endif
                @if(session('error'))
                    <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-red-800" x-data="{ show: true }" x-show="show">
                        {{ session('error') }}
                    </div>
                @endif
                @if(session('warning'))
                    <div class="mb-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg text-yellow-800" x-data="{ show: true }" x-show="show">
                        {{ session('warning') }}
                    </div>
                @endif
                @if(session('info'))
                    <div class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-lg text-blue-800" x-data="{ show: true }" x-show="show">
                        {{ session('info') }}
                    </div>
                @endif

                @isset($slot)
                    {{ $slot }}
                @else
                    @yield('content')
                @endisset
            </div>
        </main>

        <!-- Mobile Bottom Navigation (for residents) -->
        @if(auth()->user()->isResident())
            <nav class="lg:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 safe-area-inset-bottom z-40">
                <div class="flex justify-around items-center h-16">
                    <a href="{{ route('resident.dashboard') }}" class="flex flex-col items-center justify-center flex-1 min-h-touch {{ request()->routeIs('resident.dashboard') ? 'text-primary-600' : 'text-gray-500' }}">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        <span class="text-xs mt-1">{{ __('messages.home') }}</span>
                    </a>
                    <a href="{{ route('resident.bills.index') }}" class="flex flex-col items-center justify-center flex-1 min-h-touch {{ request()->routeIs('resident.bills.*') ? 'text-primary-600' : 'text-gray-500' }}">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <span class="text-xs mt-1">{{ __('messages.bills') }}</span>
                    </a>
                    <a href="{{ route('resident.payments.create') }}" class="flex flex-col items-center justify-center flex-1 min-h-touch">
                        <div class="w-12 h-12 -mt-6 bg-primary-600 rounded-full flex items-center justify-center shadow-lg">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <span class="text-xs mt-1 text-primary-600 font-medium">{{ __('messages.pay_now') }}</span>
                    </a>
                    <a href="{{ route('resident.payments.index') }}" class="flex flex-col items-center justify-center flex-1 min-h-touch {{ request()->routeIs('resident.payments.index') ? 'text-primary-600' : 'text-gray-500' }}">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                        <span class="text-xs mt-1">{{ __('messages.payments') }}</span>
                    </a>
                    <a href="{{ route('profile.edit') }}" class="flex flex-col items-center justify-center flex-1 min-h-touch {{ request()->routeIs('profile.*') ? 'text-primary-600' : 'text-gray-500' }}">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <span class="text-xs mt-1">{{ __('messages.profile') }}</span>
                    </a>
                </div>
            </nav>
        @endif
    </div>
    
    @stack('scripts')
</body>
</html>
