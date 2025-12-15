<x-app-layout>
    <x-slot name="title">{{ __('messages.payment_confirmation') }}</x-slot>

    <div class="max-w-lg mx-auto">
        <!-- Confirmation Card -->
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <!-- Header -->
            <div class="bg-primary-600 text-white p-6 text-center">
                <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h2 class="text-xl font-bold mb-1">{{ __('messages.payment_confirmation') }}</h2>
                <p class="text-primary-100 text-sm">{{ __('Sila semak maklumat pembayaran') }}</p>
            </div>

            <!-- Details -->
            <div class="p-6 space-y-4">
                <!-- House -->
                <div class="flex items-center gap-3 pb-4 border-b border-gray-100">
                    <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">{{ __('messages.house') }}</p>
                        <p class="font-medium text-gray-900">{{ $house->full_address }}</p>
                    </div>
                </div>

                <!-- Selected Bills -->
                <div>
                    <p class="text-sm text-gray-500 mb-3">{{ __('Bil yang akan dibayar') }} ({{ $bills->count() }})</p>
                    <div class="space-y-2">
                        @foreach($bills as $bill)
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <span class="font-medium">{{ $bill->bill_period }}</span>
                                <span class="text-gray-700">RM {{ number_format($bill->outstanding_amount, 2) }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Total -->
                <div class="pt-4 border-t border-gray-200">
                    <div class="flex items-center justify-between">
                        <span class="text-lg font-semibold text-gray-900">{{ __('messages.total') }}</span>
                        <span class="text-2xl font-bold text-primary-600">RM {{ number_format($totalAmount, 2) }}</span>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="p-6 bg-gray-50 space-y-3">
                <form action="{{ route('resident.payments.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="payment_type" value="{{ $validated['payment_type'] }}">
                    @foreach($validated['bill_ids'] as $billId)
                        <input type="hidden" name="bill_ids[]" value="{{ $billId }}">
                    @endforeach
                    
                    <button type="submit" class="w-full py-3 bg-primary-600 text-white font-semibold rounded-xl hover:bg-primary-700 transition min-h-touch flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                        {{ __('Bayar dengan ToyyibPay') }}
                    </button>
                </form>

                <a href="{{ route('resident.payments.create') }}" class="w-full py-3 bg-white text-gray-700 font-semibold rounded-xl border border-gray-300 hover:bg-gray-50 transition min-h-touch flex items-center justify-center">
                    {{ __('messages.back') }}
                </a>
            </div>
        </div>

        <!-- Payment Notice -->
        <div class="mt-4 p-4 bg-blue-50 rounded-xl">
            <div class="flex gap-3">
                <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div class="text-sm text-blue-800">
                    <p class="font-medium mb-1">{{ __('Maklumat Pembayaran') }}</p>
                    <p>{{ __('Anda akan dialihkan ke ToyyibPay untuk melengkapkan pembayaran. Pastikan anda tidak menutup halaman sehingga pembayaran selesai.') }}</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

