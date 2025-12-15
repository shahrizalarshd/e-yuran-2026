<x-app-layout>
    <x-slot name="title">{{ __('messages.dashboard') }}</x-slot>

    <div class="space-y-6">
        <!-- Stats Grid -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Total Collection -->
            <div class="bg-white rounded-xl p-4 lg:p-6 shadow-sm">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm text-gray-500">{{ __('messages.total_collection') }}</p>
                        <p class="text-xl lg:text-2xl font-bold text-gray-900 mt-1">RM {{ number_format($stats['total_collection'], 2) }}</p>
                    </div>
                    <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Total Outstanding -->
            <div class="bg-white rounded-xl p-4 lg:p-6 shadow-sm">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm text-gray-500">{{ __('messages.total_outstanding') }}</p>
                        <p class="text-xl lg:text-2xl font-bold text-red-600 mt-1">RM {{ number_format($stats['total_outstanding'], 2) }}</p>
                    </div>
                    <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Registered Houses -->
            <div class="bg-white rounded-xl p-4 lg:p-6 shadow-sm">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm text-gray-500">{{ __('messages.registered_houses') }}</p>
                        <p class="text-xl lg:text-2xl font-bold text-gray-900 mt-1">{{ $stats['registered_houses'] }}/{{ $stats['total_houses'] }}</p>
                    </div>
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Overdue Bills -->
            <div class="bg-white rounded-xl p-4 lg:p-6 shadow-sm">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm text-gray-500">{{ __('messages.overdue') }}</p>
                        <p class="text-xl lg:text-2xl font-bold text-orange-600 mt-1">{{ $stats['overdue_count'] }}</p>
                    </div>
                    <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        @if(auth()->user()->isSuperAdmin() || auth()->user()->isTreasurer())
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            @if(auth()->user()->isSuperAdmin())
            <a href="{{ route('admin.bills.generate.form') }}" class="bg-primary-600 text-white rounded-xl p-4 hover:bg-primary-700 transition min-h-touch flex items-center gap-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                <span class="font-medium">{{ __('messages.generate_bills') }}</span>
            </a>
            @endif

            <a href="{{ route('admin.bills.outstanding') }}" class="bg-white rounded-xl p-4 border-2 border-gray-200 hover:border-primary-300 transition min-h-touch flex items-center gap-3">
                <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span class="font-medium text-gray-700">{{ __('messages.outstanding_report') }}</span>
            </a>

            <a href="{{ route('admin.payments.report') }}" class="bg-white rounded-xl p-4 border-2 border-gray-200 hover:border-primary-300 transition min-h-touch flex items-center gap-3">
                <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                <span class="font-medium text-gray-700">{{ __('messages.collection_report') }}</span>
            </a>

            <a href="{{ route('admin.verifications.pending') }}" class="bg-white rounded-xl p-4 border-2 border-gray-200 hover:border-primary-300 transition min-h-touch flex items-center gap-3">
                <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="font-medium text-gray-700">{{ __('messages.user_verification') }}</span>
            </a>
        </div>
        @endif

        <!-- Analytics Charts Section -->
        @if(auth()->user()->isSuperAdmin() || auth()->user()->isTreasurer())
        <!-- Analytics Header with Filter -->
        <div class="bg-gradient-to-r from-primary-600 to-primary-700 rounded-xl p-4 lg:p-6 shadow-sm">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="text-white">
                    <h2 class="text-lg font-semibold">{{ __('Analitik Kewangan') }}</h2>
                    <p class="text-primary-100 text-sm">{{ __('Jumlah Kutipan') }} {{ $currentYear }}: <span class="font-bold text-white">RM {{ number_format($yearlyTotal, 2) }}</span></p>
                </div>
                <form action="{{ route('admin.dashboard') }}" method="GET" class="flex flex-wrap items-center gap-2 sm:gap-3">
                    <div class="flex items-center gap-2">
                        <label class="text-white text-xs sm:text-sm font-medium whitespace-nowrap">{{ __('Tahun') }}:</label>
                        <select name="year" onchange="this.form.submit()" class="rounded-lg border-0 bg-white/20 text-white text-sm font-medium focus:ring-2 focus:ring-white/50 min-w-[80px] backdrop-blur">
                            @foreach($availableYears as $year)
                                <option value="{{ $year }}" {{ $selectedYear == $year ? 'selected' : '' }} class="text-gray-900">{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-center gap-2">
                        <label class="text-white text-xs sm:text-sm font-medium whitespace-nowrap">vs</label>
                        <select name="compare" onchange="this.form.submit()" class="rounded-lg border-0 bg-white/20 text-white text-sm font-medium focus:ring-2 focus:ring-white/50 min-w-[80px] backdrop-blur">
                            @foreach($availableYears as $year)
                                @if($year != $selectedYear)
                                    <option value="{{ $year }}" {{ $compareYear == $year ? 'selected' : '' }} class="text-gray-900">{{ $year }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 lg:gap-6">
            <!-- Monthly Collection Chart -->
            <div class="lg:col-span-2 bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="p-3 sm:p-4 border-b border-gray-100">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                        <div>
                            <h2 class="font-semibold text-gray-900 text-sm sm:text-base">{{ __('Kutipan Bulanan') }}</h2>
                            <p class="text-xs sm:text-sm text-gray-500">{{ __('Perbandingan') }} {{ $currentYear }} vs {{ $compareYear }}</p>
                        </div>
                        <div class="flex items-center gap-3 sm:gap-4 text-xs sm:text-sm">
                            <div class="flex items-center gap-1.5 sm:gap-2">
                                <div class="w-2.5 h-2.5 sm:w-3 sm:h-3 rounded-full bg-primary-500"></div>
                                <span class="text-gray-600">{{ $currentYear }}</span>
                            </div>
                            <div class="flex items-center gap-1.5 sm:gap-2">
                                <div class="w-2.5 h-2.5 sm:w-3 sm:h-3 rounded-full bg-gray-300"></div>
                                <span class="text-gray-600">{{ $compareYear }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="p-3 sm:p-4">
                    <div class="relative h-[200px] sm:h-[250px]">
                        <canvas id="monthlyCollectionChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Collection Rate & Bill Status -->
            <div class="grid grid-cols-2 lg:grid-cols-1 gap-4 lg:gap-6">
                <!-- Collection Rate -->
                <div class="bg-white rounded-xl shadow-sm p-4 sm:p-6">
                    <div class="flex items-center justify-between mb-3 sm:mb-4">
                        <h3 class="font-semibold text-gray-900 text-sm sm:text-base">{{ __('Kadar Kutipan') }}</h3>
                        <span class="text-xs bg-primary-100 text-primary-700 px-2 py-0.5 sm:py-1 rounded-full font-medium">{{ $currentYear }}</span>
                    </div>
                    <div class="flex items-center justify-center">
                        <div class="relative w-24 h-24 sm:w-32 sm:h-32">
                            <canvas id="collectionRateChart"></canvas>
                            <div class="absolute inset-0 flex items-center justify-center">
                                <span class="text-lg sm:text-2xl font-bold text-gray-900">{{ $collectionRate }}%</span>
                            </div>
                        </div>
                    </div>
                    <p class="text-center text-xs sm:text-sm text-gray-500 mt-3 sm:mt-4">{{ __('Bil yang telah dibayar') }}</p>
                </div>

                <!-- Bill Status Distribution -->
                <div class="bg-white rounded-xl shadow-sm p-4 sm:p-6">
                    <div class="flex items-center justify-between mb-3 sm:mb-4">
                        <h3 class="font-semibold text-gray-900 text-sm sm:text-base">{{ __('Status Bil') }}</h3>
                        <span class="text-xs bg-primary-100 text-primary-700 px-2 py-0.5 sm:py-1 rounded-full font-medium">{{ $currentYear }}</span>
                    </div>
                    <div class="relative h-[100px] sm:h-[130px]">
                        <canvas id="billStatusChart"></canvas>
                    </div>
                    <div class="mt-3 sm:mt-4 space-y-1.5 sm:space-y-2">
                        <div class="flex items-center justify-between text-xs sm:text-sm">
                            <div class="flex items-center gap-1.5 sm:gap-2">
                                <div class="w-2.5 h-2.5 sm:w-3 sm:h-3 rounded-full bg-green-500"></div>
                                <span class="text-gray-600">{{ __('messages.paid') }}</span>
                            </div>
                            <span class="font-medium text-gray-900">{{ $billStatusData['paid'] ?? 0 }}</span>
                        </div>
                        <div class="flex items-center justify-between text-xs sm:text-sm">
                            <div class="flex items-center gap-1.5 sm:gap-2">
                                <div class="w-2.5 h-2.5 sm:w-3 sm:h-3 rounded-full bg-red-500"></div>
                                <span class="text-gray-600">{{ __('messages.unpaid') }}</span>
                            </div>
                            <span class="font-medium text-gray-900">{{ $billStatusData['unpaid'] ?? 0 }}</span>
                        </div>
                        <div class="flex items-center justify-between text-xs sm:text-sm">
                            <div class="flex items-center gap-1.5 sm:gap-2">
                                <div class="w-2.5 h-2.5 sm:w-3 sm:h-3 rounded-full bg-yellow-500"></div>
                                <span class="text-gray-600">{{ __('messages.partial') }}</span>
                            </div>
                            <span class="font-medium text-gray-900">{{ $billStatusData['partial'] ?? 0 }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Weekly Collection Chart -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="p-3 sm:p-4 border-b border-gray-100">
                <h2 class="font-semibold text-gray-900 text-sm sm:text-base">{{ __('Kutipan 7 Hari Terakhir') }}</h2>
            </div>
            <div class="p-3 sm:p-4">
                <div class="relative h-[120px] sm:h-[150px]">
                    <canvas id="weeklyCollectionChart"></canvas>
                </div>
            </div>
        </div>
        @endif

        <div class="grid lg:grid-cols-2 gap-6">
            <!-- Pending Verifications -->
            @if($pendingVerifications->count() > 0)
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="p-4 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="font-semibold text-gray-900">{{ __('messages.pending_verification') }}</h2>
                    <a href="{{ route('admin.verifications.pending') }}" class="text-sm text-primary-600 font-medium">{{ __('Lihat Semua') }}</a>
                </div>
                <div class="divide-y divide-gray-100">
                    @foreach($pendingVerifications as $member)
                        <div class="p-4">
                            <div class="flex items-start justify-between">
                                <div>
                                    <p class="font-medium text-gray-900">{{ $member->resident->name }}</p>
                                    <p class="text-sm text-gray-500">{{ $member->house->full_address }}</p>
                                    <p class="text-xs text-gray-400 mt-1">{{ __('messages.' . $member->relationship) }} • {{ $member->created_at->diffForHumans() }}</p>
                                </div>
                                @if(auth()->user()->canVerifyUsers())
                                <div class="flex gap-2">
                                    <form action="{{ route('admin.verifications.approve', $member) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="p-2 text-green-600 hover:bg-green-50 rounded-lg">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.verifications.reject', $member) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="p-2 text-red-600 hover:bg-red-50 rounded-lg">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Recent Payments -->
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="p-4 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="font-semibold text-gray-900">{{ __('messages.recent_payments') }}</h2>
                    <a href="{{ route('admin.payments.index') }}" class="text-sm text-primary-600 font-medium">{{ __('Lihat Semua') }}</a>
                </div>
                @if($recentPayments->count() > 0)
                    <div class="divide-y divide-gray-100">
                        @foreach($recentPayments as $payment)
                            <a href="{{ route('admin.payments.show', $payment) }}" class="p-4 flex items-center justify-between hover:bg-gray-50">
                                <div>
                                    <p class="font-medium text-gray-900">{{ $payment->house->full_address }}</p>
                                    <p class="text-sm text-gray-500">{{ $payment->resident?->name ?? '-' }} • {{ $payment->paid_at->diffForHumans() }}</p>
                                </div>
                                <span class="font-semibold text-green-600">RM {{ number_format($payment->amount, 2) }}</span>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="p-8 text-center text-gray-500">
                        {{ __('messages.no_data') }}
                    </div>
                @endif
            </div>

            <!-- Overdue Houses -->
            @if($overdueHouses->count() > 0)
            <div class="bg-white rounded-xl shadow-sm overflow-hidden lg:col-span-2">
                <div class="p-4 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="font-semibold text-gray-900">{{ __('Rumah Tertunggak') }}</h2>
                    <a href="{{ route('admin.bills.outstanding') }}" class="text-sm text-primary-600 font-medium">{{ __('Lihat Semua') }}</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('messages.house') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Bil Tertunggak') }}</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('messages.amount') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($overdueHouses as $house)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3">
                                        <a href="{{ route('admin.houses.show', $house) }}" class="font-medium text-gray-900 hover:text-primary-600">
                                            {{ $house->full_address }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-3 text-gray-600">{{ $house->bills->count() }} {{ __('bulan') }}</td>
                                    <td class="px-4 py-3 text-right font-semibold text-red-600">
                                        RM {{ number_format($house->bills->sum(fn($b) => $b->outstanding_amount), 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>
    </div>

    @if(auth()->user()->isSuperAdmin() || auth()->user()->isTreasurer())
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Detect mobile
            const isMobile = window.innerWidth < 640;
            
            // Month labels in current locale
            const monthLabels = [
                '{{ __("messages.january") }}', '{{ __("messages.february") }}', '{{ __("messages.march") }}',
                '{{ __("messages.april") }}', '{{ __("messages.may") }}', '{{ __("messages.june") }}',
                '{{ __("messages.july") }}', '{{ __("messages.august") }}', '{{ __("messages.september") }}',
                '{{ __("messages.october") }}', '{{ __("messages.november") }}', '{{ __("messages.december") }}'
            ];

            // Short month labels (shorter for mobile)
            const shortMonthLabels = monthLabels.map(m => isMobile ? m.substring(0, 1) : m.substring(0, 3));

            // Monthly Collection Chart
            const monthlyCtx = document.getElementById('monthlyCollectionChart');
            if (monthlyCtx) {
                new Chart(monthlyCtx, {
                    type: 'line',
                    data: {
                        labels: shortMonthLabels,
                        datasets: [
                            {
                                label: '{{ $currentYear }}',
                                data: @json($chartMonthlyData),
                                borderColor: 'rgb(22, 163, 74)',
                                backgroundColor: 'rgba(22, 163, 74, 0.1)',
                                tension: 0.4,
                                fill: true,
                                pointRadius: isMobile ? 2 : 4,
                                pointHoverRadius: isMobile ? 4 : 6,
                                borderWidth: isMobile ? 2 : 3
                            },
                            {
                                label: '{{ $compareYear }}',
                                data: @json($chartLastYearData),
                                borderColor: 'rgb(156, 163, 175)',
                                backgroundColor: 'transparent',
                                tension: 0.4,
                                borderDash: [5, 5],
                                pointRadius: isMobile ? 1 : 3,
                                pointHoverRadius: isMobile ? 3 : 5,
                                borderWidth: isMobile ? 1.5 : 2
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: {
                            intersect: false,
                            mode: 'index'
                        },
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                padding: isMobile ? 8 : 12,
                                titleFont: { size: isMobile ? 11 : 14 },
                                bodyFont: { size: isMobile ? 10 : 13 },
                                callbacks: {
                                    label: function(context) {
                                        return context.dataset.label + ': RM ' + context.parsed.y.toLocaleString('en-MY', {minimumFractionDigits: 2});
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: { display: false },
                                ticks: { 
                                    font: { size: isMobile ? 9 : 11 },
                                    maxRotation: isMobile ? 0 : 0
                                }
                            },
                            y: {
                                beginAtZero: true,
                                grid: { color: 'rgba(0,0,0,0.05)' },
                                ticks: {
                                    font: { size: isMobile ? 9 : 11 },
                                    maxTicksLimit: isMobile ? 5 : 8,
                                    callback: function(value) {
                                        if (isMobile && value >= 1000) {
                                            return 'RM ' + (value / 1000).toFixed(0) + 'k';
                                        }
                                        return 'RM ' + value.toLocaleString();
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // Collection Rate Donut Chart
            const rateCtx = document.getElementById('collectionRateChart');
            if (rateCtx) {
                new Chart(rateCtx, {
                    type: 'doughnut',
                    data: {
                        datasets: [{
                            data: [{{ $collectionRate }}, {{ 100 - $collectionRate }}],
                            backgroundColor: ['rgb(22, 163, 74)', 'rgb(229, 231, 235)'],
                            borderWidth: 0,
                            cutout: '75%'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: { display: false },
                            tooltip: { enabled: false }
                        }
                    }
                });
            }

            // Bill Status Chart
            const statusCtx = document.getElementById('billStatusChart');
            if (statusCtx) {
                new Chart(statusCtx, {
                    type: 'doughnut',
                    data: {
                        labels: ['{{ __("messages.paid") }}', '{{ __("messages.unpaid") }}', '{{ __("messages.partial") }}'],
                        datasets: [{
                            data: [
                                {{ $billStatusData['paid'] ?? 0 }},
                                {{ $billStatusData['unpaid'] ?? 0 }},
                                {{ $billStatusData['partial'] ?? 0 }}
                            ],
                            backgroundColor: [
                                'rgb(34, 197, 94)',
                                'rgb(239, 68, 68)',
                                'rgb(234, 179, 8)'
                            ],
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false }
                        }
                    }
                });
            }

            // Weekly Collection Bar Chart
            const weeklyCtx = document.getElementById('weeklyCollectionChart');
            if (weeklyCtx) {
                new Chart(weeklyCtx, {
                    type: 'bar',
                    data: {
                        labels: @json($weeklyLabels),
                        datasets: [{
                            label: '{{ __("messages.total_collection") }}',
                            data: @json($weeklyCollection),
                            backgroundColor: 'rgba(22, 163, 74, 0.8)',
                            borderRadius: isMobile ? 4 : 6,
                            barThickness: isMobile ? 'flex' : 40,
                            maxBarThickness: isMobile ? 30 : 50
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                padding: isMobile ? 8 : 12,
                                titleFont: { size: isMobile ? 11 : 14 },
                                bodyFont: { size: isMobile ? 10 : 13 },
                                callbacks: {
                                    label: function(context) {
                                        return 'RM ' + context.parsed.y.toLocaleString('en-MY', {minimumFractionDigits: 2});
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: { display: false },
                                ticks: {
                                    font: { size: isMobile ? 10 : 12 }
                                }
                            },
                            y: {
                                beginAtZero: true,
                                grid: { color: 'rgba(0,0,0,0.05)' },
                                ticks: {
                                    font: { size: isMobile ? 9 : 11 },
                                    maxTicksLimit: isMobile ? 5 : 8,
                                    callback: function(value) {
                                        if (isMobile && value >= 1000) {
                                            return 'RM ' + (value / 1000).toFixed(0) + 'k';
                                        }
                                        return 'RM ' + value.toLocaleString();
                                    }
                                }
                            }
                        }
                    }
                });
            }
        });
    </script>
    @endpush
    @endif
</x-app-layout>

