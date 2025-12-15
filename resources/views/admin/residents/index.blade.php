<x-app-layout>
    <x-slot name="title">{{ __('messages.residents') }}</x-slot>

    <div class="space-y-4">
        <!-- Header -->
        <div class="bg-white rounded-xl shadow-sm p-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <p class="text-gray-600">{{ __('messages.total') }}: {{ $residents->total() }} {{ __('messages.residents') }}</p>
            </div>
        </div>

        <!-- Search -->
        <div class="bg-white rounded-xl shadow-sm p-4">
            <form action="{{ route('admin.residents.index') }}" method="GET" class="flex flex-col sm:flex-row gap-4">
                <div class="flex-1">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('messages.search') }}..." class="w-full rounded-lg border-gray-300 text-sm focus:ring-primary-500 focus:border-primary-500">
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition min-h-touch">
                        {{ __('messages.search') }}
                    </button>
                    @if(request('search'))
                        <a href="{{ route('admin.residents.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition min-h-touch">
                            {{ __('messages.reset') }}
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Residents Table -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            @if($residents->isEmpty())
                <div class="p-8 text-center text-gray-500">
                    {{ __('messages.no_data') }}
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.name') }}</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.ic_number') }}</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.phone') }}</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.houses') }}</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.status') }}</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($residents as $resident)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3">
                                        <div>
                                            <p class="font-medium text-gray-900">{{ $resident->name }}</p>
                                            <p class="text-xs text-gray-500">{{ $resident->email }}</p>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-gray-600">
                                        {{ $resident->ic_number ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-gray-600">
                                        {{ $resident->phone ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3">
                                        @if($resident->houseMemberships->isNotEmpty())
                                            <div class="space-y-1">
                                                @foreach($resident->houseMemberships as $membership)
                                                    <div class="flex items-center gap-1">
                                                        <span class="text-gray-600">{{ $membership->house->full_address ?? '-' }}</span>
                                                        <span class="px-1.5 py-0.5 text-xs rounded 
                                                            @if($membership->status === 'active') bg-green-100 text-green-700
                                                            @elseif($membership->status === 'pending') bg-yellow-100 text-yellow-700
                                                            @else bg-gray-100 text-gray-600 @endif">
                                                            {{ ucfirst($membership->relationship) }}
                                                        </span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        @if($resident->user)
                                            <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-700">
                                                {{ __('messages.active') }}
                                            </span>
                                        @else
                                            <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-600">
                                                {{ __('messages.inactive') }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <a href="{{ route('admin.residents.show', $resident) }}" class="text-primary-600 hover:text-primary-700 font-medium">
                                            {{ __('messages.view') }}
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="p-4 border-t border-gray-100">
                    {{ $residents->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>

