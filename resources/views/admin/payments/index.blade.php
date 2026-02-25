<x-app-layout>
    <x-slot name="title">{{ __('messages.payments') }}</x-slot>

    <div class="space-y-4">
        <!-- Stats -->
        <div class="grid grid-cols-3 gap-4">
            <div class="bg-white rounded-xl p-4 shadow-sm">
                <p class="text-sm text-gray-500">{{ __('Hari Ini') }}</p>
                <p class="text-xl font-bold text-green-600">RM {{ number_format($todayCollection, 2) }}</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm">
                <p class="text-sm text-gray-500">{{ __('Berjaya') }}</p>
                <p class="text-xl font-bold text-gray-900">RM {{ number_format($totalSuccess, 2) }}</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm">
                <p class="text-sm text-gray-500">{{ __('Tertangguh') }}</p>
                <p class="text-xl font-bold text-yellow-600">RM {{ number_format($totalPending, 2) }}</p>
            </div>
        </div>

        <!-- Filters -->
        <form method="GET" class="bg-white rounded-xl shadow-sm p-4">
            <div class="flex flex-wrap gap-3">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Cari no. pembayaran, rumah...') }}" class="flex-1 min-w-[200px] rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                
                <select name="status" class="rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                    <option value="">{{ __('messages.all') }} {{ __('messages.status') }}</option>
                    <option value="success" {{ request('status') === 'success' ? 'selected' : '' }}>{{ __('Berjaya') }}</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>{{ __('Tertangguh') }}</option>
                    <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>{{ __('Gagal') }}</option>
                </select>

                <select name="month" class="rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                    <option value="">-- {{ __('Bulan') }} --</option>
                    @foreach(['Januari','Februari','Mac','April','Mei','Jun','Julai','Ogos','September','Oktober','November','Disember'] as $i => $name)
                        <option value="{{ $i + 1 }}" {{ request('month') == ($i + 1) ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>

                <select name="year" class="rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                    <option value="">-- {{ __('Tahun') }} --</option>
                    @for($y = now()->year; $y >= 2017; $y--)
                        <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>

                <select name="street" class="rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                    <option value="">{{ __('Semua Jalan') }}</option>
                    @foreach($streets as $street)
                        <option value="{{ $street }}" {{ request('street') === $street ? 'selected' : '' }}>{{ $street }}</option>
                    @endforeach
                </select>

                <button type="submit" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition min-h-touch">
                    {{ __('messages.filter') }}
                </button>
                <a href="{{ route('admin.payments.index') }}" class="px-4 py-2 bg-red-50 text-red-600 rounded-lg hover:bg-red-100 transition min-h-touch inline-flex items-center">
                    Set Semula
                </a>
            </div>
        </form>

        <!-- Payments Table -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <x-sort-header column="payment_no" label="{{ __('messages.payment_no') }}" />
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('messages.house') }}</th>
                            <x-sort-header column="amount" label="{{ __('messages.amount') }}" align="right" />
                            <x-sort-header column="status" label="{{ __('messages.status') }}" align="center" />
                            <x-sort-header column="created_at" label="{{ __('messages.date') }}" align="right" />
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('messages.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($payments as $payment)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $payment->payment_no }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $payment->house->full_address }}</td>
                                <td class="px-4 py-3 text-right font-medium text-gray-900">RM {{ number_format($payment->amount, 2) }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $payment->status_badge_class }}">
                                        {{ __('messages.' . $payment->status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right text-gray-500">{{ $payment->created_at->format('d/m/Y H:i') }}</td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('admin.payments.show', $payment) }}" class="text-primary-600 hover:text-primary-800 font-medium">{{ __('messages.view') }}</a>
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
            {{ $payments->withQueryString()->links() }}
        </div>
    </div>
</x-app-layout>

