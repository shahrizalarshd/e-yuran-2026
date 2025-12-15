<x-app-layout>
    <x-slot name="title">{{ __('messages.dashboard') }}</x-slot>

    <div class="space-y-4 lg:space-y-6">
        <!-- Outstanding Amount Card -->
        <div class="bg-gradient-to-br from-primary-600 to-primary-700 rounded-2xl p-6 text-white shadow-lg">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <p class="text-primary-100 text-sm">{{ __('messages.outstanding_amount') }}</p>
                    <p class="text-3xl lg:text-4xl font-bold mt-1">RM {{ number_format($outstandingAmount, 2) }}</p>
                </div>
                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <div class="flex items-center gap-2 text-sm text-primary-100">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                <span>{{ $house->full_address }}</span>
            </div>

            @if($outstandingAmount > 0 && $primaryMembership->can_pay)
                <a href="{{ route('resident.payments.create') }}" class="mt-4 w-full inline-flex items-center justify-center gap-2 px-4 py-3 bg-white text-primary-700 font-semibold rounded-xl hover:bg-primary-50 transition min-h-touch">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                    {{ __('messages.pay_now') }}
                </a>
            @endif
        </div>

        <!-- Unpaid Membership Fee Alert -->
        @if($unpaidMembershipFee)
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 flex items-start gap-4">
            <div class="w-10 h-10 bg-amber-100 rounded-lg flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <div class="flex-1">
                <h4 class="font-semibold text-amber-800">{{ __('messages.membership_fee') }} {{ __('messages.unpaid') }}</h4>
                <p class="text-sm text-amber-700 mt-1">
                    {{ __('Yuran keahlian sebanyak') }} <strong>RM {{ number_format($unpaidMembershipFee->amount, 2) }}</strong> 
                    {{ __('untuk tahun') }} {{ $unpaidMembershipFee->fee_year }} {{ __('belum dibayar.') }}
                </p>
                <p class="text-xs text-amber-600 mt-2">
                    {{ __('Sila hubungi bendahari untuk membuat pembayaran.') }}
                </p>
            </div>
        </div>
        @endif

        <!-- Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 lg:gap-6">
            <!-- Bill Status Chart (Donut) -->
            <div class="bg-white rounded-xl shadow-sm p-4 lg:p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold text-gray-900">{{ __('Status Bil') }} {{ $currentYear }}</h3>
                    <span class="text-xs text-gray-500">{{ $billStatusData['totalBills'] }} {{ __('bil') }}</span>
                </div>
                <div class="relative">
                    <canvas id="billStatusChart" class="w-full" style="max-height: 200px;"></canvas>
                    <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                        <div class="text-center">
                            <p class="text-2xl font-bold text-gray-900">{{ $billStatusData['data'][0] }}</p>
                            <p class="text-xs text-gray-500">{{ __('Dibayar') }}</p>
                        </div>
                    </div>
                </div>
                <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-green-500"></span>
                        <span class="text-gray-600">{{ __('messages.paid') }}: RM {{ number_format($billStatusData['paidAmount'], 2) }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-red-500"></span>
                        <span class="text-gray-600">{{ __('messages.unpaid') }}: RM {{ number_format($billStatusData['unpaidAmount'], 2) }}</span>
                    </div>
                </div>
            </div>

            <!-- Payment History Chart (Bar) -->
            <div class="bg-white rounded-xl shadow-sm p-4 lg:p-6">
                <h3 class="font-semibold text-gray-900 mb-4">{{ __('Sejarah Pembayaran') }}</h3>
                <canvas id="paymentHistoryChart" class="w-full" style="max-height: 200px;"></canvas>
                <p class="text-xs text-gray-500 text-center mt-3">{{ __('12 bulan lepas') }}</p>
            </div>
        </div>

        <!-- Quick Actions (Desktop) -->
        <div class="hidden lg:grid lg:grid-cols-3 gap-4">
            <a href="{{ route('resident.payments.create', ['type' => 'current']) }}" class="bg-white rounded-xl p-4 shadow-sm hover:shadow-md transition flex items-center gap-4">
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <p class="font-medium text-gray-900">{{ __('messages.current_month') }}</p>
                    <p class="text-sm text-gray-500">{{ __('Bayar bulan semasa') }}</p>
                </div>
            </a>

            <a href="{{ route('resident.payments.create', ['type' => 'selected']) }}" class="bg-white rounded-xl p-4 shadow-sm hover:shadow-md transition flex items-center gap-4">
                <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                    </svg>
                </div>
                <div>
                    <p class="font-medium text-gray-900">{{ __('messages.selected_months') }}</p>
                    <p class="text-sm text-gray-500">{{ __('Pilih bulan untuk bayar') }}</p>
                </div>
            </a>

            <a href="{{ route('resident.payments.create', ['type' => 'yearly']) }}" class="bg-white rounded-xl p-4 shadow-sm hover:shadow-md transition flex items-center gap-4">
                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                    </svg>
                </div>
                <div>
                    <p class="font-medium text-gray-900">{{ __('messages.yearly') }}</p>
                    <p class="text-sm text-gray-500">{{ __('Bayar setahun penuh') }}</p>
                </div>
            </a>
        </div>

        <!-- Unpaid Bills -->
        @if($unpaidBills->count() > 0)
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="p-4 border-b border-gray-100">
                    <h2 class="font-semibold text-gray-900">{{ __('messages.unpaid') }} ({{ $unpaidBills->count() }})</h2>
                </div>
                <div class="divide-y divide-gray-100">
                    @foreach($unpaidBills as $bill)
                        <div class="p-4 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
                                    <span class="text-red-600 font-semibold text-sm">{{ $bill->bill_month }}</span>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">{{ $bill->bill_period }}</p>
                                    <p class="text-sm text-gray-500">{{ __('messages.due_date') }}: {{ $bill->due_date->format('d/m/Y') }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold text-gray-900">RM {{ number_format($bill->outstanding_amount, 2) }}</p>
                                @if($bill->is_overdue)
                                    <span class="text-xs text-red-600">{{ __('messages.overdue') }}</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <div class="bg-white rounded-xl shadow-sm p-8 text-center">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h3 class="font-semibold text-gray-900 mb-1">{{ __('Tiada tunggakan') }}</h3>
                <p class="text-gray-500 text-sm">{{ __('Semua bil anda telah dibayar') }}</p>
            </div>
        @endif

        <!-- Recent Payments -->
        @if($recentPayments->count() > 0)
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="p-4 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="font-semibold text-gray-900">{{ __('messages.recent_payments') }}</h2>
                    <a href="{{ route('resident.payments.index') }}" class="text-sm text-primary-600 font-medium">{{ __('Lihat Semua') }}</a>
                </div>
                <div class="divide-y divide-gray-100">
                    @foreach($recentPayments as $payment)
                        <a href="{{ route('resident.payments.show', $payment) }}" class="p-4 flex items-center justify-between hover:bg-gray-50">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">{{ $payment->payment_no }}</p>
                                    <p class="text-sm text-gray-500">{{ $payment->paid_at->format('d/m/Y H:i') }}</p>
                                </div>
                            </div>
                            <span class="font-semibold text-green-600">RM {{ number_format($payment->amount, 2) }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Paid Bills -->
        @if($paidBills->count() > 0)
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="p-4 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="font-semibold text-gray-900">{{ __('messages.paid') }}</h2>
                    <a href="{{ route('resident.bills.index', ['status' => 'paid']) }}" class="text-sm text-primary-600 font-medium">{{ __('Lihat Semua') }}</a>
                </div>
                <div class="divide-y divide-gray-100">
                    @foreach($paidBills->take(5) as $bill)
                        <div class="p-4 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                                    <span class="text-green-600 font-semibold text-sm">{{ $bill->bill_month }}</span>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">{{ $bill->bill_period }}</p>
                                    <p class="text-sm text-gray-500">{{ __('messages.paid_at') }}: {{ $bill->paid_at->format('d/m/Y') }}</p>
                                </div>
                            </div>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                {{ __('messages.paid') }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Bill Status Donut Chart
            const billStatusCtx = document.getElementById('billStatusChart');
            if (billStatusCtx) {
                new Chart(billStatusCtx, {
                    type: 'doughnut',
                    data: {
                        labels: {!! json_encode($billStatusData['labels']) !!},
                        datasets: [{
                            data: {!! json_encode($billStatusData['data']) !!},
                            backgroundColor: {!! json_encode($billStatusData['colors']) !!},
                            borderWidth: 0,
                            cutout: '70%'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                padding: 12,
                                titleFont: {
                                    size: 14,
                                    weight: 'bold'
                                },
                                bodyFont: {
                                    size: 13
                                },
                                callbacks: {
                                    label: function(context) {
                                        return context.label + ': ' + context.raw + ' bil';
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // Payment History Bar Chart
            const paymentHistoryCtx = document.getElementById('paymentHistoryChart');
            if (paymentHistoryCtx) {
                new Chart(paymentHistoryCtx, {
                    type: 'bar',
                    data: {
                        labels: {!! json_encode($paymentHistoryData['labels']) !!},
                        datasets: [{
                            label: 'Pembayaran (RM)',
                            data: {!! json_encode($paymentHistoryData['data']) !!},
                            backgroundColor: function(context) {
                                const value = context.raw;
                                return value > 0 ? 'rgba(34, 197, 94, 0.8)' : 'rgba(239, 68, 68, 0.3)';
                            },
                            borderColor: function(context) {
                                const value = context.raw;
                                return value > 0 ? 'rgb(34, 197, 94)' : 'rgb(239, 68, 68)';
                            },
                            borderWidth: 1,
                            borderRadius: 4,
                            barThickness: 'flex',
                            maxBarThickness: 24
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                padding: 12,
                                callbacks: {
                                    label: function(context) {
                                        return 'RM ' + context.raw.toFixed(2);
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    font: {
                                        size: 10
                                    },
                                    maxRotation: 45,
                                    minRotation: 45
                                }
                            },
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: 'rgba(0, 0, 0, 0.05)'
                                },
                                ticks: {
                                    font: {
                                        size: 10
                                    },
                                    callback: function(value) {
                                        return 'RM ' + value;
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
</x-app-layout>

