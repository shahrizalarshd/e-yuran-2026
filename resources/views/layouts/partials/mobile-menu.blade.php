<div class="flex flex-col h-full">
    <!-- Header -->
    <div class="flex items-center justify-between px-4 h-16 bg-primary-600 text-white">
        <div class="flex items-center">
            <img src="{{ asset('images/logo.png') }}" alt="PPTTK" class="h-10 w-auto bg-white rounded-lg p-1">
        </div>
        <button @click="mobileMenuOpen = false" class="p-2 rounded-lg hover:bg-primary-700">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    <!-- User Info -->
    <div class="p-4 bg-gray-50 border-b">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 bg-primary-100 rounded-full flex items-center justify-center">
                <span class="text-primary-700 font-bold text-lg">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
            </div>
            <div>
                <p class="font-medium text-gray-900">{{ auth()->user()->name }}</p>
                <p class="text-sm text-gray-500">{{ auth()->user()->role_display_name }}</p>
            </div>
        </div>
    </div>

    <!-- Language Switcher -->
    <div class="flex items-center gap-2 p-4 border-b">
        <span class="text-sm text-gray-500">{{ __('Bahasa') }}:</span>
        <a href="{{ route('language.switch', 'bm') }}" class="px-3 py-1.5 rounded-lg text-sm {{ app()->getLocale() === 'bm' ? 'bg-primary-100 text-primary-700 font-medium' : 'bg-gray-100 text-gray-600' }}">BM</a>
        <a href="{{ route('language.switch', 'en') }}" class="px-3 py-1.5 rounded-lg text-sm {{ app()->getLocale() === 'en' ? 'bg-primary-100 text-primary-700 font-medium' : 'bg-gray-100 text-gray-600' }}">EN</a>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 p-4 space-y-1 overflow-y-auto">
        @if(auth()->user()->isAdmin())
            <a href="{{ route('admin.dashboard') }}" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-3 py-3 rounded-lg min-h-touch {{ request()->routeIs('admin.dashboard') ? 'bg-primary-50 text-primary-700' : 'text-gray-700 hover:bg-gray-50' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6z"/>
                </svg>
                <span>{{ __('messages.dashboard') }}</span>
            </a>

            <a href="{{ route('admin.houses.index') }}" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-3 py-3 rounded-lg min-h-touch {{ request()->routeIs('admin.houses.*') ? 'bg-primary-50 text-primary-700' : 'text-gray-700 hover:bg-gray-50' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                <span>{{ __('messages.houses') }}</span>
            </a>

            <a href="{{ route('admin.residents.index') }}" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-3 py-3 rounded-lg min-h-touch {{ request()->routeIs('admin.residents.*') ? 'bg-primary-50 text-primary-700' : 'text-gray-700 hover:bg-gray-50' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                <span>{{ __('messages.residents') }}</span>
            </a>

            <a href="{{ route('admin.verifications.pending') }}" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-3 py-3 rounded-lg min-h-touch {{ request()->routeIs('admin.verifications.*') ? 'bg-primary-50 text-primary-700' : 'text-gray-700 hover:bg-gray-50' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>{{ __('messages.user_verification') }}</span>
            </a>

            <a href="{{ route('admin.bills.index') }}" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-3 py-3 rounded-lg min-h-touch {{ request()->routeIs('admin.bills.*') ? 'bg-primary-50 text-primary-700' : 'text-gray-700 hover:bg-gray-50' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span>{{ __('messages.bills') }}</span>
            </a>

            <a href="{{ route('admin.payments.index') }}" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-3 py-3 rounded-lg min-h-touch {{ request()->routeIs('admin.payments.*') ? 'bg-primary-50 text-primary-700' : 'text-gray-700 hover:bg-gray-50' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
                <span>{{ __('messages.payments') }}</span>
            </a>

            @if(auth()->user()->isSuperAdmin())
            <div class="pt-4 mt-4 border-t border-gray-200">
                <a href="{{ route('admin.fees.index') }}" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-3 py-3 rounded-lg min-h-touch {{ request()->routeIs('admin.fees.*') ? 'bg-primary-50 text-primary-700' : 'text-gray-700 hover:bg-gray-50' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>{{ __('messages.fee_configuration') }}</span>
                </a>

                <a href="{{ route('admin.settings.index') }}" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-3 py-3 rounded-lg min-h-touch {{ request()->routeIs('admin.settings.*') ? 'bg-primary-50 text-primary-700' : 'text-gray-700 hover:bg-gray-50' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span>{{ __('messages.settings') }}</span>
                </a>
            </div>
            @endif

            @if(auth()->user()->canViewAuditLogs())
            <a href="{{ route('admin.audit-logs.index') }}" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-3 py-3 rounded-lg min-h-touch {{ request()->routeIs('admin.audit-logs.*') ? 'bg-primary-50 text-primary-700' : 'text-gray-700 hover:bg-gray-50' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <span>{{ __('messages.audit_logs') }}</span>
            </a>
            @endif
        @else
            <!-- Resident navigation for mobile -->
            <a href="{{ route('resident.dashboard') }}" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-3 py-3 rounded-lg min-h-touch {{ request()->routeIs('resident.dashboard') ? 'bg-primary-50 text-primary-700' : 'text-gray-700 hover:bg-gray-50' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                <span>{{ __('messages.dashboard') }}</span>
            </a>

            <a href="{{ route('resident.bills.index') }}" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-3 py-3 rounded-lg min-h-touch {{ request()->routeIs('resident.bills.*') ? 'bg-primary-50 text-primary-700' : 'text-gray-700 hover:bg-gray-50' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span>{{ __('messages.bills') }}</span>
            </a>

            <a href="{{ route('resident.payments.index') }}" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-3 py-3 rounded-lg min-h-touch {{ request()->routeIs('resident.payments.index') ? 'bg-primary-50 text-primary-700' : 'text-gray-700 hover:bg-gray-50' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
                <span>{{ __('messages.payment_history') }}</span>
            </a>

            <a href="{{ route('resident.house-settings.index') }}" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-3 py-3 rounded-lg min-h-touch {{ request()->routeIs('resident.house-settings.*') ? 'bg-primary-50 text-primary-700' : 'text-gray-700 hover:bg-gray-50' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <span>{{ __('Tetapan Rumah') }}</span>
            </a>
        @endif
    </nav>

    <!-- Footer -->
    <div class="p-4 border-t space-y-2">
        <a href="{{ route('profile.edit') }}" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-3 py-3 rounded-lg min-h-touch text-gray-700 hover:bg-gray-50">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
            <span>{{ __('messages.profile') }}</span>
        </a>
        
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="flex items-center gap-3 px-3 py-3 rounded-lg min-h-touch text-red-600 hover:bg-red-50 w-full">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
                <span>{{ __('messages.logout') }}</span>
            </button>
        </form>
    </div>
</div>

