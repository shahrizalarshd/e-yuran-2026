<x-app-layout>
    <x-slot name="title">{{ __('Panduan Sistem') }}</x-slot>

    <div class="space-y-6" x-data="{ activeSection: null }">
        {{-- Header --}}
        <div class="bg-gradient-to-r from-primary-600 to-primary-700 rounded-2xl p-6 text-white">
            <div class="flex items-center gap-3 mb-2">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
                <div>
                    <h1 class="text-2xl font-bold">{{ __('Panduan Sistem e-Yuran') }}</h1>
                    <p class="text-primary-100 text-sm mt-1">
                        @if(auth()->user()->isSuperAdmin())
                            {{ __('Panduan lengkap untuk Super Admin') }}
                        @elseif(auth()->user()->isTreasurer())
                            {{ __('Panduan untuk Bendahari') }}
                        @elseif(auth()->user()->isAuditor())
                            {{ __('Panduan untuk Pemeriksa') }}
                        @else
                            {{ __('Panduan Sistem') }}
                        @endif
                    </p>
                </div>
            </div>
            {{-- Role Badge --}}
            <div class="mt-3 inline-flex items-center gap-2 px-3 py-1.5 bg-white/20 rounded-full text-sm">
                @if(auth()->user()->isSuperAdmin())
                    🔑 Super Admin — {{ __('Akses penuh ke semua modul') }}
                @elseif(auth()->user()->isTreasurer())
                    💰 {{ __('Bendahari') }} — {{ __('Pengurusan kewangan & bil') }}
                @elseif(auth()->user()->isAuditor())
                    📋 {{ __('Pemeriksa') }} — {{ __('Semakan & audit') }}
                @endif
            </div>
        </div>

        {{-- Quick Navigation --}}
        <div class="bg-white rounded-xl shadow-sm p-4">
            <h2 class="font-semibold text-gray-900 mb-3">{{ __('Navigasi Pantas') }}</h2>
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-2">
                <button @click="activeSection = activeSection === 'dashboard' ? null : 'dashboard'" class="text-left px-3 py-2 rounded-lg text-sm hover:bg-primary-50 hover:text-primary-700 transition" :class="activeSection === 'dashboard' ? 'bg-primary-50 text-primary-700 font-medium' : 'text-gray-600'">📊 {{ __('Papan Pemuka') }}</button>

                @if(auth()->user()->isSuperAdmin() || auth()->user()->isTreasurer())
                <button @click="activeSection = activeSection === 'houses' ? null : 'houses'" class="text-left px-3 py-2 rounded-lg text-sm hover:bg-primary-50 hover:text-primary-700 transition" :class="activeSection === 'houses' ? 'bg-primary-50 text-primary-700 font-medium' : 'text-gray-600'">🏠 {{ __('Rumah') }}</button>
                <button @click="activeSection = activeSection === 'bills' ? null : 'bills'" class="text-left px-3 py-2 rounded-lg text-sm hover:bg-primary-50 hover:text-primary-700 transition" :class="activeSection === 'bills' ? 'bg-primary-50 text-primary-700 font-medium' : 'text-gray-600'">📋 {{ __('Bil') }}</button>
                <button @click="activeSection = activeSection === 'payments' ? null : 'payments'" class="text-left px-3 py-2 rounded-lg text-sm hover:bg-primary-50 hover:text-primary-700 transition" :class="activeSection === 'payments' ? 'bg-primary-50 text-primary-700 font-medium' : 'text-gray-600'">💳 {{ __('Pembayaran') }}</button>
                <button @click="activeSection = activeSection === 'reports' ? null : 'reports'" class="text-left px-3 py-2 rounded-lg text-sm hover:bg-primary-50 hover:text-primary-700 transition" :class="activeSection === 'reports' ? 'bg-primary-50 text-primary-700 font-medium' : 'text-gray-600'">📈 {{ __('Laporan') }}</button>
                @endif

                @if(auth()->user()->isSuperAdmin())
                <button @click="activeSection = activeSection === 'membership' ? null : 'membership'" class="text-left px-3 py-2 rounded-lg text-sm hover:bg-primary-50 hover:text-primary-700 transition" :class="activeSection === 'membership' ? 'bg-primary-50 text-primary-700 font-medium' : 'text-gray-600'">👥 {{ __('Keahlian') }}</button>
                <button @click="activeSection = activeSection === 'settings' ? null : 'settings'" class="text-left px-3 py-2 rounded-lg text-sm hover:bg-primary-50 hover:text-primary-700 transition" :class="activeSection === 'settings' ? 'bg-primary-50 text-primary-700 font-medium' : 'text-gray-600'">⚙️ {{ __('Tetapan') }}</button>
                @endif

                @if(auth()->user()->canViewAuditLogs())
                <button @click="activeSection = activeSection === 'audit' ? null : 'audit'" class="text-left px-3 py-2 rounded-lg text-sm hover:bg-primary-50 hover:text-primary-700 transition" :class="activeSection === 'audit' ? 'bg-primary-50 text-primary-700 font-medium' : 'text-gray-600'">📝 {{ __('Log Audit') }}</button>
                @endif

                @if(auth()->user()->isAuditor())
                <button @click="activeSection = activeSection === 'bills' ? null : 'bills'" class="text-left px-3 py-2 rounded-lg text-sm hover:bg-primary-50 hover:text-primary-700 transition" :class="activeSection === 'bills' ? 'bg-primary-50 text-primary-700 font-medium' : 'text-gray-600'">📋 {{ __('Bil') }}</button>
                <button @click="activeSection = activeSection === 'payments' ? null : 'payments'" class="text-left px-3 py-2 rounded-lg text-sm hover:bg-primary-50 hover:text-primary-700 transition" :class="activeSection === 'payments' ? 'bg-primary-50 text-primary-700 font-medium' : 'text-gray-600'">💳 {{ __('Pembayaran') }}</button>
                @endif
            </div>
        </div>

        {{-- ============================================= --}}
        {{-- BENDAHARI: Your Key Responsibilities --}}
        {{-- ============================================= --}}
        @if(auth()->user()->isTreasurer())
        <div x-show="!activeSection" x-transition class="bg-amber-50 border border-amber-200 rounded-xl p-5">
            <h2 class="font-semibold text-amber-900 mb-3 flex items-center gap-2">💰 {{ __('Tanggungjawab Utama Bendahari') }}</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div class="flex items-start gap-3 p-3 bg-white rounded-lg">
                    <span class="text-lg">📊</span>
                    <div>
                        <p class="font-medium text-gray-900 text-sm">{{ __('Pantau Kutipan') }}</p>
                        <p class="text-xs text-gray-600">{{ __('Semak dashboard untuk jumlah kutipan & tertunggak harian') }}</p>
                    </div>
                </div>
                <div class="flex items-start gap-3 p-3 bg-white rounded-lg">
                    <span class="text-lg">📋</span>
                    <div>
                        <p class="font-medium text-gray-900 text-sm">{{ __('Urus Bil') }}</p>
                        <p class="text-xs text-gray-600">{{ __('Lihat & edit bil yang belum bayar sahaja. Bil yang sudah bayar tidak boleh diedit.') }}</p>
                    </div>
                </div>
                <div class="flex items-start gap-3 p-3 bg-white rounded-lg">
                    <span class="text-lg">📈</span>
                    <div>
                        <p class="font-medium text-gray-900 text-sm">{{ __('Jana Laporan') }}</p>
                        <p class="text-xs text-gray-600">{{ __('Cetak laporan kewangan untuk mesyuarat agung') }}</p>
                    </div>
                </div>
                <div class="flex items-start gap-3 p-3 bg-white rounded-lg">
                    <span class="text-lg">💳</span>
                    <div>
                        <p class="font-medium text-gray-900 text-sm">{{ __('Semak Pembayaran') }}</p>
                        <p class="text-xs text-gray-600">{{ __('Lihat status semua pembayaran FPX/ToyyibPay') }}</p>
                    </div>
                </div>
            </div>
            <div class="mt-3 p-3 bg-red-50 border border-red-200 rounded-lg">
                <p class="text-sm text-red-800">⚠️ <strong>{{ __('Had Akses') }}:</strong> {{ __('Bendahari TIDAK boleh jana bil baru, padam data, edit bil yang sudah dibayar, atau akses tetapan sistem. Hanya Super Admin boleh.') }}</p>
            </div>
        </div>
        @endif

        {{-- ============================================= --}}
        {{-- PEMERIKSA: Your Key Responsibilities --}}
        {{-- ============================================= --}}
        @if(auth()->user()->isAuditor())
        <div x-show="!activeSection" x-transition class="bg-blue-50 border border-blue-200 rounded-xl p-5">
            <h2 class="font-semibold text-blue-900 mb-3 flex items-center gap-2">📋 {{ __('Tanggungjawab Utama Pemeriksa') }}</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div class="flex items-start gap-3 p-3 bg-white rounded-lg">
                    <span class="text-lg">📝</span>
                    <div>
                        <p class="font-medium text-gray-900 text-sm">{{ __('Semak Log Audit') }}</p>
                        <p class="text-xs text-gray-600">{{ __('Lihat setiap tindakan yang dilakukan oleh admin — siapa, bila, apa') }}</p>
                    </div>
                </div>
                <div class="flex items-start gap-3 p-3 bg-white rounded-lg">
                    <span class="text-lg">💳</span>
                    <div>
                        <p class="font-medium text-gray-900 text-sm">{{ __('Sahkan Pembayaran') }}</p>
                        <p class="text-xs text-gray-600">{{ __('Semak rekod pembayaran untuk pastikan tiada ketidaksesuaian') }}</p>
                    </div>
                </div>
                <div class="flex items-start gap-3 p-3 bg-white rounded-lg">
                    <span class="text-lg">📋</span>
                    <div>
                        <p class="font-medium text-gray-900 text-sm">{{ __('Lihat Bil') }}</p>
                        <p class="text-xs text-gray-600">{{ __('Semak senarai bil dan status untuk audit kewangan') }}</p>
                    </div>
                </div>
                <div class="flex items-start gap-3 p-3 bg-white rounded-lg">
                    <span class="text-lg">📊</span>
                    <div>
                        <p class="font-medium text-gray-900 text-sm">{{ __('Papan Pemuka') }}</p>
                        <p class="text-xs text-gray-600">{{ __('Lihat ringkasan statistik kutipan untuk gambaran keseluruhan') }}</p>
                    </div>
                </div>
            </div>
            <div class="mt-3 p-3 bg-blue-100 border border-blue-300 rounded-lg">
                <p class="text-sm text-blue-900">ℹ️ <strong>{{ __('Akses Baca Sahaja') }}:</strong> {{ __('Pemeriksa hanya boleh LIHAT data. Tiada kebenaran untuk edit, padam, atau tambah sebarang rekod. Ini untuk menjamin integriti audit.') }}</p>
            </div>
        </div>
        @endif

        {{-- Section: Dashboard --}}
        <div x-show="!activeSection || activeSection === 'dashboard'" x-transition class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="p-4 border-b border-gray-100 bg-gray-50">
                <h2 class="font-semibold text-gray-900 flex items-center gap-2">📊 {{ __('Papan Pemuka Admin') }}</h2>
            </div>
            <div class="p-4 space-y-4">
                <img src="{{ asset('images/guide/admin-dashboard.png') }}" alt="Admin Dashboard" class="w-full rounded-lg border border-gray-200 shadow-sm">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div class="flex items-start gap-3 p-3 bg-green-50 rounded-lg">
                        <span class="text-green-600 font-bold text-lg">➊</span>
                        <div>
                            <p class="font-medium text-gray-900 text-sm">{{ __('Kad Statistik') }}</p>
                            <p class="text-xs text-gray-600">{{ __('Jumlah kutipan, tertunggak, rumah ahli aktif, dan bil overdue.') }}</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3 p-3 bg-blue-50 rounded-lg">
                        <span class="text-blue-600 font-bold text-lg">➋</span>
                        <div>
                            <p class="font-medium text-gray-900 text-sm">{{ __('Graf Analitik') }}</p>
                            <p class="text-xs text-gray-600">{{ __('Perbandingan kutipan bulanan. Guna dropdown tahun untuk tukar tahun.') }}</p>
                        </div>
                    </div>
                    @if(auth()->user()->isSuperAdmin() || auth()->user()->isTreasurer())
                    <div class="flex items-start gap-3 p-3 bg-purple-50 rounded-lg">
                        <span class="text-purple-600 font-bold text-lg">➌</span>
                        <div>
                            <p class="font-medium text-gray-900 text-sm">{{ __('Tindakan Pantas') }}</p>
                            <p class="text-xs text-gray-600">
                                @if(auth()->user()->isSuperAdmin())
                                    {{ __('Jana Bil, Laporan Tertunggak, Laporan Kutipan, Pengesahan Pengguna.') }}
                                @else
                                    {{ __('Laporan Tertunggak, Laporan Kutipan. (Jana Bil hanya untuk Super Admin)') }}
                                @endif
                            </p>
                        </div>
                    </div>
                    @endif
                    <div class="flex items-start gap-3 p-3 bg-orange-50 rounded-lg">
                        <span class="text-orange-600 font-bold text-lg">➍</span>
                        <div>
                            <p class="font-medium text-gray-900 text-sm">{{ __('Senarai Terkini') }}</p>
                            <p class="text-xs text-gray-600">{{ __('Pembayaran terkini dan rumah tertunggak di bahagian bawah.') }}</p>
                        </div>
                    </div>
                </div>
                @if(auth()->user()->isTreasurer())
                <div class="p-3 bg-green-50 border border-green-200 rounded-lg">
                    <p class="text-sm text-green-800">💡 <strong>{{ __('Tip Bendahari') }}:</strong> {{ __('Semak papan pemuka setiap hari untuk memastikan kutipan berjalan lancar. Guna dropdown tahun untuk bandingkan prestasi tahunan.') }}</p>
                </div>
                @endif
                @if(auth()->user()->isAuditor())
                <div class="p-3 bg-blue-50 border border-blue-200 rounded-lg">
                    <p class="text-sm text-blue-800">ℹ️ <strong>{{ __('Nota Pemeriksa') }}:</strong> {{ __('Gunakan statistik di papan pemuka untuk mendapat gambaran pantas sebelum menyemak log audit secara terperinci.') }}</p>
                </div>
                @endif
            </div>
        </div>

        {{-- Section: Houses (Super Admin & Bendahari) --}}
        @if(auth()->user()->isSuperAdmin() || auth()->user()->isTreasurer())
        <div x-show="!activeSection || activeSection === 'houses'" x-transition class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="p-4 border-b border-gray-100 bg-gray-50">
                <h2 class="font-semibold text-gray-900 flex items-center gap-2">🏠 {{ __('Pengurusan Rumah') }}</h2>
            </div>
            <div class="p-4 space-y-4">
                <img src="{{ asset('images/guide/admin-houses.png') }}" alt="Houses List" class="w-full rounded-lg border border-gray-200 shadow-sm">
                <div class="space-y-2">
                    <div class="flex items-start gap-3 p-3 bg-gray-50 rounded-lg">
                        <span class="text-primary-600 font-bold">🔍</span>
                        <p class="text-sm text-gray-700"><strong>{{ __('Penapis') }}:</strong> {{ __('Cari rumah mengikut nombor, jalan, atau status pendaftaran.') }}</p>
                    </div>
                    <div class="flex items-start gap-3 p-3 bg-gray-50 rounded-lg">
                        <span class="text-green-600 font-bold">🟢</span>
                        <p class="text-sm text-gray-700"><strong>{{ __('Badge Hijau "Berdaftar"') }}:</strong> {{ __('Rumah telah berdaftar — boleh dijana bil.') }}</p>
                    </div>
                    <div class="flex items-start gap-3 p-3 bg-gray-50 rounded-lg">
                        <span class="text-primary-600 font-bold">👁️</span>
                        <p class="text-sm text-gray-700"><strong>{{ __('Klik nombor rumah') }}:</strong> {{ __('Lihat butiran penuh termasuk penghuni dan sejarah bil.') }}</p>
                    </div>
                </div>
                <img src="{{ asset('images/guide/admin-house-detail.png') }}" alt="House Detail" class="w-full rounded-lg border border-gray-200 shadow-sm">
                <div class="p-3 bg-amber-50 border border-amber-200 rounded-lg">
                    <p class="text-sm text-amber-800"><strong>ℹ️ {{ __('Model Hibrid') }}:</strong> {{ __('Bil tahunan kekal dengan rumah. Yuran keahlian ikut penghuni.') }}</p>
                </div>
            </div>
        </div>
        @endif

        {{-- Section: Bills --}}
        <div x-show="!activeSection || activeSection === 'bills'" x-transition class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="p-4 border-b border-gray-100 bg-gray-50">
                <h2 class="font-semibold text-gray-900 flex items-center gap-2">📋 {{ __('Pengurusan Bil') }}</h2>
            </div>
            <div class="p-4 space-y-4">
                <img src="{{ asset('images/guide/admin-bills.png') }}" alt="Bills List" class="w-full rounded-lg border border-gray-200 shadow-sm">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div class="flex items-center gap-2 p-2 bg-green-50 rounded-lg">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">Paid</span>
                        <span class="text-sm text-gray-700">{{ __('Bil sudah dibayar penuh') }}</span>
                    </div>
                    <div class="flex items-center gap-2 p-2 bg-red-50 rounded-lg">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">Unpaid</span>
                        <span class="text-sm text-gray-700">{{ __('Bil belum dibayar') }}</span>
                    </div>
                    <div class="flex items-center gap-2 p-2 bg-blue-50 rounded-lg">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">Processing</span>
                        <span class="text-sm text-gray-700">{{ __('Sedang diproses di ToyyibPay') }}</span>
                    </div>
                    <div class="flex items-center gap-2 p-2 bg-yellow-50 rounded-lg">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">Overdue</span>
                        <span class="text-sm text-gray-700">{{ __('Sudah melepasi tarikh akhir') }}</span>
                    </div>
                </div>

                @if(auth()->user()->isSuperAdmin())
                <h3 class="font-semibold text-gray-900 mt-4">{{ __('Jana Bil Tahunan') }}</h3>
                <img src="{{ asset('images/guide/admin-generate-bills.png') }}" alt="Generate Bills" class="w-full rounded-lg border border-gray-200 shadow-sm">
                <div class="p-3 bg-green-50 border border-green-200 rounded-lg">
                    <p class="text-sm text-green-800">✅ {{ __('Anda boleh jana bil tahunan untuk semua rumah sekaligus. Sistem akan skip bil yang sudah wujud.') }}</p>
                </div>
                @endif

                @if(auth()->user()->isTreasurer())
                <div class="p-3 bg-amber-50 border border-amber-200 rounded-lg">
                    <p class="text-sm text-amber-800">⚠️ <strong>{{ __('Perhatian Bendahari') }}:</strong></p>
                    <ul class="text-sm text-amber-800 mt-1 space-y-1 list-disc list-inside">
                        <li>{{ __('Anda boleh EDIT bil yang belum bayar (tukar amaun, tarikh)') }}</li>
                        <li>{{ __('Anda TIDAK BOLEH edit bil yang sudah bayar (Paid)') }}</li>
                        <li>{{ __('Anda TIDAK BOLEH jana bil baru — hubungi Super Admin') }}</li>
                        <li>{{ __('Anda TIDAK BOLEH padam bil') }}</li>
                    </ul>
                </div>
                @endif

                @if(auth()->user()->isAuditor())
                <div class="p-3 bg-blue-50 border border-blue-200 rounded-lg">
                    <p class="text-sm text-blue-800">ℹ️ <strong>{{ __('Nota Pemeriksa') }}:</strong> {{ __('Anda boleh lihat semua bil tetapi tiada kebenaran edit atau padam. Gunakan penapis untuk semak bil mengikut tahun dan status.') }}</p>
                </div>
                @endif
            </div>
        </div>

        {{-- Section: Payments --}}
        <div x-show="!activeSection || activeSection === 'payments'" x-transition class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="p-4 border-b border-gray-100 bg-gray-50">
                <h2 class="font-semibold text-gray-900 flex items-center gap-2">💳 {{ __('Senarai Pembayaran') }}</h2>
            </div>
            <div class="p-4 space-y-4">
                <img src="{{ asset('images/guide/admin-payments.png') }}" alt="Payments List" class="w-full rounded-lg border border-gray-200 shadow-sm">
                <div class="space-y-2">
                    <div class="flex items-start gap-3 p-3 bg-gray-50 rounded-lg">
                        <span class="text-green-600 font-bold">✅</span>
                        <p class="text-sm text-gray-700"><strong>{{ __('Success') }}:</strong> {{ __('Pembayaran berjaya melalui ToyyibPay (FPX).') }}</p>
                    </div>
                    <div class="flex items-start gap-3 p-3 bg-gray-50 rounded-lg">
                        <span class="text-red-600 font-bold">❌</span>
                        <p class="text-sm text-gray-700"><strong>{{ __('Failed') }}:</strong> {{ __('Pembayaran gagal. Bil dikembalikan ke status Belum Bayar.') }}</p>
                    </div>
                    <div class="flex items-start gap-3 p-3 bg-gray-50 rounded-lg">
                        <span class="text-yellow-600 font-bold">⏳</span>
                        <p class="text-sm text-gray-700"><strong>{{ __('Pending') }}:</strong> {{ __('Menunggu maklumbalas dari gateway pembayaran.') }}</p>
                    </div>
                </div>
                @if(auth()->user()->isTreasurer())
                <div class="p-3 bg-green-50 border border-green-200 rounded-lg">
                    <p class="text-sm text-green-800">💡 <strong>{{ __('Tip Bendahari') }}:</strong> {{ __('Klik pada mana-mana pembayaran untuk lihat butiran penuh termasuk nombor rujukan ToyyibPay. Gunakan ini untuk reconcile dengan penyata bank.') }}</p>
                </div>
                @endif
                @if(auth()->user()->isAuditor())
                <div class="p-3 bg-blue-50 border border-blue-200 rounded-lg">
                    <p class="text-sm text-blue-800">ℹ️ <strong>{{ __('Nota Pemeriksa') }}:</strong> {{ __('Semak pembayaran untuk pastikan semua transaksi berjaya berpadanan dengan bil. Perhatikan jika ada pembayaran "Failed" yang banyak — ia mungkin petanda isu teknikal.') }}</p>
                </div>
                @endif
            </div>
        </div>

        {{-- Section: Reports (Super Admin & Bendahari) --}}
        @if(auth()->user()->isSuperAdmin() || auth()->user()->isTreasurer())
        <div x-show="!activeSection || activeSection === 'reports'" x-transition class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="p-4 border-b border-gray-100 bg-gray-50">
                <h2 class="font-semibold text-gray-900 flex items-center gap-2">📈 {{ __('Laporan Kewangan') }}</h2>
            </div>
            <div class="p-4 space-y-4">
                <img src="{{ asset('images/guide/admin-payment-report.png') }}" alt="Payment Report" class="w-full rounded-lg border border-gray-200 shadow-sm">
                <div class="space-y-2">
                    <div class="flex items-start gap-3 p-3 bg-gray-50 rounded-lg">
                        <span class="font-bold">📊</span>
                        <p class="text-sm text-gray-700"><strong>{{ __('Ringkasan Kutipan') }}:</strong> {{ __('Jumlah kutipan keseluruhan, pecahan mengikut tahun, dan peratusan kutipan.') }}</p>
                    </div>
                    <div class="flex items-start gap-3 p-3 bg-gray-50 rounded-lg">
                        <span class="font-bold">📈</span>
                        <p class="text-sm text-gray-700"><strong>{{ __('Graf & Jadual') }}:</strong> {{ __('Pecahan kutipan bulanan dan perbandingan tahun-ke-tahun.') }}</p>
                    </div>
                </div>
                @if(auth()->user()->isTreasurer())
                <div class="p-3 bg-green-50 border border-green-200 rounded-lg">
                    <p class="text-sm text-green-800">💡 <strong>{{ __('Tip Bendahari') }}:</strong> {{ __('Halaman ini sangat berguna untuk pembentangan mesyuarat agung tahunan. Cetak terus dari pelayar web (Ctrl+P / Cmd+P).') }}</p>
                </div>
                @endif
            </div>
        </div>
        @endif

        {{-- Section: Membership Fees (Super Admin) --}}
        @if(auth()->user()->isSuperAdmin() || auth()->user()->isTreasurer())
        <div x-show="!activeSection || activeSection === 'membership'" x-transition class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="p-4 border-b border-gray-100 bg-gray-50">
                <h2 class="font-semibold text-gray-900 flex items-center gap-2">👥 {{ __('Yuran Keahlian PPTT') }}</h2>
            </div>
            <div class="p-4 space-y-4">
                <img src="{{ asset('images/guide/admin-membership-fees.png') }}" alt="Membership Fees" class="w-full rounded-lg border border-gray-200 shadow-sm">
                <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <h4 class="font-semibold text-blue-900 mb-2">💡 {{ __('Perbezaan Penting') }}</h4>
                    <div class="space-y-2 text-sm text-blue-800">
                        <p>📋 <strong>{{ __('Yuran Tahunan') }}</strong> = {{ __('Ikut RUMAH. Kekal walaupun pemilik bertukar.') }}</p>
                        <p>👤 <strong>{{ __('Yuran Keahlian') }}</strong> = {{ __('Ikut PENGHUNI. Reset bila pemilik bertukar.') }}</p>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- Section: Settings (Super Admin only) --}}
        @if(auth()->user()->isSuperAdmin())
        <div x-show="!activeSection || activeSection === 'settings'" x-transition class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="p-4 border-b border-gray-100 bg-gray-50">
                <h2 class="font-semibold text-gray-900 flex items-center gap-2">⚙️ {{ __('Tetapan Sistem') }}</h2>
            </div>
            <div class="p-4 space-y-4">
                <img src="{{ asset('images/guide/admin-settings.png') }}" alt="Settings" class="w-full rounded-lg border border-gray-200 shadow-sm">
                <div class="space-y-2">
                    <div class="flex items-start gap-3 p-3 bg-gray-50 rounded-lg">
                        <span class="font-bold">💳</span>
                        <p class="text-sm text-gray-700"><strong>ToyyibPay:</strong> {{ __('API Key, Secret Key, Category Code. Toggle Sandbox/Production.') }}</p>
                    </div>
                    <div class="flex items-start gap-3 p-3 bg-gray-50 rounded-lg">
                        <span class="font-bold">📱</span>
                        <p class="text-sm text-gray-700"><strong>Telegram:</strong> {{ __('Bot Token dan Chat ID untuk notifikasi ralat sistem.') }}</p>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- Section: Audit Logs --}}
        @if(auth()->user()->canViewAuditLogs())
        <div x-show="!activeSection || activeSection === 'audit'" x-transition class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="p-4 border-b border-gray-100 bg-gray-50">
                <h2 class="font-semibold text-gray-900 flex items-center gap-2">📝 {{ __('Log Audit') }}</h2>
            </div>
            <div class="p-4 space-y-4">
                <img src="{{ asset('images/guide/admin-audit-logs.png') }}" alt="Audit Logs" class="w-full rounded-lg border border-gray-200 shadow-sm">
                <div class="space-y-2">
                    <div class="flex items-start gap-3 p-3 bg-gray-50 rounded-lg">
                        <span class="font-bold">📋</span>
                        <p class="text-sm text-gray-700">{{ __('Setiap tindakan penting direkod: Siapa, Buat Apa, Bila, dan Alamat IP.') }}</p>
                    </div>
                    <div class="flex items-start gap-3 p-3 bg-gray-50 rounded-lg">
                        <span class="font-bold">👁️</span>
                        <p class="text-sm text-gray-700">{{ __('Klik pada mana-mana log untuk lihat data sebelum & selepas perubahan.') }}</p>
                    </div>
                    <div class="flex items-start gap-3 p-3 bg-gray-50 rounded-lg">
                        <span class="font-bold">🔒</span>
                        <p class="text-sm text-gray-700">{{ __('Log Audit kekal dan tidak boleh dipadam — menjamin ketelusan.') }}</p>
                    </div>
                </div>
                @if(auth()->user()->isAuditor())
                <div class="p-3 bg-blue-50 border border-blue-200 rounded-lg">
                    <p class="text-sm text-blue-800">💡 <strong>{{ __('Tip Pemeriksa') }}:</strong> {{ __('Gunakan penapis tarikh dan pengguna untuk fokus pemeriksaan. Perhatikan tindakan "delete" dan "update" untuk mengesan perubahan data kritikal.') }}</p>
                </div>
                @endif
            </div>
        </div>
        @endif

        {{-- Role Access Table --}}
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="p-4 border-b border-gray-100 bg-gray-50">
                <h2 class="font-semibold text-gray-900">🔑 {{ __('Carta Peranan & Akses') }}</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-gray-500">{{ __('Ciri') }}</th>
                            <th class="px-4 py-3 text-center font-medium text-gray-500">Super Admin</th>
                            <th class="px-4 py-3 text-center font-medium text-gray-500">{{ __('Bendahari') }}</th>
                            <th class="px-4 py-3 text-center font-medium text-gray-500">{{ __('Pemeriksa') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr class="{{ auth()->user()->isSuperAdmin() ? '' : (auth()->user()->isTreasurer() ? '' : (auth()->user()->isAuditor() ? '' : '')) }}">
                            <td class="px-4 py-2">{{ __('Lihat Papan Pemuka') }}</td><td class="px-4 py-2 text-center">✅</td><td class="px-4 py-2 text-center">✅</td><td class="px-4 py-2 text-center">✅</td>
                        </tr>
                        <tr><td class="px-4 py-2">{{ __('Lihat Rumah & Penduduk') }}</td><td class="px-4 py-2 text-center">✅</td><td class="px-4 py-2 text-center">✅</td><td class="px-4 py-2 text-center">✅</td></tr>
                        <tr><td class="px-4 py-2">{{ __('Edit Rumah') }}</td><td class="px-4 py-2 text-center">✅</td><td class="px-4 py-2 text-center">✅</td><td class="px-4 py-2 text-center">❌</td></tr>
                        <tr><td class="px-4 py-2">{{ __('Jana Bil Baru') }}</td><td class="px-4 py-2 text-center">✅</td><td class="px-4 py-2 text-center">❌</td><td class="px-4 py-2 text-center">❌</td></tr>
                        <tr><td class="px-4 py-2">{{ __('Edit Bil Belum Bayar') }}</td><td class="px-4 py-2 text-center">✅</td><td class="px-4 py-2 text-center">✅</td><td class="px-4 py-2 text-center">❌</td></tr>
                        <tr><td class="px-4 py-2">{{ __('Edit Bil Sudah Bayar') }}</td><td class="px-4 py-2 text-center">✅</td><td class="px-4 py-2 text-center">❌</td><td class="px-4 py-2 text-center">❌</td></tr>
                        <tr><td class="px-4 py-2">{{ __('Lihat Pembayaran') }}</td><td class="px-4 py-2 text-center">✅</td><td class="px-4 py-2 text-center">✅</td><td class="px-4 py-2 text-center">✅</td></tr>
                        <tr><td class="px-4 py-2">{{ __('Laporan Kewangan') }}</td><td class="px-4 py-2 text-center">✅</td><td class="px-4 py-2 text-center">✅</td><td class="px-4 py-2 text-center">❌</td></tr>
                        <tr><td class="px-4 py-2">{{ __('Yuran Keahlian') }}</td><td class="px-4 py-2 text-center">✅</td><td class="px-4 py-2 text-center">✅</td><td class="px-4 py-2 text-center">❌</td></tr>
                        <tr><td class="px-4 py-2">{{ __('Tetapan Sistem') }}</td><td class="px-4 py-2 text-center">✅</td><td class="px-4 py-2 text-center">❌</td><td class="px-4 py-2 text-center">❌</td></tr>
                        <tr><td class="px-4 py-2">{{ __('Log Audit') }}</td><td class="px-4 py-2 text-center">✅</td><td class="px-4 py-2 text-center">❌</td><td class="px-4 py-2 text-center">✅</td></tr>
                        <tr><td class="px-4 py-2">{{ __('Padam Data') }}</td><td class="px-4 py-2 text-center">✅</td><td class="px-4 py-2 text-center">❌</td><td class="px-4 py-2 text-center">❌</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
