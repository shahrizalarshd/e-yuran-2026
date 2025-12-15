<x-app-layout>
    <x-slot name="title">{{ __('messages.edit_membership_fee') }}</x-slot>

    <div class="py-2 lg:py-6">
        {{-- Page Header --}}
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 mb-6">
            <div class="flex items-center gap-4">
                <a href="{{ route('admin.membership-fees.index') }}" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </a>
                <h2 class="text-2xl font-bold text-gray-800">
                    {{ __('messages.edit_membership_fee') }}
                </h2>
            </div>
        </div>
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                {{-- House Info --}}
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">{{ $membershipFee->house->full_address }}</h3>
                    <p class="text-sm text-gray-500">
                        {{ __('messages.membership_fee_year') }}: {{ $membershipFee->fee_year }}
                        @if ($membershipFee->is_legacy)
                            <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-200 text-gray-700">
                                Legacy
                            </span>
                        @endif
                    </p>
                </div>

                <form action="{{ route('admin.membership-fees.update', $membershipFee) }}" method="POST" class="p-6 space-y-6">
                    @csrf
                    @method('PUT')

                    {{-- Owner Name --}}
                    <div>
                        <label for="legacy_owner_name" class="block text-sm font-medium text-gray-700 mb-1">
                            {{ __('messages.owner_name') }}
                        </label>
                        <input type="text" name="legacy_owner_name" id="legacy_owner_name"
                            value="{{ old('legacy_owner_name', $membershipFee->legacy_owner_name) }}"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-green-500 focus:border-green-500">
                        @error('legacy_owner_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Amount --}}
                    <div>
                        <label for="amount" class="block text-sm font-medium text-gray-700 mb-1">
                            {{ __('messages.amount') }} (RM)
                        </label>
                        <input type="number" name="amount" id="amount" step="0.01" min="0"
                            value="{{ old('amount', $membershipFee->amount) }}"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-green-500 focus:border-green-500">
                        @error('amount')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Status --}}
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">
                            {{ __('messages.status') }}
                        </label>
                        <select name="status" id="status"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-green-500 focus:border-green-500">
                            <option value="unpaid" {{ old('status', $membershipFee->status) === 'unpaid' ? 'selected' : '' }}>
                                {{ __('messages.unpaid') }}
                            </option>
                            <option value="paid" {{ old('status', $membershipFee->status) === 'paid' ? 'selected' : '' }}>
                                {{ __('messages.paid') }}
                            </option>
                        </select>
                        @error('status')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Paid At --}}
                    <div>
                        <label for="paid_at" class="block text-sm font-medium text-gray-700 mb-1">
                            {{ __('messages.paid_at') }}
                        </label>
                        <input type="date" name="paid_at" id="paid_at"
                            value="{{ old('paid_at', $membershipFee->paid_at?->format('Y-m-d')) }}"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-green-500 focus:border-green-500">
                        @error('paid_at')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Payment Reference --}}
                    <div>
                        <label for="payment_reference" class="block text-sm font-medium text-gray-700 mb-1">
                            {{ __('messages.payment_reference') }}
                        </label>
                        <input type="text" name="payment_reference" id="payment_reference"
                            value="{{ old('payment_reference', $membershipFee->payment_reference) }}"
                            placeholder="{{ __('messages.payment_reference_placeholder') }}"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-green-500 focus:border-green-500">
                        @error('payment_reference')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Submit --}}
                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
                        <a href="{{ route('admin.membership-fees.index') }}"
                            class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                            {{ __('messages.cancel') }}
                        </a>
                        <button type="submit"
                            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                            {{ __('messages.save') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

