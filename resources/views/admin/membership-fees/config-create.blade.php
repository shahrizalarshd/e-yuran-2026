<x-app-layout>
    <x-slot name="title">{{ __('messages.add_membership_fee_config') }}</x-slot>

    <div class="py-2 lg:py-6">
        {{-- Page Header --}}
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 mb-6">
            <div class="flex items-center gap-4">
                <a href="{{ route('admin.membership-fees.config.index') }}" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </a>
                <h2 class="text-2xl font-bold text-gray-800">
                    {{ __('messages.add_membership_fee_config') }}
                </h2>
            </div>
        </div>
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <form action="{{ route('admin.membership-fees.config.store') }}" method="POST" class="p-6 space-y-6">
                    @csrf

                    {{-- Name --}}
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                            {{ __('messages.config_name') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" id="name" required
                            value="{{ old('name') }}"
                            placeholder="{{ __('messages.config_name_placeholder') }}"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-green-500 focus:border-green-500">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Amount --}}
                    <div>
                        <label for="amount" class="block text-sm font-medium text-gray-700 mb-1">
                            {{ __('messages.amount') }} (RM) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="amount" id="amount" step="0.01" min="0" required
                            value="{{ old('amount', '20.00') }}"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-green-500 focus:border-green-500">
                        @error('amount')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Effective From --}}
                    <div>
                        <label for="effective_from" class="block text-sm font-medium text-gray-700 mb-1">
                            {{ __('messages.effective_from') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="effective_from" id="effective_from" required
                            value="{{ old('effective_from', date('Y-m-d')) }}"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-green-500 focus:border-green-500">
                        @error('effective_from')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Effective Until --}}
                    <div>
                        <label for="effective_until" class="block text-sm font-medium text-gray-700 mb-1">
                            {{ __('messages.effective_until') }}
                        </label>
                        <input type="date" name="effective_until" id="effective_until"
                            value="{{ old('effective_until') }}"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-green-500 focus:border-green-500">
                        <p class="mt-1 text-sm text-gray-500">{{ __('messages.leave_empty_no_end') }}</p>
                        @error('effective_until')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Description --}}
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                            {{ __('messages.description') }}
                        </label>
                        <textarea name="description" id="description" rows="3"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-green-500 focus:border-green-500">{{ old('description') }}</textarea>
                        @error('description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Is Active --}}
                    <div class="flex items-center">
                        <input type="checkbox" name="is_active" id="is_active" value="1"
                            {{ old('is_active', true) ? 'checked' : '' }}
                            class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                        <label for="is_active" class="ml-2 block text-sm text-gray-700">
                            {{ __('messages.is_active') }}
                        </label>
                    </div>

                    {{-- Submit --}}
                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
                        <a href="{{ route('admin.membership-fees.config.index') }}"
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

