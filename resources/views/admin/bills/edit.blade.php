<x-app-layout>
    <x-slot name="title">{{ __('messages.edit') }} {{ __('messages.bill') }} #{{ $bill->bill_no }}</x-slot>

    <div class="max-w-2xl mx-auto space-y-6">
        <!-- Bill Info Card -->
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
                </div>
            </div>
        </div>

        <!-- Edit Form -->
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <div class="p-6 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900">{{ __('messages.edit') }} {{ __('messages.bill') }}</h3>
            </div>

            <form action="{{ route('admin.bills.update', $bill) }}" method="POST" class="p-6 space-y-6">
                @csrf
                @method('PUT')

                <!-- Amount -->
                <div>
                    <label for="amount" class="block text-sm font-medium text-gray-700 mb-2">
                        {{ __('messages.amount') }} (RM)
                    </label>
                    <input type="number" 
                           name="amount" 
                           id="amount" 
                           step="0.01" 
                           min="0"
                           value="{{ old('amount', $bill->amount) }}"
                           class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 transition @error('amount') border-red-500 @enderror"
                           required>
                    @error('amount')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Due Date -->
                <div>
                    <label for="due_date" class="block text-sm font-medium text-gray-700 mb-2">
                        {{ __('messages.due_date') }}
                    </label>
                    <input type="date" 
                           name="due_date" 
                           id="due_date" 
                           value="{{ old('due_date', $bill->due_date->format('Y-m-d')) }}"
                           class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 transition @error('due_date') border-red-500 @enderror"
                           required>
                    @error('due_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Current Info -->
                <div class="bg-gray-50 rounded-xl p-4">
                    <p class="text-sm text-gray-600">
                        <strong>{{ __('messages.current') }} {{ __('messages.amount') }}:</strong> 
                        RM {{ number_format($bill->amount, 2) }}
                    </p>
                    <p class="text-sm text-gray-600 mt-1">
                        <strong>{{ __('messages.current') }} {{ __('messages.due_date') }}:</strong> 
                        {{ $bill->due_date->format('d/m/Y') }}
                    </p>
                    @if($bill->paid_amount > 0)
                    <p class="text-sm text-green-600 mt-1">
                        <strong>{{ __('messages.paid') }}:</strong> 
                        RM {{ number_format($bill->paid_amount, 2) }}
                    </p>
                    @endif
                </div>

                <!-- Actions -->
                <div class="flex gap-3 pt-4">
                    <a href="{{ route('admin.bills.show', $bill) }}" 
                       class="flex-1 py-3 bg-gray-100 text-gray-700 font-medium rounded-xl hover:bg-gray-200 transition text-center min-h-touch">
                        {{ __('messages.cancel') }}
                    </a>
                    <button type="submit" 
                            class="flex-1 py-3 bg-primary-600 text-white font-medium rounded-xl hover:bg-primary-700 transition text-center min-h-touch">
                        {{ __('messages.save') }}
                    </button>
                </div>
            </form>
        </div>

        @if(auth()->user()->isSuperAdmin())
        <!-- Danger Zone for Super Admin -->
        <div class="bg-red-50 border border-red-200 rounded-2xl overflow-hidden">
            <div class="p-6 border-b border-red-200">
                <h3 class="font-semibold text-red-800">{{ __('messages.danger_zone') }}</h3>
            </div>
            <div class="p-6">
                <p class="text-sm text-red-700 mb-4">{{ __('messages.delete_bill_warning') }}</p>
                <form action="{{ route('admin.bills.destroy', $bill) }}" method="POST" 
                      onsubmit="return confirm('{{ __('messages.confirm_delete') }}')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                            class="px-6 py-3 bg-red-600 text-white font-medium rounded-xl hover:bg-red-700 transition">
                        {{ __('messages.delete') }} {{ __('messages.bill') }}
                    </button>
                </form>
            </div>
        </div>
        @endif
    </div>
</x-app-layout>

