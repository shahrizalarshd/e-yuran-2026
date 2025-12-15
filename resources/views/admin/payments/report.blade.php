<x-app-layout>
    <x-slot name="title">{{ __('messages.collection_report') }}</x-slot>

    <div class="space-y-4">
        <!-- Filters -->
        <div class="bg-white rounded-xl shadow-sm p-4">
            <form action="{{ route('admin.payments.report') }}" method="GET" class="flex flex-col sm:flex-row gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.bill_year') }}</label>
                    <select name="year" class="rounded-lg border-gray-300 text-sm focus:ring-primary-500 focus:border-primary-500">
                        @foreach($years as $y)
                            <option value="{{ $y }}" @selected($year == $y)>{{ $y }}</option>
                        @endforeach
                        @if($years->isEmpty())
                            <option value="{{ now()->year }}" selected>{{ now()->year }}</option>
                        @endif
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.bill_month') }}</label>
                    <select name="month" class="rounded-lg border-gray-300 text-sm focus:ring-primary-500 focus:border-primary-500">
                        <option value="">{{ __('messages.all') }}</option>
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" @selected($month == $m)>
                                {{ \Carbon\Carbon::create(null, $m, 1)->translatedFormat('F') }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition min-h-touch">
                        {{ __('messages.filter') }}
                    </button>
                </div>
            </form>
        </div>

        <!-- Summary -->
        <div class="bg-white rounded-xl shadow-sm p-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">{{ __('messages.collection_report') }} - {{ $year }}</h2>
                    <p class="text-sm text-gray-500">{{ __('messages.total') }}: {{ $payments->total() }} {{ __('messages.payments') }}</p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-500">{{ __('messages.total_collection') }}</p>
                    <p class="text-2xl font-bold text-green-600">RM {{ number_format($totalAmount, 2) }}</p>
                </div>
            </div>
        </div>

        <!-- Monthly Chart -->
        <div class="bg-white rounded-xl shadow-sm p-4">
            <h3 class="font-semibold text-gray-900 mb-4">{{ __('messages.bill_period') }}</h3>
            <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-12 gap-2">
                @for($m = 1; $m <= 12; $m++)
                    @php
                        $data = $monthlyData->get($m);
                        $monthTotal = $data->total ?? 0;
                        $monthCount = $data->count ?? 0;
                        $maxTotal = $monthlyData->max('total') ?: 1;
                        $heightPercent = $maxTotal > 0 ? ($monthTotal / $maxTotal) * 100 : 0;
                    @endphp
                    <div class="text-center">
                        <div class="h-24 flex items-end justify-center mb-2">
                            <div class="w-full max-w-[30px] bg-primary-500 rounded-t" style="height: {{ max($heightPercent, 5) }}%"></div>
                        </div>
                        <p class="text-xs font-medium text-gray-600">{{ \Carbon\Carbon::create(null, $m, 1)->translatedFormat('M') }}</p>
                        <p class="text-xs text-gray-500">{{ $monthCount }}</p>
                    </div>
                @endfor
            </div>
        </div>

        <!-- Monthly Breakdown Table -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="p-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900">{{ __('messages.bill_period') }}</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.bill_month') }}</th>
                            <th class="px-4 py-3 text-right font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.payments') }}</th>
                            <th class="px-4 py-3 text-right font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.amount') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @php $yearTotal = 0; $yearCount = 0; @endphp
                        @for($m = 1; $m <= 12; $m++)
                            @php
                                $data = $monthlyData->get($m);
                                $monthTotal = $data->total ?? 0;
                                $monthCount = $data->count ?? 0;
                                $yearTotal += $monthTotal;
                                $yearCount += $monthCount;
                            @endphp
                            <tr class="hover:bg-gray-50 {{ $monthTotal > 0 ? '' : 'text-gray-400' }}">
                                <td class="px-4 py-3">
                                    {{ \Carbon\Carbon::create($year, $m, 1)->translatedFormat('F Y') }}
                                </td>
                                <td class="px-4 py-3 text-right">{{ $monthCount }}</td>
                                <td class="px-4 py-3 text-right font-medium {{ $monthTotal > 0 ? 'text-green-600' : '' }}">
                                    RM {{ number_format($monthTotal, 2) }}
                                </td>
                            </tr>
                        @endfor
                    </tbody>
                    <tfoot class="bg-gray-50">
                        <tr>
                            <td class="px-4 py-3 font-semibold text-gray-900">{{ __('messages.total') }}</td>
                            <td class="px-4 py-3 text-right font-semibold text-gray-900">{{ $yearCount }}</td>
                            <td class="px-4 py-3 text-right font-bold text-green-600">RM {{ number_format($yearTotal, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Recent Payments -->
        @if($payments->isNotEmpty())
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="p-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-900">{{ __('messages.recent_payments') }}</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.date') }}</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.payment_no') }}</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.house') }}</th>
                                <th class="px-4 py-3 text-right font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.amount') }}</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($payments as $payment)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-gray-600">
                                        {{ $payment->paid_at?->format('d/m/Y H:i') ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3 font-medium text-gray-900">
                                        {{ $payment->payment_no }}
                                    </td>
                                    <td class="px-4 py-3 text-gray-600">
                                        {{ $payment->house->full_address ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-right font-medium text-green-600">
                                        RM {{ number_format($payment->amount, 2) }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <a href="{{ route('admin.payments.show', $payment) }}" class="text-primary-600 hover:text-primary-700 font-medium">
                                            {{ __('messages.view') }}
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="p-4 border-t border-gray-100">
                    {{ $payments->withQueryString()->links() }}
                </div>
            </div>
        @endif
    </div>
</x-app-layout>

