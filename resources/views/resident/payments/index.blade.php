<x-app-layout>
    <x-slot name="title">{{ __('messages.payment_history') }}</x-slot>

    <div class="space-y-4">
        <!-- House Info -->
        <div class="bg-white rounded-xl shadow-sm p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-primary-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                </div>
                <div>
                    <p class="font-medium text-gray-900">{{ $house->full_address }}</p>
                    <p class="text-sm text-gray-500">{{ __('messages.payment_history') }}</p>
                </div>
            </div>
        </div>

        <!-- Payments List -->
        @if($payments->count() > 0)
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="divide-y divide-gray-100">
                    @foreach($payments as $payment)
                        <a href="{{ route('resident.payments.show', $payment) }}" class="p-4 flex items-center justify-between hover:bg-gray-50">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg flex items-center justify-center {{ $payment->status === 'success' ? 'bg-green-100' : ($payment->status === 'pending' ? 'bg-yellow-100' : 'bg-red-100') }}">
                                    @if($payment->status === 'success')
                                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    @elseif($payment->status === 'pending')
                                        <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    @else
                                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    @endif
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">{{ $payment->payment_no }}</p>
                                    <p class="text-sm text-gray-500">{{ $payment->created_at->format('d/m/Y H:i') }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold {{ $payment->status === 'success' ? 'text-green-600' : ($payment->status === 'pending' ? 'text-yellow-600' : 'text-red-600') }}">
                                    RM {{ number_format($payment->amount, 2) }}
                                </p>
                                <span class="text-xs px-2 py-0.5 rounded-full {{ $payment->status_badge_class }}">
                                    {{ __('messages.' . $payment->status) }}
                                </span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                {{ $payments->links() }}
            </div>
        @else
            <div class="bg-white rounded-xl shadow-sm p-8 text-center">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                </div>
                <h3 class="font-semibold text-gray-900 mb-1">{{ __('Tiada rekod pembayaran') }}</h3>
                <p class="text-gray-500 text-sm">{{ __('Anda belum membuat sebarang pembayaran') }}</p>
            </div>
        @endif
    </div>
</x-app-layout>

