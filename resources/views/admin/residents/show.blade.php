<x-app-layout>
    <x-slot name="title">{{ $resident->name }}</x-slot>

    <div class="max-w-4xl mx-auto space-y-4">
        <!-- Back Button -->
        <div>
            <a href="{{ route('admin.residents.index') }}" class="inline-flex items-center text-primary-600 hover:text-primary-700 font-medium">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                {{ __('messages.back') }}
            </a>
        </div>

        <!-- Resident Info -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="p-4 border-b border-gray-100 bg-primary-50">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 bg-primary-100 rounded-full flex items-center justify-center">
                        <span class="text-2xl font-bold text-primary-600">{{ strtoupper(substr($resident->name, 0, 1)) }}</span>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">{{ $resident->name }}</h2>
                        <p class="text-gray-600">{{ $resident->email }}</p>
                    </div>
                </div>
            </div>

            <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-500">{{ __('messages.ic_number') }}</label>
                    <p class="mt-1 text-gray-900">{{ $resident->ic_number ?? '-' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500">{{ __('messages.phone') }}</label>
                    <p class="mt-1 text-gray-900">{{ $resident->phone ?? '-' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500">{{ __('messages.email') }}</label>
                    <p class="mt-1 text-gray-900">{{ $resident->email }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500">{{ __('messages.status') }}</label>
                    <p class="mt-1">
                        @if($resident->user)
                            <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-700">
                                {{ __('messages.active') }}
                            </span>
                        @else
                            <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-600">
                                {{ __('messages.inactive') }}
                            </span>
                        @endif
                    </p>
                </div>
            </div>
        </div>

        <!-- House Memberships -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="p-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900">{{ __('messages.houses') }}</h3>
            </div>

            @if($resident->houseMemberships->isEmpty())
                <div class="p-8 text-center text-gray-500">
                    {{ __('messages.no_data') }}
                </div>
            @else
                <div class="divide-y divide-gray-100">
                    @foreach($resident->houseMemberships as $membership)
                        <div class="p-4">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('admin.houses.show', $membership->house) }}" class="font-medium text-primary-600 hover:text-primary-700">
                                            {{ $membership->house->full_address }}
                                        </a>
                                        <span class="px-2 py-0.5 text-xs rounded-full 
                                            @if($membership->status === 'active') bg-green-100 text-green-700
                                            @elseif($membership->status === 'pending') bg-yellow-100 text-yellow-700
                                            @else bg-red-100 text-red-700 @endif">
                                            {{ ucfirst($membership->status) }}
                                        </span>
                                    </div>
                                    <p class="text-sm text-gray-500 mt-1">
                                        {{ __('messages.relationship') }}: {{ ucfirst($membership->relationship) }}
                                    </p>
                                </div>

                                @if($membership->status === 'active')
                                    <form action="{{ route('admin.members.permissions', $membership) }}" method="POST" class="flex items-center gap-4">
                                        @csrf
                                        @method('PATCH')
                                        <label class="flex items-center gap-2 text-sm">
                                            <input type="checkbox" name="can_view_bills" value="1" @checked($membership->can_view_bills) class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                                            {{ __('messages.can_view_bills') }}
                                        </label>
                                        <label class="flex items-center gap-2 text-sm">
                                            <input type="checkbox" name="can_pay" value="1" @checked($membership->can_pay) class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                                            {{ __('messages.can_pay') }}
                                        </label>
                                        <button type="submit" class="px-3 py-1 bg-primary-600 text-white text-sm rounded-lg hover:bg-primary-700 transition">
                                            {{ __('messages.save') }}
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- House Occupancies -->
        @if($resident->occupancies->isNotEmpty())
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="p-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-900">{{ __('messages.owner') }} / {{ __('messages.tenant') }}</h3>
                </div>
                <div class="divide-y divide-gray-100">
                    @foreach($resident->occupancies as $occupancy)
                        <div class="p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <a href="{{ route('admin.houses.show', $occupancy->house) }}" class="font-medium text-primary-600 hover:text-primary-700">
                                        {{ $occupancy->house->full_address }}
                                    </a>
                                    <p class="text-sm text-gray-500 mt-1">
                                        {{ ucfirst($occupancy->type) }} - 
                                        {{ $occupancy->start_date->format('d/m/Y') }}
                                        @if($occupancy->end_date)
                                            - {{ $occupancy->end_date->format('d/m/Y') }}
                                        @else
                                            - {{ __('messages.active') }}
                                        @endif
                                    </p>
                                </div>
                                <span class="px-2 py-1 text-xs rounded-full {{ $occupancy->is_current ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                    {{ $occupancy->is_current ? __('messages.active') : __('messages.inactive') }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Recent Payments -->
        @if($resident->payments->isNotEmpty())
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="p-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-900">{{ __('messages.recent_payments') }}</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium text-gray-500">{{ __('messages.date') }}</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500">{{ __('messages.payment_no') }}</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500">{{ __('messages.house') }}</th>
                                <th class="px-4 py-3 text-right font-medium text-gray-500">{{ __('messages.amount') }}</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500">{{ __('messages.status') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($resident->payments as $payment)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-gray-600">
                                        {{ $payment->created_at->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <a href="{{ route('admin.payments.show', $payment) }}" class="text-primary-600 hover:text-primary-700 font-medium">
                                            {{ $payment->payment_no }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-3 text-gray-600">
                                        {{ $payment->house->full_address ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-right font-medium text-gray-900">
                                        RM {{ number_format($payment->amount, 2) }}
                                    </td>
                                    <td class="px-4 py-3">
                                        @php
                                            $statusColors = [
                                                'success' => 'bg-green-100 text-green-700',
                                                'pending' => 'bg-yellow-100 text-yellow-700',
                                                'failed' => 'bg-red-100 text-red-700',
                                            ];
                                        @endphp
                                        <span class="px-2 py-1 text-xs rounded-full {{ $statusColors[$payment->status] ?? 'bg-gray-100 text-gray-600' }}">
                                            {{ ucfirst($payment->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>

