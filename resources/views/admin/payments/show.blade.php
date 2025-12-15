<x-app-layout>
    <x-slot name="title">{{ __('messages.payment') }} #{{ $payment->payment_no }}</x-slot>

    <div class="max-w-2xl mx-auto space-y-6">
        <!-- Status Card -->
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            @if($payment->status === 'success')
                <div class="bg-green-500 text-white p-6 text-center">
                    <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <h2 class="text-xl font-bold">{{ __('messages.payment_success') }}</h2>
                </div>
            @elseif($payment->status === 'pending')
                <div class="bg-yellow-500 text-white p-6 text-center">
                    <h2 class="text-xl font-bold">{{ __('messages.processing') }}</h2>
                </div>
            @else
                <div class="bg-red-500 text-white p-6 text-center">
                    <h2 class="text-xl font-bold">{{ __('messages.payment_failed') }}</h2>
                </div>
            @endif

            <!-- Amount -->
            <div class="p-6 text-center border-b border-gray-100">
                <p class="text-sm text-gray-500">{{ __('messages.amount') }}</p>
                <p class="text-3xl font-bold text-gray-900">RM {{ number_format($payment->amount, 2) }}</p>
            </div>

            <!-- Details -->
            <div class="p-6 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-500">{{ __('messages.payment_no') }}</p>
                        <p class="font-medium text-gray-900">{{ $payment->payment_no }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">{{ __('messages.house') }}</p>
                        <p class="font-medium text-gray-900">{{ $payment->house->full_address }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">{{ __('Pembayar') }}</p>
                        <p class="font-medium text-gray-900">{{ $payment->resident?->name ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">{{ __('messages.payment_type') }}</p>
                        <p class="font-medium text-gray-900">{{ $payment->payment_type_text }}</p>
                    </div>
                    @if($payment->toyyibpay_billcode)
                    <div>
                        <p class="text-sm text-gray-500">ToyyibPay Billcode</p>
                        <p class="font-medium text-gray-900">{{ $payment->toyyibpay_billcode }}</p>
                    </div>
                    @endif
                    @if($payment->toyyibpay_ref)
                    <div>
                        <p class="text-sm text-gray-500">{{ __('messages.transaction_id') }}</p>
                        <p class="font-medium text-gray-900">{{ $payment->toyyibpay_ref }}</p>
                    </div>
                    @endif
                    <div>
                        <p class="text-sm text-gray-500">{{ __('Dicipta') }}</p>
                        <p class="font-medium text-gray-900">{{ $payment->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    @if($payment->paid_at)
                    <div>
                        <p class="text-sm text-gray-500">{{ __('messages.paid_at') }}</p>
                        <p class="font-medium text-gray-900">{{ $payment->paid_at->format('d/m/Y H:i') }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Bills Paid -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
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
        <a href="{{ route('admin.payments.index') }}" class="block w-full py-3 bg-gray-100 text-gray-700 font-medium rounded-xl hover:bg-gray-200 transition text-center min-h-touch">
            {{ __('messages.back') }}
        </a>
    </div>
</x-app-layout>

