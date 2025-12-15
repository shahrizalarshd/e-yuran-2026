<x-app-layout>
    <x-slot name="title">{{ __('messages.houses') }}</x-slot>

    <div class="space-y-4">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <p class="text-gray-500">{{ __('Jumlah') }}: {{ $houses->total() }} {{ __('rumah') }}</p>
            </div>
            @if(auth()->user()->isSuperAdmin())
            <a href="{{ route('admin.houses.create') }}" class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-primary-600 text-white font-medium rounded-lg hover:bg-primary-700 transition min-h-touch">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                {{ __('messages.add_house') }}
            </a>
            @endif
        </div>

        <!-- Filters -->
        <form method="GET" class="bg-white rounded-xl shadow-sm p-4">
            <div class="flex flex-wrap gap-3">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('messages.search') }}..." class="flex-1 min-w-[200px] rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                
                <select name="status" onchange="this.form.submit()" class="rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                    <option value="">{{ __('messages.all') }} {{ __('messages.status') }}</option>
                    <option value="occupied" {{ request('status') === 'occupied' ? 'selected' : '' }}>{{ __('messages.occupied') }}</option>
                    <option value="vacant" {{ request('status') === 'vacant' ? 'selected' : '' }}>{{ __('messages.vacant') }}</option>
                </select>
                
                <select name="is_registered" onchange="this.form.submit()" class="rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                    <option value="">{{ __('Semua Pendaftaran') }}</option>
                    <option value="true" {{ request('is_registered') === 'true' ? 'selected' : '' }}>{{ __('messages.is_registered') }}</option>
                    <option value="false" {{ request('is_registered') === 'false' ? 'selected' : '' }}>{{ __('Tidak Berdaftar') }}</option>
                </select>

                <button type="submit" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition min-h-touch">
                    {{ __('messages.filter') }}
                </button>
            </div>
        </form>

        <!-- Houses List -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <!-- Mobile Cards -->
            <div class="lg:hidden divide-y divide-gray-100">
                @forelse($houses as $house)
                    <a href="{{ route('admin.houses.show', $house) }}" class="block p-4 hover:bg-gray-50">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="font-medium text-gray-900">{{ $house->full_address }}</p>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $house->is_registered ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                        {{ $house->is_registered ? __('messages.is_registered') : __('Tidak Berdaftar') }}
                                    </span>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $house->status === 'occupied' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                                        {{ __('messages.' . $house->status) }}
                                    </span>
                                </div>
                            </div>
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </div>
                    </a>
                @empty
                    <div class="p-8 text-center text-gray-500">
                        {{ __('messages.no_data') }}
                    </div>
                @endforelse
            </div>

            <!-- Desktop Table -->
            <div class="hidden lg:block overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('messages.house_no') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('messages.street_name') }}</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">{{ __('messages.is_registered') }}</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">{{ __('messages.status') }}</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">{{ __('messages.bills') }}</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('messages.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($houses as $house)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $house->house_no }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $house->street_name }}</td>
                                <td class="px-4 py-3 text-center">
                                    @if($house->is_registered)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">{{ __('messages.yes') }}</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">{{ __('messages.no') }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $house->status === 'occupied' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                                        {{ __('messages.' . $house->status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center text-gray-600">{{ $house->bills_count }}</td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('admin.houses.show', $house) }}" class="text-primary-600 hover:text-primary-800 font-medium">{{ __('messages.view') }}</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-gray-500">{{ __('messages.no_data') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $houses->withQueryString()->links() }}
        </div>
    </div>
</x-app-layout>

