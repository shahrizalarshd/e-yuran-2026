<x-app-layout>
    <x-slot name="title">{{ __('messages.bill') }} #{{ $bill->bill_no }}</x-slot>

    <div class="max-w-2xl mx-auto space-y-6">
        <!-- Bill Card -->
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <div class="p-6 border-b border-gray-100">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm text-gray-500">{{ __('messages.bill_no') }}</p>
                        <p class="text-lg font-bold text-gray-900">{{ $bill->bill_no }}</p>
                    </div>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $bill->status_badge_class }}">
                        {{ __('messages.' . $bill->status) }}
                    </span>
                </div>
            </div>

            <div class="p-6 text-center border-b border-gray-100">
                <p class="text-sm text-gray-500">{{ __('messages.amount') }}</p>
                <p class="text-3xl font-bold text-gray-900">RM {{ number_format($bill->amount, 2) }}</p>
                @if($bill->paid_amount > 0 && $bill->paid_amount < $bill->amount)
                    <p class="text-sm text-green-600 mt-1">{{ __('Dibayar') }}: RM {{ number_format($bill->paid_amount, 2) }}</p>
                @endif
            </div>

            <div class="p-6 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-500">{{ __('messages.house') }}</p>
                        <p class="font-medium text-gray-900">{{ $bill->house->full_address }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">{{ __('messages.bill_period') }}</p>
                        <p class="font-medium text-gray-900">{{ $bill->bill_period }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">{{ __('messages.due_date') }}</p>
                        <p class="font-medium {{ $bill->is_overdue ? 'text-red-600' : 'text-gray-900' }}">{{ $bill->due_date->format('d/m/Y') }}</p>
                    </div>
                    @if($bill->paid_at)
                    <div>
                        <p class="text-sm text-gray-500">{{ __('messages.paid_at') }}</p>
                        <p class="font-medium text-gray-900">{{ $bill->paid_at->format('d/m/Y') }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Payment History -->
        @if($bill->payments->count() > 0)
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="p-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900">{{ __('messages.payment_history') }}</h3>
            </div>
            <div class="divide-y divide-gray-100">
                @foreach($bill->payments as $payment)
                    <a href="{{ route('admin.payments.show', $payment) }}" class="p-4 flex items-center justify-between hover:bg-gray-50">
                        <div>
                            <p class="font-medium text-gray-900">{{ $payment->payment_no }}</p>
                            <p class="text-sm text-gray-500">{{ $payment->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                        <div class="text-right">
                            <p class="font-medium text-gray-900">RM {{ number_format($payment->pivot->amount, 2) }}</p>
                            <span class="text-xs px-2 py-0.5 rounded-full {{ $payment->status_badge_class }}">{{ __('messages.' . $payment->status) }}</span>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Actions -->
        <div class="flex gap-3">
            <a href="{{ route('admin.bills.index') }}" class="flex-1 py-3 bg-gray-100 text-gray-700 font-medium rounded-xl hover:bg-gray-200 transition text-center min-h-touch">
                {{ __('messages.back') }}
            </a>
            @if($bill->status !== 'paid' && (auth()->user()->isSuperAdmin() || auth()->user()->isTreasurer()))
            <a href="{{ route('admin.bills.edit', $bill) }}" class="flex-1 py-3 bg-primary-600 text-white font-medium rounded-xl hover:bg-primary-700 transition text-center min-h-touch">
                {{ __('messages.edit') }}
            </a>
            @endif
        </div>
    </div>
</x-app-layout>

