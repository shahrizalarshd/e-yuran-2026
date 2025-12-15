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
                
                <select name="status" onchange="this.form.submit()" class="rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                    <option value="">{{ __('messages.all') }} {{ __('messages.status') }}</option>
                    <option value="success" {{ request('status') === 'success' ? 'selected' : '' }}>{{ __('Berjaya') }}</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>{{ __('Tertangguh') }}</option>
                    <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>{{ __('Gagal') }}</option>
                </select>

                <input type="date" name="from_date" value="{{ request('from_date') }}" class="rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                <input type="date" name="to_date" value="{{ request('to_date') }}" class="rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">

                <button type="submit" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition min-h-touch">
                    {{ __('messages.filter') }}
                </button>
            </div>
        </form>

        <!-- Payments Table -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('messages.payment_no') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('messages.house') }}</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('messages.amount') }}</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">{{ __('messages.status') }}</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('messages.date') }}</th>
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

