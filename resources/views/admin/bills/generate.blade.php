<x-app-layout>
    <x-slot name="title">{{ __('messages.generate_bills') }}</x-slot>

    <div class="max-w-xl mx-auto space-y-6">
        <!-- Info Card -->
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
            <div class="flex gap-3">
                <svg class="w-6 h-6 text-blue-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div class="text-sm text-blue-800">
                    <p class="font-semibold">{{ __('Halaman Admin Sahaja') }}</p>
                    <p class="mt-1">{{ __('Sistem akan menjana bil secara automatik setiap 1 Januari. Halaman ini untuk kes khas sahaja - testing, penjanaan automatik gagal, atau kadar yuran berbeza.') }}</p>
                </div>
            </div>
        </div>

        <!-- Yearly Generation Card -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="p-4 lg:p-6 border-b border-gray-100 bg-gradient-to-r from-primary-500 to-primary-600">
                <h2 class="text-lg font-semibold text-white">{{ __('Jana Bil Tahunan') }}</h2>
                <p class="text-sm text-primary-100 mt-1">{{ __('Jana bil untuk semua 12 bulan sekaligus') }}</p>
            </div>

            <form action="{{ route('admin.bills.generate.yearly') }}" method="POST" class="p-4 lg:p-6 space-y-5">
                @csrf

                <div>
                    <label for="year" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.bill_year') }} <span class="text-red-500">*</span></label>
                    <input type="number" name="year" id="year" value="{{ now()->year }}" required min="2020" max="2099" class="w-full rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500" placeholder="2025">
                    <p class="text-xs text-gray-500 mt-1">{{ __('Masukkan tahun bil yang ingin dijana (contoh: 2025, 2026)') }}</p>
                    @error('year')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="amount" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Amaun Sebulan (RM)') }} <span class="text-red-500">*</span></label>
                    <input type="number" name="amount" id="amount" value="{{ $currentFeeAmount ?? 20 }}" required step="0.01" min="1" class="w-full rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500" placeholder="20.00">
                    <p class="text-xs text-gray-500 mt-1">{{ __('Amaun yuran setiap bulan untuk setiap rumah') }}</p>
                    @error('amount')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Preview -->
                <div class="p-4 bg-gray-50 rounded-lg border border-gray-200" x-data="{ year: {{ now()->year }}, amount: {{ $currentFeeAmount ?? 20 }}, houses: {{ $housesCount ?? 25 }} }" x-init="$watch('amount', value => amount = value)">
                    <p class="text-sm font-medium text-gray-700 mb-2">{{ __('Pratonton:') }}</p>
                    <div class="space-y-1 text-sm text-gray-600">
                        <p>📅 {{ __('Bulan') }}: <span class="font-medium">12 bulan</span></p>
                        <p>🏠 {{ __('Rumah Berdaftar') }}: <span class="font-medium">{{ $housesCount ?? 25 }} rumah</span></p>
                        <p>💰 {{ __('Amaun Sebulan') }}: <span class="font-medium">RM <span x-text="parseFloat(document.getElementById('amount').value || {{ $currentFeeAmount ?? 20 }}).toFixed(2)">{{ number_format($currentFeeAmount ?? 20, 2) }}</span></span></p>
                    </div>
                    <div class="mt-3 pt-3 border-t border-gray-300">
                        <p class="text-sm text-primary-700 font-semibold">
                            {{ __('Jumlah Bil') }}: 12 × {{ $housesCount ?? 25 }} = <span class="text-lg">{{ 12 * ($housesCount ?? 25) }} bil</span>
                        </p>
                    </div>
                </div>

                <button type="submit" class="w-full py-3 bg-primary-600 text-white font-medium rounded-lg hover:bg-primary-700 transition min-h-touch flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    {{ __('Jana Bil Setahun') }}
                </button>
            </form>
        </div>

        <!-- Warning Card -->
        <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-xl">
            <div class="flex gap-3">
                <svg class="w-5 h-5 text-yellow-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <div class="text-sm text-yellow-800">
                    <p class="font-medium">{{ __('Perhatian') }}</p>
                    <ul class="mt-1 list-disc list-inside space-y-1">
                        <li>{{ __('Bil hanya dijana untuk rumah yang berdaftar dan aktif') }}</li>
                        <li>{{ __('Bil sedia ada tidak akan ditimpa') }}</li>
                        <li>{{ __('Notifikasi akan dihantar kepada semua penduduk') }}</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="text-center">
            <a href="{{ route('admin.bills.index') }}" class="text-gray-500 hover:text-gray-700 text-sm">
                ← {{ __('Kembali ke Senarai Bil') }}
            </a>
        </div>
    </div>
</x-app-layout>
