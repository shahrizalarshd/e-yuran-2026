<x-app-layout>
    <x-slot name="title">{{ __('Tiada Rumah') }}</x-slot>

    <div class="max-w-lg mx-auto">
        <div class="bg-white rounded-2xl shadow-sm p-8 text-center">
            <div class="w-20 h-20 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-10 h-10 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
            </div>
            <h2 class="text-xl font-bold text-gray-900 mb-2">{{ __('Tiada Rumah Didaftarkan') }}</h2>
            <p class="text-gray-500 mb-6">{{ __('Anda belum mendaftar sebagai ahli mana-mana rumah di Taman Tropika Kajang.') }}</p>
            
            <div class="p-4 bg-blue-50 rounded-lg text-left mb-6">
                <p class="text-sm text-blue-800 font-medium mb-2">{{ __('Untuk mendaftar:') }}</p>
                <ol class="text-sm text-blue-700 space-y-1 list-decimal list-inside">
                    <li>{{ __('Hubungi pentadbir taman') }}</li>
                    <li>{{ __('Berikan maklumat rumah anda') }}</li>
                    <li>{{ __('Tunggu kelulusan pendaftaran') }}</li>
                </ol>
            </div>

            <a href="{{ route('profile.edit') }}" class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-primary-600 text-white font-medium rounded-xl hover:bg-primary-700 transition min-h-touch">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                {{ __('Lihat Profil') }}
            </a>
        </div>
    </div>
</x-app-layout>

