<x-app-layout>
    <x-slot name="title">{{ __('messages.pay_bill') }}</x-slot>

    <div class="max-w-2xl mx-auto" x-data="paymentForm()">
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

        <!-- Payment Type Tabs -->
        <div class="bg-white rounded-xl shadow-sm p-2 mb-4">
            <div class="flex gap-2">
                <button type="button" @click="selectAll('current')" :class="{'bg-primary-100 text-primary-700': paymentType === 'current_month', 'text-gray-600': paymentType !== 'current_month'}" class="flex-1 px-3 py-2 rounded-lg text-sm font-medium transition min-h-touch">
                    {{ __('messages.current_month') }}
                </button>
                <button type="button" @click="selectAll('selected')" :class="{'bg-primary-100 text-primary-700': paymentType === 'selected_months', 'text-gray-600': paymentType !== 'selected_months'}" class="flex-1 px-3 py-2 rounded-lg text-sm font-medium transition min-h-touch">
                    {{ __('messages.selected_months') }}
                </button>
                <button type="button" @click="selectAll('yearly')" :class="{'bg-primary-100 text-primary-700': paymentType === 'yearly', 'text-gray-600': paymentType !== 'yearly'}" class="flex-1 px-3 py-2 rounded-lg text-sm font-medium transition min-h-touch">
                    {{ __('messages.yearly') }}
                </button>
            </div>
        </div>

        <form action="{{ route('resident.payments.confirm') }}" method="POST">
            @csrf
            <input type="hidden" name="payment_type" x-model="paymentType">

            <!-- Bill Selection -->
            <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-4">
                <div class="p-4 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="font-semibold text-gray-900">{{ __('messages.unpaid') }}</h2>
                    <button type="button" @click="toggleAll()" class="text-sm text-primary-600 font-medium">
                        <span x-text="allSelected ? '{{ __('Nyahpilih Semua') }}' : '{{ __('Pilih Semua') }}'"></span>
                    </button>
                </div>
                
                @if($unpaidBills->isEmpty())
                    <div class="p-8 text-center">
                        <p class="text-gray-500">{{ __('Tiada bil tertunggak') }}</p>
                    </div>
                @else
                    <div class="divide-y divide-gray-100">
                        @foreach($unpaidBills as $bill)
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
                                            <span class="text-xs text-red-600">{{ __('messages.overdue') }}</span>
                                        @endif
                                    </div>
                                </div>
                            </label>
                        @endforeach
                    </div>
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
                <button type="submit" :disabled="selectedBills.length === 0" class="w-full py-3 bg-primary-600 text-white font-semibold rounded-xl hover:bg-primary-700 transition min-h-touch disabled:opacity-50 disabled:cursor-not-allowed">
                    {{ __('messages.proceed_to_payment') }}
                </button>
            </div>
        </form>
    </div>

    <script>
        function paymentForm() {
            const bills = @json($billsJson);
            const currentYear = {{ now()->year }};
            const currentMonth = {{ now()->month }};
            const preselected = @json($selectedBillIds);

            return {
                paymentType: '{{ $paymentType === 'current' ? 'current_month' : ($paymentType === 'yearly' ? 'yearly' : 'selected_months') }}',
                selectedBills: preselected.map(String),
                bills: bills,
                
                get totalAmount() {
                    return this.bills
                        .filter(b => this.selectedBills.includes(String(b.id)))
                        .reduce((sum, b) => sum + parseFloat(b.amount), 0);
                },
                
                get allSelected() {
                    return this.selectedBills.length === this.bills.length;
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
                        const currentBill = this.bills.find(b => b.year === currentYear && b.month === currentMonth);
                        this.selectedBills = currentBill ? [String(currentBill.id)] : [];
                        this.paymentType = 'current_month';
                    } else if (type === 'yearly') {
                        this.selectedBills = this.bills.filter(b => b.year === currentYear).map(b => String(b.id));
                        this.paymentType = 'yearly';
                    } else {
                        this.paymentType = 'selected_months';
                    }
                }
            };
        }
    </script>
</x-app-layout>

