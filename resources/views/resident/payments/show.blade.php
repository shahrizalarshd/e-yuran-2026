<x-app-layout>
    <x-slot name="title">{{ __('messages.payment') }} #{{ $payment->payment_no }}</x-slot>

    <div class="max-w-lg mx-auto">
        <!-- Status Card -->
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden mb-4">
            @if($payment->status === 'success')
                <div class="bg-green-500 text-white p-6 text-center">
                    <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <h2 class="text-xl font-bold mb-1">{{ __('messages.payment_success') }}</h2>
                    <p class="text-green-100">{{ $payment->paid_at->format('d/m/Y H:i') }}</p>
                </div>
            @elseif($payment->status === 'pending')
                <div class="bg-yellow-500 text-white p-6 text-center">
                    <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                    </div>
                    <h2 class="text-xl font-bold mb-1">{{ __('messages.processing') }}</h2>
                    <p class="text-yellow-100">{{ __('Pembayaran sedang diproses') }}</p>
                </div>
            @else
                <div class="bg-red-500 text-white p-6 text-center">
                    <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </div>
                    <h2 class="text-xl font-bold mb-1">{{ __('messages.payment_failed') }}</h2>
                    <p class="text-red-100">{{ __('Pembayaran tidak berjaya') }}</p>
                </div>
            @endif

            <!-- Amount -->
            <div class="p-6 text-center border-b border-gray-100">
                <p class="text-sm text-gray-500 mb-1">{{ __('messages.amount') }}</p>
                <p class="text-3xl font-bold text-gray-900">RM {{ number_format($payment->amount, 2) }}</p>
            </div>

            <!-- Details -->
            <div class="p-6 space-y-4">
                <div class="flex justify-between">
                    <span class="text-gray-500">{{ __('messages.payment_no') }}</span>
                    <span class="font-medium text-gray-900">{{ $payment->payment_no }}</span>
                </div>
                
                @if($payment->toyyibpay_ref)
                    <div class="flex justify-between">
                        <span class="text-gray-500">{{ __('messages.transaction_id') }}</span>
                        <span class="font-medium text-gray-900">{{ $payment->toyyibpay_ref }}</span>
                    </div>
                @endif

                <div class="flex justify-between">
                    <span class="text-gray-500">{{ __('messages.payment_type') }}</span>
                    <span class="font-medium text-gray-900">{{ $payment->payment_type_text }}</span>
                </div>

                <div class="flex justify-between">
                    <span class="text-gray-500">{{ __('messages.date') }}</span>
                    <span class="font-medium text-gray-900">{{ $payment->created_at->format('d/m/Y H:i') }}</span>
                </div>
            </div>
        </div>

        <!-- Bills Paid -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-4">
            <div class="p-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900">{{ __('Bil yang Dibayar') }}</h3>
            </div>
            <div class="divide-y divide-gray-100">
                @foreach($payment->bills as $bill)
                    <div class="p-4 flex items-center justify-between">
                        <div>
                            <p class="font-medium text-gray-900">{{ $bill->bill_period }}</p>
                            <p class="text-sm text-gray-500">{{ $bill->bill_no }}</p>
                        </div>
                        <span class="font-medium text-gray-900">RM {{ number_format($bill->pivot->amount, 2) }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Actions -->
        <div class="flex gap-3">
            <a href="{{ route('resident.payments.index') }}" class="flex-1 py-3 bg-white text-gray-700 font-semibold rounded-xl border border-gray-300 hover:bg-gray-50 transition min-h-touch flex items-center justify-center">
                {{ __('messages.back') }}
            </a>
            <a href="{{ route('resident.dashboard') }}" class="flex-1 py-3 bg-primary-600 text-white font-semibold rounded-xl hover:bg-primary-700 transition min-h-touch flex items-center justify-center">
                {{ __('messages.dashboard') }}
            </a>
        </div>
    </div>
</x-app-layout>

