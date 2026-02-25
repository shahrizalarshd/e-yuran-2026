<x-app-layout>
    <x-slot name="title">{{ __('Panduan Pengguna') }}</x-slot>

    <div class="space-y-6">
        {{-- Header --}}
        <div class="bg-gradient-to-r from-primary-600 to-primary-700 rounded-2xl p-6 text-white">
            <div class="flex items-center gap-3 mb-2">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
                <div>
                    <h1 class="text-2xl font-bold">{{ __('Panduan Pengguna') }}</h1>
                    <p class="text-primary-100 text-sm mt-1">{{ __('Cara menggunakan sistem e-Yuran') }}</p>
                </div>
            </div>
        </div>

        {{-- Step 1: Dashboard --}}
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="p-4 border-b border-gray-100 bg-gray-50">
                <h2 class="font-semibold text-gray-900 flex items-center gap-2">
                    <span class="w-7 h-7 bg-primary-600 text-white rounded-full flex items-center justify-center text-sm font-bold">1</span>
                    {{ __('Papan Pemuka (Dashboard)') }}
                </h2>
            </div>
            <div class="p-4 space-y-4">
                <img src="{{ asset('images/guide/resident-dashboard.png') }}" alt="Resident Dashboard" class="w-full rounded-lg border border-gray-200 shadow-sm">
                <div class="space-y-2">
                    <div class="flex items-start gap-3 p-3 bg-blue-50 rounded-lg">
                        <span class="text-blue-600 font-bold text-lg shrink-0">➊</span>
                        <div>
                            <p class="font-medium text-gray-900 text-sm">{{ __('Jumlah Tertunggak') }}</p>
                            <p class="text-xs text-gray-600">{{ __('Menunjukkan jumlah keseluruhan yang perlu dibayar untuk rumah anda.') }}</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3 p-3 bg-green-50 rounded-lg">
                        <span class="text-green-600 font-bold text-lg shrink-0">➋</span>
                        <div>
                            <p class="font-medium text-gray-900 text-sm">{{ __('Butang "Bayar Sekarang"') }}</p>
                            <p class="text-xs text-gray-600">{{ __('Klik untuk terus ke halaman pembayaran melalui FPX (online banking).') }}</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3 p-3 bg-purple-50 rounded-lg">
                        <span class="text-purple-600 font-bold text-lg shrink-0">➌</span>
                        <div>
                            <p class="font-medium text-gray-900 text-sm">{{ __('Progres Tahun Semasa') }}</p>
                            <p class="text-xs text-gray-600">{{ __('Bar progress menunjukkan berapa bulan sudah dibayar dari 12 bulan tahun ini.') }}</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3 p-3 bg-orange-50 rounded-lg">
                        <span class="text-orange-600 font-bold text-lg shrink-0">➍</span>
                        <div>
                            <p class="font-medium text-gray-900 text-sm">{{ __('Senarai Bil Belum Bayar') }}</p>
                            <p class="text-xs text-gray-600">{{ __('Bil-bil disusun mengikut tahun. Klik tahun untuk kembang/tutup senarai.') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Step 2: Bills --}}
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="p-4 border-b border-gray-100 bg-gray-50">
                <h2 class="font-semibold text-gray-900 flex items-center gap-2">
                    <span class="w-7 h-7 bg-primary-600 text-white rounded-full flex items-center justify-center text-sm font-bold">2</span>
                    {{ __('Senarai Bil') }}
                </h2>
            </div>
            <div class="p-4 space-y-4">
                <img src="{{ asset('images/guide/resident-bills.png') }}" alt="Resident Bills" class="w-full rounded-lg border border-gray-200 shadow-sm">
                <div class="grid grid-cols-2 gap-3">
                    <div class="flex items-center gap-2 p-2 bg-green-50 rounded-lg">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">{{ __('Sudah Bayar') }}</span>
                        <span class="text-xs text-gray-600">✅</span>
                    </div>
                    <div class="flex items-center gap-2 p-2 bg-red-50 rounded-lg">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">{{ __('Belum Bayar') }}</span>
                        <span class="text-xs text-gray-600">❌</span>
                    </div>
                </div>
                <p class="text-sm text-gray-600">{{ __('Klik ikon 🖨️ untuk cetak resit pembayaran yang berjaya.') }}</p>
            </div>
        </div>

        {{-- Step 3: How to Pay --}}
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="p-4 border-b border-gray-100 bg-gray-50">
                <h2 class="font-semibold text-gray-900 flex items-center gap-2">
                    <span class="w-7 h-7 bg-primary-600 text-white rounded-full flex items-center justify-center text-sm font-bold">3</span>
                    {{ __('Cara Membuat Pembayaran') }}
                </h2>
            </div>
            <div class="p-4">
                <div class="space-y-4">
                    {{-- Step by step --}}
                    <div class="flex items-start gap-4 p-4 bg-gray-50 rounded-lg">
                        <div class="w-10 h-10 bg-primary-100 rounded-full flex items-center justify-center shrink-0">
                            <span class="text-primary-700 font-bold">1</span>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">{{ __('Klik "Bayar Sekarang"') }}</p>
                            <p class="text-sm text-gray-600">{{ __('Dari papan pemuka, klik butang putih "Bayar Sekarang" pada kad tertunggak.') }}</p>
                        </div>
                    </div>

                    <div class="flex justify-center">
                        <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                        </svg>
                    </div>

                    <div class="flex items-start gap-4 p-4 bg-gray-50 rounded-lg">
                        <div class="w-10 h-10 bg-primary-100 rounded-full flex items-center justify-center shrink-0">
                            <span class="text-primary-700 font-bold">2</span>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">{{ __('Pilih Bulan') }}</p>
                            <p class="text-sm text-gray-600">{{ __('Tandakan (✅) bulan-bulan yang ingin dibayar. Boleh pilih satu atau banyak sekaligus.') }}</p>
                        </div>
                    </div>

                    <div class="flex justify-center">
                        <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                        </svg>
                    </div>

                    <div class="flex items-start gap-4 p-4 bg-gray-50 rounded-lg">
                        <div class="w-10 h-10 bg-primary-100 rounded-full flex items-center justify-center shrink-0">
                            <span class="text-primary-700 font-bold">3</span>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">{{ __('Semak & Bayar') }}</p>
                            <p class="text-sm text-gray-600">{{ __('Semak jumlah amaun. Klik "Bayar" untuk diarahkan ke ToyyibPay (FPX).') }}</p>
                        </div>
                    </div>

                    <div class="flex justify-center">
                        <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                        </svg>
                    </div>

                    <div class="flex items-start gap-4 p-4 bg-gray-50 rounded-lg">
                        <div class="w-10 h-10 bg-primary-100 rounded-full flex items-center justify-center shrink-0">
                            <span class="text-primary-700 font-bold">4</span>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">{{ __('Pilih Bank & Bayar') }}</p>
                            <p class="text-sm text-gray-600">{{ __('Di ToyyibPay, pilih bank anda dan lengkapkan pembayaran. Anda akan dibawa semula ke halaman resit.') }}</p>
                        </div>
                    </div>

                    <div class="flex justify-center">
                        <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                        </svg>
                    </div>

                    <div class="flex items-start gap-4 p-4 bg-green-50 rounded-lg border border-green-200">
                        <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center shrink-0">
                            <span class="text-green-700 font-bold">✓</span>
                        </div>
                        <div>
                            <p class="font-medium text-green-900">{{ __('Siap!') }}</p>
                            <p class="text-sm text-green-700">{{ __('Resit akan dipaparkan. Bil secara automatik dikemas kini ke status "Sudah Bayar".') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Payment Options --}}
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="p-4 border-b border-gray-100 bg-gray-50">
                <h2 class="font-semibold text-gray-900">💡 {{ __('Jenis Bayaran') }}</h2>
            </div>
            <div class="p-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div class="p-4 bg-blue-50 rounded-lg text-center">
                        <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center mx-auto mb-2">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <p class="font-medium text-gray-900 text-sm">{{ __('Bulan Semasa') }}</p>
                        <p class="text-xs text-gray-600 mt-1">{{ __('Bayar bil bulan ini sahaja') }}</p>
                    </div>
                    <div class="p-4 bg-purple-50 rounded-lg text-center">
                        <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center mx-auto mb-2">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                            </svg>
                        </div>
                        <p class="font-medium text-gray-900 text-sm">{{ __('Bulan Terpilih') }}</p>
                        <p class="text-xs text-gray-600 mt-1">{{ __('Pilih mana-mana bulan') }}</p>
                    </div>
                    <div class="p-4 bg-green-50 rounded-lg text-center">
                        <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center mx-auto mb-2">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                            </svg>
                        </div>
                        <p class="font-medium text-gray-900 text-sm">{{ __('Setahun Penuh') }}</p>
                        <p class="text-xs text-gray-600 mt-1">{{ __('Bayar semua 12 bulan') }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Contact --}}
        <div class="bg-white rounded-xl shadow-sm p-4">
            <h3 class="font-semibold text-gray-900 mb-2">❓ {{ __('Perlukan Bantuan?') }}</h3>
            <p class="text-sm text-gray-600">{{ __('Sila hubungi Bendahari PPTT untuk sebarang pertanyaan mengenai bil atau pembayaran anda.') }}</p>
        </div>
    </div>
</x-app-layout>
