<x-app-layout>
    <x-slot name="title">{{ __('messages.outstanding_report') }}</x-slot>

    <div class="space-y-4">
        <!-- Summary -->
        <div class="bg-white rounded-xl shadow-sm p-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">{{ __('messages.outstanding_report') }}</h2>
                    <p class="text-sm text-gray-500">{{ __('messages.houses') }}: {{ $houses->count() }}</p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-500">{{ __('messages.total_outstanding') }}</p>
                    <p class="text-2xl font-bold text-red-600">RM {{ number_format($houses->sum('total_outstanding'), 2) }}</p>
                </div>
            </div>
        </div>

        <!-- Outstanding List -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            @if($houses->isEmpty())
                <div class="p-8 text-center">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-1">{{ __('messages.no_data') }}</h3>
                    <p class="text-gray-500">Semua rumah telah menjelaskan bayaran</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.house') }}</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.bills') }}</th>
                                <th class="px-4 py-3 text-right font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.outstanding') }}</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($houses as $house)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3">
                                        <a href="{{ route('admin.houses.show', $house) }}" class="font-medium text-primary-600 hover:text-primary-700">
                                            {{ $house->full_address }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="space-y-1">
                                            @foreach($house->bills->take(3) as $bill)
                                                <div class="flex items-center gap-2">
                                                    <span class="text-gray-600">{{ $bill->bill_period }}</span>
                                                    @if($bill->is_overdue)
                                                        <span class="px-1.5 py-0.5 text-xs rounded bg-red-100 text-red-700">{{ __('messages.overdue') }}</span>
                                                    @endif
                                                </div>
                                            @endforeach
                                            @if($house->bills->count() > 3)
                                                <span class="text-xs text-gray-400">+{{ $house->bills->count() - 3 }} {{ __('messages.bills') }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <span class="font-semibold text-red-600">RM {{ number_format($house->total_outstanding, 2) }}</span>
                                        <p class="text-xs text-gray-500">{{ $house->bills->count() }} {{ __('messages.bills') }}</p>
                                    </td>
                                    <td class="px-4 py-3">
                                        <a href="{{ route('admin.houses.show', $house) }}" class="text-primary-600 hover:text-primary-700 font-medium text-sm">
                                            {{ __('messages.view') }}
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <!-- Breakdown by Month -->
        @if($houses->isNotEmpty())
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="p-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-900">{{ __('messages.bill_period') }}</h3>
                </div>
                <div class="p-4">
                    @php
                        $billsByPeriod = $houses->flatMap->bills->groupBy(fn($b) => $b->bill_year . '-' . str_pad($b->bill_month, 2, '0', STR_PAD_LEFT));
                    @endphp
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
                        @foreach($billsByPeriod->sortKeys() as $period => $bills)
                            @php
                                [$year, $month] = explode('-', $period);
                                $monthName = \Carbon\Carbon::create($year, $month, 1)->translatedFormat('M Y');
                            @endphp
                            <div class="bg-gray-50 rounded-lg p-3 text-center">
                                <p class="text-sm font-medium text-gray-900">{{ $monthName }}</p>
                                <p class="text-lg font-bold text-red-600">{{ $bills->count() }}</p>
                                <p class="text-xs text-gray-500">{{ __('messages.bills') }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>

