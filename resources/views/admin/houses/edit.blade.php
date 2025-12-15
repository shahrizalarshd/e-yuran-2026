<x-app-layout>
    <x-slot name="title">{{ __('messages.edit_house') }}</x-slot>

    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="p-4 lg:p-6 border-b border-gray-100">
                <h2 class="text-lg font-semibold text-gray-900">{{ __('messages.edit_house') }}</h2>
                <p class="text-sm text-gray-500 mt-1">{{ $house->full_address }}</p>
            </div>

            <form action="{{ route('admin.houses.update', $house) }}" method="POST" class="p-4 lg:p-6 space-y-6">
                @csrf
                @method('PATCH')

                <div class="grid gap-6 lg:grid-cols-2">
                    <div>
                        <label for="house_no" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.house_no') }} <span class="text-red-500">*</span></label>
                        <input type="text" name="house_no" id="house_no" value="{{ old('house_no', $house->house_no) }}" required class="w-full rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500">
                        @error('house_no')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="street_name" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.street_name') }} <span class="text-red-500">*</span></label>
                        <input type="text" name="street_name" id="street_name" value="{{ old('street_name', $house->street_name) }}" required class="w-full rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500">
                        @error('street_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.status') }} <span class="text-red-500">*</span></label>
                    <select name="status" id="status" required class="w-full rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500">
                        <option value="occupied" {{ old('status', $house->status) === 'occupied' ? 'selected' : '' }}>{{ __('messages.occupied') }}</option>
                        <option value="vacant" {{ old('status', $house->status) === 'vacant' ? 'selected' : '' }}>{{ __('messages.vacant') }}</option>
                    </select>
                </div>

                <div class="space-y-4">
                    <label class="flex items-center gap-3 p-4 bg-gray-50 rounded-lg cursor-pointer hover:bg-gray-100 min-h-touch">
                        <input type="checkbox" name="is_registered" value="1" {{ old('is_registered', $house->is_registered) ? 'checked' : '' }} class="w-5 h-5 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                        <div>
                            <p class="font-medium text-gray-900">{{ __('messages.is_registered') }}</p>
                            <p class="text-sm text-gray-500">{{ __('Rumah yang berdaftar akan dikenakan bil yuran') }}</p>
                        </div>
                    </label>

                    <label class="flex items-center gap-3 p-4 bg-gray-50 rounded-lg cursor-pointer hover:bg-gray-100 min-h-touch">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $house->is_active) ? 'checked' : '' }} class="w-5 h-5 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                        <div>
                            <p class="font-medium text-gray-900">{{ __('messages.is_active') }}</p>
                            <p class="text-sm text-gray-500">{{ __('Rumah yang aktif sahaja akan dijana bil') }}</p>
                        </div>
                    </label>
                </div>

                <div class="flex gap-3 pt-4 border-t border-gray-200">
                    <a href="{{ route('admin.houses.show', $house) }}" class="flex-1 py-3 bg-gray-100 text-gray-700 font-medium rounded-lg hover:bg-gray-200 transition text-center min-h-touch">
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

