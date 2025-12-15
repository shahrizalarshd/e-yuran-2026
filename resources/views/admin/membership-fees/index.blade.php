<x-app-layout>
    <x-slot name="title">{{ __('messages.membership_fees') }}</x-slot>

    <div class="py-2 lg:py-6">
        {{-- Page Header with Config Button --}}
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <h2 class="text-2xl font-bold text-gray-800">
                    {{ __('messages.membership_fees') }}
                </h2>
                <a href="{{ route('admin.membership-fees.config.index') }}"
                    class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 transition">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    {{ __('messages.configuration') }}
                </a>
            </div>
        </div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- Success Message --}}
            @if (session('success'))
                <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Stats Cards --}}
            <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
                <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
                    <p class="text-sm text-gray-500">{{ __('messages.total') }}</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $stats['total'] }}</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
                    <p class="text-sm text-gray-500">{{ __('messages.paid') }}</p>
                    <p class="text-2xl font-bold text-green-600">{{ $stats['paid'] }}</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
                    <p class="text-sm text-gray-500">{{ __('messages.unpaid') }}</p>
                    <p class="text-2xl font-bold text-red-600">{{ $stats['unpaid'] }}</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
                    <p class="text-sm text-gray-500">{{ __('messages.collected') }}</p>
                    <p class="text-2xl font-bold text-green-600">RM {{ number_format($stats['total_collected'], 2) }}</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
                    <p class="text-sm text-gray-500">{{ __('messages.outstanding') }}</p>
                    <p class="text-2xl font-bold text-red-600">RM {{ number_format($stats['total_outstanding'], 2) }}</p>
                </div>
            </div>

            {{-- Filters --}}
            <div class="bg-white rounded-xl shadow-sm p-4 mb-6 border border-gray-100">
                <form method="GET" action="{{ route('admin.membership-fees.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.search') }}</label>
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="{{ __('messages.search_house_owner') }}"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-green-500 focus:border-green-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.status') }}</label>
                        <select name="status" class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-green-500 focus:border-green-500">
                            <option value="">{{ __('messages.all') }}</option>
                            <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>{{ __('messages.paid') }}</option>
                            <option value="unpaid" {{ request('status') === 'unpaid' ? 'selected' : '' }}>{{ __('messages.unpaid') }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.year') }}</label>
                        <select name="year" class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-green-500 focus:border-green-500">
                            <option value="">{{ __('messages.all') }}</option>
                            @foreach ($years as $year)
                                <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.street') }}</label>
                        <select name="street" class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-green-500 focus:border-green-500">
                            <option value="">{{ __('messages.all') }}</option>
                            @foreach ($streets as $street)
                                <option value="{{ $street }}" {{ request('street') === $street ? 'selected' : '' }}>{{ $street }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-end gap-2">
                        <button type="submit" class="flex-1 bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition">
                            {{ __('messages.filter') }}
                        </button>
                        <a href="{{ route('admin.membership-fees.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                            {{ __('messages.reset') }}
                        </a>
                    </div>
                </form>
            </div>

            {{-- Table --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.house') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.owner') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.year') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.amount') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.status') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.paid_at') }}</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($membershipFees as $fee)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <a href="{{ route('admin.houses.show', $fee->house) }}" class="text-green-600 hover:text-green-800 font-medium">
                                            {{ $fee->house->full_address }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $fee->owner_name }}
                                        @if ($fee->is_legacy)
                                            <span class="ml-1 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600">
                                                Legacy
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $fee->fee_year }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">RM {{ number_format($fee->amount, 2) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $fee->status_badge_class }}">
                                            {{ $fee->status === 'paid' ? __('messages.paid') : __('messages.unpaid') }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $fee->paid_at ? $fee->paid_at->format('d/m/Y') : '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex justify-end gap-2">
                                            @if ($fee->status === 'unpaid')
                                                <form action="{{ route('admin.membership-fees.mark-paid', $fee) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="text-green-600 hover:text-green-800" title="{{ __('messages.mark_as_paid') }}">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                        </svg>
                                                    </button>
                                                </form>
                                            @endif
                                            <a href="{{ route('admin.membership-fees.edit', $fee) }}" class="text-blue-600 hover:text-blue-800" title="{{ __('messages.edit') }}">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                        {{ __('messages.no_membership_fees_found') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if ($membershipFees->hasPages())
                    <div class="px-6 py-4 border-t border-gray-200">
                        {{ $membershipFees->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>

