<x-app-layout>
    <x-slot name="title">{{ __('messages.pay_bill') }}</x-slot>

    <div class="max-w-2xl mx-auto" x-data="paymentForm()">
        <!-- Step Indicator -->
        <div class="bg-white rounded-xl shadow-sm p-4 mb-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-primary-600 text-white rounded-full flex items-center justify-center text-sm font-bold">1</div>
                    <span class="text-sm font-semibold text-primary-700">{{ __('Pilih Bil') }}</span>
                </div>
                <div class="flex-1 h-0.5 bg-gray-200 mx-3"></div>
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-gray-200 text-gray-400 rounded-full flex items-center justify-center text-sm font-bold">2</div>
                    <span class="text-sm text-gray-400">{{ __('Semak') }}</span>
                </div>
                <div class="flex-1 h-0.5 bg-gray-200 mx-3"></div>
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-gray-200 text-gray-400 rounded-full flex items-center justify-center text-sm font-bold">3</div>
                    <span class="text-sm text-gray-400">{{ __('Bayar') }}</span>
                </div>
            </div>
        </div>

        <!-- House Info -->
        <div class="bg-white rounded-xl shadow-sm p-4 mb-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-primary-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                </div>
                <div>
                    <p class="font-medium text-gray-900">{{ $house->full_address }}</p>
                    <p class="text-sm text-gray-500">{{ __('messages.select_bills') }}</p>
                </div>
            </div>
        </div>

        <!-- Outstanding Summary Card -->
        <div class="bg-gradient-to-r from-red-50 to-orange-50 border border-red-100 rounded-xl p-4 mb-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-red-600 font-medium">{{ __('messages.total_outstanding') }}</p>
                    <p class="text-2xl font-bold text-red-700">RM {{ number_format($unpaidBills->sum('outstanding_amount'), 2) }}</p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-red-600">{{ $unpaidBills->count() }} {{ __('bil tertunggak') }}</p>
                </div>
            </div>
        </div>

        <!-- Payment Type Tabs -->
        <div class="bg-white rounded-xl shadow-sm p-2 mb-4">
            <div class="flex gap-2">
                <button type="button" @click="selectAll('current')" :class="{'bg-primary-100 text-primary-700': paymentType === 'current_month', 'text-gray-600': paymentType !== 'current_month'}" class="flex-1 px-3 py-2 rounded-lg text-sm font-medium transition min-h-touch text-center">
                    <span class="block">{{ __('messages.current_month') }}</span>
                    <span class="block text-xs mt-0.5 opacity-70">{{ __('Bulan ini sahaja') }}</span>
                </button>
                <button type="button" @click="selectAll('selected')" :class="{'bg-primary-100 text-primary-700': paymentType === 'selected_months', 'text-gray-600': paymentType !== 'selected_months'}" class="flex-1 px-3 py-2 rounded-lg text-sm font-medium transition min-h-touch text-center">
                    <span class="block">{{ __('messages.choose_months') }}</span>
                    <span class="block text-xs mt-0.5 opacity-70">{{ __('Pilih sendiri') }}</span>
                </button>
                <button type="button" @click="selectAll('yearly')" :class="{'bg-primary-100 text-primary-700': paymentType === 'yearly', 'text-gray-600': paymentType !== 'yearly'}" class="flex-1 px-3 py-2 rounded-lg text-sm font-medium transition min-h-touch text-center">
                    <span class="block">{{ __('messages.yearly') }}</span>
                    <span class="block text-xs mt-0.5 opacity-70">{{ __('Semua bulan tahun ini') }}</span>
                </button>
            </div>
        </div>

        <form action="{{ route('resident.payments.confirm') }}" method="POST">
            @csrf
            <input type="hidden" name="payment_type" x-model="paymentType">

            <!-- Bill Selection - Grouped by Year -->
            <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-4">
                <div class="p-4 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="font-semibold text-gray-900">{{ __('messages.unpaid') }}</h2>
                    <button type="button" @click="toggleAll()" class="px-4 py-2 text-sm font-semibold rounded-lg transition" :class="allSelected ? 'bg-red-100 text-red-700 hover:bg-red-200' : 'bg-primary-600 text-white hover:bg-primary-700'">
                        <span x-text="allSelected ? '{{ __('Nyahpilih Semua') }}' : '{{ __('Pilih Semua') }}'"></span>
                    </button>
                </div>
                
                @if($unpaidBills->isEmpty())
                    <div class="p-8 text-center">
                        <p class="text-gray-500">{{ __('Tiada bil tertunggak') }}</p>
                    </div>
                @else
                    @foreach($unpaidBills->groupBy('bill_year') as $year => $bills)
                        <!-- Year Group Header -->
                        <button type="button" @click="toggleYear({{ $year }})" class="w-full p-3 bg-gray-50 border-b border-gray-100 flex items-center justify-between hover:bg-gray-100 transition">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-gray-500 transition-transform" :class="{ 'rotate-90': expandedYears.includes({{ $year }}) }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                                <span class="font-semibold text-gray-700">{{ $year }}</span>
                                <span class="text-xs bg-gray-200 text-gray-600 px-2 py-0.5 rounded-full">{{ $bills->count() }} {{ __('bil') }}</span>
                            </div>
                            <span class="text-sm font-medium text-gray-600">RM {{ number_format($bills->sum('outstanding_amount'), 2) }}</span>
                        </button>
                        <!-- Year Group Bills -->
                        <div x-show="expandedYears.includes({{ $year }})" x-transition class="divide-y divide-gray-100">
                            @foreach($bills as $bill)
                                <label class="p-4 flex items-center gap-4 cursor-pointer hover:bg-gray-50 min-h-touch">
                                    <input type="checkbox" name="bill_ids[]" value="{{ $bill->id }}" x-model="selectedBills" class="w-5 h-5 text-primary-600 border-gray-300 rounded focus:ring-primary-500" @if(in_array($bill->id, $selectedBillIds)) checked @endif>
                                    <div class="flex-1 flex items-center justify-between">
                                        <div>
                                            <p class="font-medium text-gray-900">{{ $bill->bill_period }}</p>
                                            <p class="text-sm text-gray-500">{{ __('messages.due_date') }}: {{ $bill->due_date->format('d/m/Y') }}</p>
                                        </div>
                                        <div class="text-right">
                                            <p class="font-semibold text-gray-900">RM {{ number_format($bill->outstanding_amount, 2) }}</p>
                                            @if($bill->is_overdue)
                                                @php
                                                    $overdueMonths = (int) $bill->due_date->diffInMonths(now());
                                                    $overdueYears = (int) floor($overdueMonths / 12);
                                                    $remainingMonths = $overdueMonths % 12;
                                                @endphp
                                                <span class="text-xs text-red-600">
                                                    {{ __('messages.overdue') }}
                                                    @if($overdueYears > 0)
                                                        · {{ $overdueYears }} {{ __('tahun') }}
                                                        @if($remainingMonths > 0)
                                                            {{ $remainingMonths }} {{ __('bulan') }}
                                                        @endif
                                                    @else
                                                        · {{ $overdueMonths }} {{ __('bulan') }}
                                                    @endif
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    @endforeach
                @endif
            </div>

            <!-- Total & Submit -->
            <div class="bg-white rounded-xl shadow-sm p-4 sticky bottom-20 lg:bottom-4">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <p class="text-sm text-gray-500">{{ __('messages.total') }}</p>
                        <p class="text-2xl font-bold text-gray-900">RM <span x-text="totalAmount.toFixed(2)">0.00</span></p>
                    </div>
                    <span x-show="selectedBills.length > 0" class="px-3 py-1 bg-primary-100 text-primary-700 rounded-full text-sm font-medium">
                        <span x-text="selectedBills.length"></span> {{ __('bil dipilih') }}
                    </span>
                </div>

                <!-- Guide message when no bills selected -->
                <div x-show="selectedBills.length === 0" class="mb-3 p-3 bg-blue-50 rounded-lg flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-sm text-blue-700">{{ __('Sila pilih bil yang ingin dibayar dengan menanda kotak di sebelah kiri') }}</p>
                </div>

                <button type="submit" :disabled="selectedBills.length === 0" class="w-full py-3 bg-primary-600 text-white font-semibold rounded-xl hover:bg-primary-700 transition min-h-touch disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                    <svg x-show="selectedBills.length > 0" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                    <span x-show="selectedBills.length === 0">{{ __('messages.proceed_to_payment') }}</span>
                    <span x-show="selectedBills.length > 0">{{ __('Bayar') }} RM <span x-text="totalAmount.toFixed(2)"></span> (<span x-text="selectedBills.length"></span> {{ __('bil') }})</span>
                </button>
            </div>
        </form>

        <!-- FAQ Section -->
        <div class="mt-4 space-y-2">
            <h3 class="font-semibold text-gray-700 text-sm px-1">{{ __('Soalan Lazim') }}</h3>

            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <button type="button" @click="openFaq = openFaq === 1 ? null : 1" class="w-full p-4 flex items-center justify-between text-left hover:bg-gray-50">
                    <span class="text-sm font-medium text-gray-700">{{ __('Bagaimana cara membayar bil?') }}</span>
                    <svg class="w-4 h-4 text-gray-400 transition-transform" :class="{ 'rotate-180': openFaq === 1 }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="openFaq === 1" x-transition class="px-4 pb-4">
                    <p class="text-sm text-gray-500">{{ __('Pilih bil yang ingin dibayar menggunakan kotak semak (checkbox), semak jumlah, kemudian tekan butang "Bayar". Anda akan dibawa ke ToyyibPay untuk membuat pembayaran.') }}</p>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <button type="button" @click="openFaq = openFaq === 2 ? null : 2" class="w-full p-4 flex items-center justify-between text-left hover:bg-gray-50">
                    <span class="text-sm font-medium text-gray-700">{{ __('Bolehkah saya bayar beberapa bulan sekaligus?') }}</span>
                    <svg class="w-4 h-4 text-gray-400 transition-transform" :class="{ 'rotate-180': openFaq === 2 }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="openFaq === 2" x-transition class="px-4 pb-4">
                    <p class="text-sm text-gray-500">{{ __('Ya! Anda boleh memilih beberapa bil sekaligus atau gunakan butang "Pilih Semua" untuk memilih semua bil tertunggak. Anda juga boleh gunakan tab "Setahun" untuk memilih semua bil tahun semasa.') }}</p>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <button type="button" @click="openFaq = openFaq === 3 ? null : 3" class="w-full p-4 flex items-center justify-between text-left hover:bg-gray-50">
                    <span class="text-sm font-medium text-gray-700">{{ __('Apa akan berlaku jika pembayaran gagal?') }}</span>
                    <svg class="w-4 h-4 text-gray-400 transition-transform" :class="{ 'rotate-180': openFaq === 3 }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="openFaq === 3" x-transition class="px-4 pb-4">
                    <p class="text-sm text-gray-500">{{ __('Jika pembayaran gagal, bil anda tidak akan dikemas kini dan tiada caj akan dikenakan. Anda boleh cuba membuat pembayaran semula pada bila-bila masa.') }}</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        function paymentForm() {
            const bills = @json($billsJson);
            const currentYear = {{ now()->year }};
            const currentMonth = {{ now()->month }};
            const preselected = @json($selectedBillIds);
            const years = [...new Set(bills.map(b => Number(b.year)))];

            return {
                paymentType: '{{ $paymentType === 'current' ? 'current_month' : ($paymentType === 'yearly' ? 'yearly' : 'selected_months') }}',
                selectedBills: preselected.map(String),
                bills: bills,
                expandedYears: [...years],
                openFaq: null,
                
                get totalAmount() {
                    return this.bills
                        .filter(b => this.selectedBills.includes(String(b.id)))
                        .reduce((sum, b) => sum + parseFloat(b.amount), 0);
                },
                
                get allSelected() {
                    return this.bills.length > 0 && this.selectedBills.length === this.bills.length;
                },
                
                toggleYear(year) {
                    const idx = this.expandedYears.indexOf(year);
                    if (idx > -1) {
                        this.expandedYears.splice(idx, 1);
                    } else {
                        this.expandedYears.push(year);
                    }
                },
                
                toggleAll() {
                    if (this.allSelected) {
                        this.selectedBills = [];
                    } else {
                        this.selectedBills = this.bills.map(b => String(b.id));
                    }
                    this.paymentType = 'selected_months';
                },
                
                selectAll(type) {
                    if (type === 'current') {
                        const currentBill = this.bills.find(b => Number(b.year) === currentYear && Number(b.month) === currentMonth);
                        this.selectedBills = currentBill ? [String(currentBill.id)] : [];
                        this.paymentType = 'current_month';
                    } else if (type === 'yearly') {
                        this.selectedBills = this.bills.filter(b => Number(b.year) === currentYear).map(b => String(b.id));
                        this.paymentType = 'yearly';
                    } else {
                        this.paymentType = 'selected_months';
                    }
                }
            };
        }
    </script>
</x-app-layout>
