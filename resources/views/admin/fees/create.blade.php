<x-app-layout>
    <x-slot name="title">{{ __('Tambah Konfigurasi Yuran') }}</x-slot>

    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="p-4 lg:p-6 border-b border-gray-100">
                <h2 class="text-lg font-semibold text-gray-900">{{ __('Tambah Konfigurasi Yuran') }}</h2>
                <p class="text-sm text-gray-500 mt-1">{{ __('Tetapkan kadar yuran bulanan baru') }}</p>
            </div>

            <form action="{{ route('admin.fees.store') }}" method="POST" class="p-4 lg:p-6 space-y-6">
                @csrf

                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.name') }} <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required class="w-full rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500" placeholder="Yuran Bulanan 2025">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="amount" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Amaun (RM)') }} <span class="text-red-500">*</span></label>
                    <input type="number" name="amount" id="amount" value="{{ old('amount') }}" required step="0.01" min="0" class="w-full rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500" placeholder="50.00">
                    @error('amount')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid gap-6 lg:grid-cols-2">
                    <div>
                        <label for="effective_from" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Berkuatkuasa Dari') }} <span class="text-red-500">*</span></label>
                        <input type="date" name="effective_from" id="effective_from" value="{{ old('effective_from', now()->format('Y-m-d')) }}" required class="w-full rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500">
                        @error('effective_from')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="effective_until" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Berkuatkuasa Sehingga') }}</label>
                        <input type="date" name="effective_until" id="effective_until" value="{{ old('effective_until') }}" class="w-full rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500">
                        <p class="text-xs text-gray-500 mt-1">{{ __('Kosongkan untuk yuran tanpa had tarikh') }}</p>
                        @error('effective_until')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Keterangan') }}</label>
                    <textarea name="description" id="description" rows="3" class="w-full rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500" placeholder="{{ __('Keterangan tambahan...') }}">{{ old('description') }}</textarea>
                </div>

                <label class="flex items-center gap-3 p-4 bg-gray-50 rounded-lg cursor-pointer hover:bg-gray-100 min-h-touch">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="w-5 h-5 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                    <div>
                        <p class="font-medium text-gray-900">{{ __('messages.is_active') }}</p>
                        <p class="text-sm text-gray-500">{{ __('Yuran aktif akan digunakan untuk jana bil') }}</p>
                    </div>
                </label>

                <div class="flex gap-3 pt-4 border-t border-gray-200">
                    <a href="{{ route('admin.fees.index') }}" class="flex-1 py-3 bg-gray-100 text-gray-700 font-medium rounded-lg hover:bg-gray-200 transition text-center min-h-touch">
                        {{ __('messages.cancel') }}
                    </a>
                    <button type="submit" class="flex-1 py-3 bg-primary-600 text-white font-medium rounded-lg hover:bg-primary-700 transition min-h-touch">
                        {{ __('messages.save') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>

