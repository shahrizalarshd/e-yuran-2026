<x-app-layout>
    <x-slot name="title">{{ __('Peringatan Yuran') }}</x-slot>

    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">📧 Peringatan Yuran</h1>
                <p class="text-gray-600 mt-1">Hantar peringatan email kepada penduduk yang mempunyai bil tertunggak</p>
            </div>
            <a href="{{ route('admin.bills.outstanding') }}" class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-900">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Kembali ke Senarai Tertunggak
            </a>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Overdue Bills -->
            <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-xl p-6 text-white">
                <div class="flex items-center gap-4">
                    <div class="bg-white/20 rounded-full p-3">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-red-100 text-sm">Bil Tertunggak (Overdue)</p>
                        <p class="text-3xl font-bold">{{ $overdueCount }}</p>
                        <p class="text-red-100 text-sm mt-1">RM {{ number_format($totalOverdueAmount, 2) }}</p>
                    </div>
                </div>
                <p class="text-red-100 text-xs mt-4">Bil yang telah melepasi tarikh akhir pembayaran</p>
            </div>

            <!-- Unpaid Bills (not overdue) -->
            <div class="bg-gradient-to-br from-amber-500 to-amber-600 rounded-xl p-6 text-white">
                <div class="flex items-center gap-4">
                    <div class="bg-white/20 rounded-full p-3">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-amber-100 text-sm">Bil Belum Bayar</p>
                        <p class="text-3xl font-bold">{{ $unpaidCount }}</p>
                        <p class="text-amber-100 text-sm mt-1">RM {{ number_format($totalUnpaidAmount, 2) }}</p>
                    </div>
                </div>
                <p class="text-amber-100 text-xs mt-4">Bil yang belum dibayar tetapi belum melepasi tarikh akhir</p>
            </div>
        </div>

        <!-- Send Reminders Actions -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="p-4 lg:p-6 border-b border-gray-100 bg-gradient-to-r from-primary-500 to-primary-600">
                <h2 class="text-lg font-semibold text-white">Hantar Peringatan Email</h2>
                <p class="text-primary-100 text-sm mt-1">Pilih jenis peringatan yang ingin dihantar</p>
            </div>

            <div class="p-4 lg:p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Send All Reminders -->
                    <form action="{{ route('admin.bills.send-reminders') }}" method="POST" onsubmit="return confirm('Adakah anda pasti mahu menghantar SEMUA peringatan?')">
                        @csrf
                        <input type="hidden" name="type" value="all">
                        <button type="submit" class="w-full p-6 border-2 border-primary-200 rounded-xl hover:border-primary-500 hover:bg-primary-50 transition group">
                            <div class="text-center">
                                <div class="bg-primary-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:bg-primary-200 transition">
                                    <svg class="w-8 h-8 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <h3 class="font-semibold text-gray-900 mb-2">Hantar Semua</h3>
                                <p class="text-sm text-gray-500">Hantar peringatan kepada semua penduduk dengan bil belum bayar</p>
                                <p class="text-xs text-primary-600 mt-2 font-medium">{{ $overdueCount + $unpaidCount }} bil</p>
                            </div>
                        </button>
                    </form>

                    <!-- Send Overdue Only -->
                    <form action="{{ route('admin.bills.send-reminders') }}" method="POST" onsubmit="return confirm('Adakah anda pasti mahu menghantar peringatan bil TERTUNGGAK?')">
                        @csrf
                        <input type="hidden" name="type" value="overdue">
                        <button type="submit" class="w-full p-6 border-2 border-red-200 rounded-xl hover:border-red-500 hover:bg-red-50 transition group" {{ $overdueCount == 0 ? 'disabled' : '' }}>
                            <div class="text-center">
                                <div class="bg-red-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:bg-red-200 transition">
                                    <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <h3 class="font-semibold text-gray-900 mb-2">Bil Tertunggak Sahaja</h3>
                                <p class="text-sm text-gray-500">Hantar peringatan segera untuk bil yang telah melepasi tarikh akhir</p>
                                <p class="text-xs text-red-600 mt-2 font-medium">{{ $overdueCount }} bil</p>
                            </div>
                        </button>
                    </form>

                    <!-- Send Unpaid Only -->
                    <form action="{{ route('admin.bills.send-reminders') }}" method="POST" onsubmit="return confirm('Adakah anda pasti mahu menghantar peringatan bil BELUM BAYAR?')">
                        @csrf
                        <input type="hidden" name="type" value="unpaid">
                        <button type="submit" class="w-full p-6 border-2 border-amber-200 rounded-xl hover:border-amber-500 hover:bg-amber-50 transition group" {{ $unpaidCount == 0 ? 'disabled' : '' }}>
                            <div class="text-center">
                                <div class="bg-amber-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:bg-amber-200 transition">
                                    <svg class="w-8 h-8 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <h3 class="font-semibold text-gray-900 mb-2">Bil Belum Bayar Sahaja</h3>
                                <p class="text-sm text-gray-500">Hantar peringatan mesra untuk bil yang belum dibayar</p>
                                <p class="text-xs text-amber-600 mt-2 font-medium">{{ $unpaidCount }} bil</p>
                            </div>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Schedule Info -->
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
            <div class="flex gap-3">
                <svg class="w-6 h-6 text-blue-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div class="text-sm text-blue-800">
                    <p class="font-semibold">Jadual Peringatan Automatik</p>
                    <ul class="mt-2 space-y-1">
                        <li>📅 <strong>1hb setiap bulan, 9:00 pagi</strong> - Peringatan bil belum bayar</li>
                        <li>📅 <strong>15hb setiap bulan, 9:00 pagi</strong> - Peringatan bil tertunggak</li>
                    </ul>
                    <p class="mt-2 text-blue-600">Peringatan di atas dihantar secara automatik oleh sistem. Gunakan butang di atas untuk penghantaran manual.</p>
                </div>
            </div>
        </div>

        <!-- Houses with Overdue Bills -->
        @if($overdueHouses->count() > 0)
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="p-4 lg:p-6 border-b border-gray-100">
                <h2 class="text-lg font-semibold text-gray-900">🚨 Rumah Dengan Bil Tertunggak</h2>
                <p class="text-gray-500 text-sm mt-1">Senarai rumah yang mempunyai bil melepasi tarikh akhir pembayaran</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rumah</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bil Tertunggak</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($overdueHouses as $house)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-4">
                                <div class="font-medium text-gray-900">{{ $house->house_no }}</div>
                                <div class="text-sm text-gray-500">{{ $house->street_name }}</div>
                            </td>
                            <td class="px-4 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    {{ $house->bills_count }} bil tertunggak
                                </span>
                            </td>
                            <td class="px-4 py-4 text-right">
                                <form action="{{ route('admin.bills.send-reminder-house', $house) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="text-primary-600 hover:text-primary-900 text-sm font-medium" onclick="return confirm('Hantar peringatan ke rumah ini?')">
                                        📧 Hantar Peringatan
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <!-- Houses with Unpaid Bills -->
        @if($unpaidHouses->count() > 0)
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="p-4 lg:p-6 border-b border-gray-100">
                <h2 class="text-lg font-semibold text-gray-900">⏳ Rumah Dengan Bil Belum Bayar</h2>
                <p class="text-gray-500 text-sm mt-1">Senarai rumah yang mempunyai bil belum dibayar (belum melepasi tarikh akhir)</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rumah</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bil Belum Bayar</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($unpaidHouses as $house)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-4">
                                <div class="font-medium text-gray-900">{{ $house->house_no }}</div>
                                <div class="text-sm text-gray-500">{{ $house->street_name }}</div>
                            </td>
                            <td class="px-4 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
                                    {{ $house->bills_count }} bil belum bayar
                                </span>
                            </td>
                            <td class="px-4 py-4 text-right">
                                <form action="{{ route('admin.bills.send-reminder-house', $house) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="text-primary-600 hover:text-primary-900 text-sm font-medium" onclick="return confirm('Hantar peringatan ke rumah ini?')">
                                        📧 Hantar Peringatan
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>
</x-app-layout>

