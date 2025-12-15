<x-app-layout>
    <x-slot name="title">{{ __('messages.fee_configuration') }}</x-slot>

    <div class="space-y-4">
        <!-- Current Fee -->
        @if($currentFee)
        <div class="bg-primary-600 text-white rounded-xl p-6">
            <p class="text-primary-100 text-sm">{{ __('Yuran Semasa') }}</p>
            <p class="text-3xl font-bold mt-1">RM {{ number_format($currentFee->amount, 2) }}</p>
            <p class="text-primary-100 text-sm mt-2">{{ $currentFee->name }} • {{ __('Berkuatkuasa dari') }} {{ $currentFee->effective_from->format('d/m/Y') }}</p>
        </div>
        @endif

        <!-- Header -->
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-900">{{ __('Senarai Konfigurasi') }}</h2>
            <a href="{{ route('admin.fees.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 text-white font-medium rounded-lg hover:bg-primary-700 transition min-h-touch">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                {{ __('Tambah Yuran') }}
            </a>
        </div>

        <!-- Fees List -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            @if($fees->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('messages.name') }}</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('messages.amount') }}</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">{{ __('Tempoh') }}</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">{{ __('messages.status') }}</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('messages.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($fees as $fee)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <p class="font-medium text-gray-900">{{ $fee->name }}</p>
                                    @if($fee->description)
                                        <p class="text-sm text-gray-500">{{ Str::limit($fee->description, 50) }}</p>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right font-semibold text-gray-900">RM {{ number_format($fee->amount, 2) }}</td>
                                <td class="px-4 py-3 text-center text-sm text-gray-600">
                                    {{ $fee->effective_from->format('d/m/Y') }}
                                    @if($fee->effective_until)
                                        - {{ $fee->effective_until->format('d/m/Y') }}
                                    @else
                                        - {{ __('Seterusnya') }}
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $fee->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                        {{ $fee->is_active ? __('messages.active') : __('messages.inactive') }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('admin.fees.edit', $fee) }}" class="text-primary-600 hover:text-primary-800 font-medium">{{ __('messages.edit') }}</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="p-8 text-center text-gray-500">
                {{ __('messages.no_data') }}
            </div>
            @endif
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $fees->links() }}
        </div>
    </div>
</x-app-layout>

