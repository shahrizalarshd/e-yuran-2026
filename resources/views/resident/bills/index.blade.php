<x-app-layout>
    <x-slot name="title">{{ __('messages.bills') }}</x-slot>

    <div class="space-y-4">
        <!-- House Info -->
        <div class="bg-white rounded-xl shadow-sm p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-primary-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                </div>
                <div>
                    <p class="font-medium text-gray-900">{{ $house->full_address }}</p>
                    <p class="text-sm text-gray-500">{{ __('messages.your_bills') }}</p>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <form method="GET" class="bg-white rounded-xl shadow-sm p-4">
            <div class="flex flex-wrap gap-3">
                <select name="status" onchange="this.form.submit()" class="flex-1 min-w-[120px] rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                    <option value="">{{ __('messages.all') }} {{ __('messages.status') }}</option>
                    <option value="unpaid" {{ request('status') === 'unpaid' ? 'selected' : '' }}>{{ __('messages.unpaid') }}</option>
                    <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>{{ __('messages.paid') }}</option>
                    <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>{{ __('messages.processing') }}</option>
                </select>
                
                <select name="year" onchange="this.form.submit()" class="flex-1 min-w-[100px] rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                    <option value="">{{ __('messages.all') }} {{ __('Tahun') }}</option>
                    @foreach($years as $year)
                        <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>{{ $year }}</option>
                    @endforeach
                </select>
            </div>
        </form>

        <!-- Bills List -->
        @if($bills->count() > 0)
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="divide-y divide-gray-100">
                    @foreach($bills as $bill)
                        <div class="p-4 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg flex items-center justify-center {{ $bill->status === 'paid' ? 'bg-green-100' : ($bill->status === 'processing' ? 'bg-yellow-100' : 'bg-red-100') }}">
                                    <span class="font-semibold text-sm {{ $bill->status === 'paid' ? 'text-green-600' : ($bill->status === 'processing' ? 'text-yellow-600' : 'text-red-600') }}">{{ $bill->bill_month }}</span>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">{{ $bill->bill_period }}</p>
                                    <p class="text-sm text-gray-500">
                                        @if($bill->status === 'paid')
                                            {{ __('messages.paid_at') }}: {{ $bill->paid_at->format('d/m/Y') }}
                                        @else
                                            {{ __('messages.due_date') }}: {{ $bill->due_date->format('d/m/Y') }}
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold text-gray-900">RM {{ number_format($bill->amount, 2) }}</p>
                                <span class="text-xs px-2 py-0.5 rounded-full {{ $bill->status_badge_class }}">
                                    {{ __('messages.' . $bill->status) }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                {{ $bills->withQueryString()->links() }}
            </div>
        @else
            <div class="bg-white rounded-xl shadow-sm p-8 text-center">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <h3 class="font-semibold text-gray-900 mb-1">{{ __('Tiada bil') }}</h3>
                <p class="text-gray-500 text-sm">{{ __('Tiada bil dijumpai') }}</p>
            </div>
        @endif
    </div>
</x-app-layout>

