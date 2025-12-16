<x-app-layout>
    <x-slot name="title">{{ $house->full_address }}</x-slot>

    <div class="space-y-6">
        <!-- House Info Card -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="p-4 lg:p-6 border-b border-gray-100 flex items-start justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">{{ $house->full_address }}</h2>
                    <div class="flex items-center gap-2 mt-2">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $house->is_registered ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                            {{ $house->is_registered ? __('messages.is_registered') : __('Tidak Berdaftar') }}
                        </span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $house->is_active ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                            {{ $house->is_active ? __('messages.active') : __('messages.inactive') }}
                        </span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $house->status === 'occupied' ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-800' }}">
                            {{ __('messages.' . $house->status) }}
                        </span>
                    </div>
                </div>
                @if(auth()->user()->isSuperAdmin() || auth()->user()->isTreasurer())
                <a href="{{ route('admin.houses.edit', $house) }}" class="px-4 py-2 bg-primary-600 text-white font-medium rounded-lg hover:bg-primary-700 transition min-h-touch flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    {{ __('messages.edit') }}
                </a>
                @endif
            </div>

            <div class="p-4 lg:p-6 grid lg:grid-cols-3 gap-6">
                <!-- Owner -->
                <div class="p-4 bg-gray-50 rounded-lg">
                    <p class="text-sm text-gray-500 mb-2">{{ __('messages.owner') }}</p>
                    @if($currentOwner)
                        <p class="font-medium text-gray-900">{{ $currentOwner->resident->name }}</p>
                        <p class="text-sm text-gray-500">{{ $currentOwner->resident->phone ?? '-' }}</p>
                        @if($currentOwner->is_payer)
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-primary-100 text-primary-800 mt-2">Payer</span>
                        @endif
                    @else
                        <p class="text-gray-400">{{ __('Tiada pemilik') }}</p>
                    @endif
                </div>

                <!-- Tenant -->
                <div class="p-4 bg-gray-50 rounded-lg">
                    <p class="text-sm text-gray-500 mb-2">{{ __('messages.tenant') }}</p>
                    @if($currentTenant)
                        <p class="font-medium text-gray-900">{{ $currentTenant->resident->name }}</p>
                        <p class="text-sm text-gray-500">{{ $currentTenant->resident->phone ?? '-' }}</p>
                        @if($currentTenant->is_payer)
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-primary-100 text-primary-800 mt-2">Payer</span>
                        @endif
                    @else
                        <p class="text-gray-400">{{ __('Tiada penyewa') }}</p>
                    @endif
                </div>

                <!-- Outstanding -->
                <div class="p-4 bg-red-50 rounded-lg">
                    <p class="text-sm text-red-600 mb-2">{{ __('messages.outstanding') }}</p>
                    <p class="text-2xl font-bold text-red-700">RM {{ number_format($house->outstanding_amount, 2) }}</p>
                </div>
            </div>
        </div>

        <!-- Timeline/History -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="p-4 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h3 class="font-semibold text-gray-900">{{ __('Sejarah & Timeline Rumah') }}</h3>
                    <p class="text-sm text-gray-500 mt-1">{{ __('Kronologi lengkap sejak rumah didaftarkan') }}</p>
                </div>
                <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-medium">
                    {{ count($timeline) }} {{ __('Aktiviti') }}
                </span>
            </div>
            @if(count($timeline) > 0)
                <div class="p-4 lg:p-6">
                    <div class="relative">
                        <!-- Timeline line -->
                        <div class="absolute left-6 top-0 bottom-0 w-0.5 bg-gray-200"></div>
                        
                        <!-- Timeline events -->
                        <div class="space-y-6">
                            @foreach($timeline as $event)
                                <div class="relative flex gap-4">
                                    <!-- Icon circle -->
                                    <div class="relative z-10 flex items-center justify-center w-12 h-12 rounded-full bg-{{ $event['color'] }}-100 flex-shrink-0">
                                        @if($event['icon'] === 'home')
                                            <svg class="w-6 h-6 text-{{ $event['color'] }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                            </svg>
                                        @elseif($event['icon'] === 'user-check')
                                            <svg class="w-6 h-6 text-{{ $event['color'] }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        @elseif($event['icon'] === 'users')
                                            <svg class="w-6 h-6 text-{{ $event['color'] }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                            </svg>
                                        @elseif($event['icon'] === 'user-plus')
                                            <svg class="w-6 h-6 text-{{ $event['color'] }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                                            </svg>
                                        @elseif($event['icon'] === 'user-minus')
                                            <svg class="w-6 h-6 text-{{ $event['color'] }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7a4 4 0 11-8 0 4 4 0 018 0zM9 14a6 6 0 00-6 6v1h12v-1a6 6 0 00-6-6zM21 12h-6"/>
                                            </svg>
                                        @elseif($event['icon'] === 'check-circle')
                                            <svg class="w-6 h-6 text-{{ $event['color'] }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        @elseif($event['icon'] === 'x-circle')
                                            <svg class="w-6 h-6 text-{{ $event['color'] }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        @elseif($event['icon'] === 'refresh')
                                            <svg class="w-6 h-6 text-{{ $event['color'] }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                            </svg>
                                        @else
                                            <svg class="w-6 h-6 text-{{ $event['color'] }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        @endif
                                    </div>

                                    <!-- Event content -->
                                    <div class="flex-1 pb-6">
                                        <div class="bg-gray-50 rounded-lg p-4 hover:bg-gray-100 transition">
                                            <div class="flex items-start justify-between mb-2">
                                                <div>
                                                    <h4 class="font-semibold text-gray-900">{{ $event['title'] }}</h4>
                                                    @if(isset($event['meta']))
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-primary-100 text-primary-800 mt-1">
                                                            {{ $event['meta'] }}
                                                        </span>
                                                    @endif
                                                </div>
                                                <span class="text-xs text-gray-500 whitespace-nowrap ml-4">
                                                    {{ $event['date']->format('d/m/Y') }}
                                                </span>
                                            </div>
                                            <p class="text-sm text-gray-600">{{ $event['description'] }}</p>
                                            <div class="flex items-center gap-2 mt-2 text-xs text-gray-400">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                                {{ $event['date']->diffForHumans() }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @else
                <div class="p-8 text-center text-gray-500">
                    {{ __('Tiada sejarah tersedia') }}
                </div>
            @endif
        </div>

        <!-- House Members -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="p-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900">{{ __('Ahli Rumah') }}</h3>
            </div>
            @if($house->members->count() > 0)
                <div class="divide-y divide-gray-100">
                    @foreach($house->members as $member)
                        <div class="p-4 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center">
                                    <span class="text-gray-600 font-medium">{{ strtoupper(substr($member->resident->name, 0, 1)) }}</span>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">{{ $member->resident->name }}</p>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="text-xs text-gray-500">{{ __('messages.' . $member->relationship) }}</span>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $member->status_badge_class }}">
                                            {{ __('messages.' . $member->status) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 text-sm">
                                @if($member->can_view_bills)
                                    <span class="px-2 py-1 bg-blue-50 text-blue-700 rounded text-xs">{{ __('Lihat Bil') }}</span>
                                @endif
                                @if($member->can_pay)
                                    <span class="px-2 py-1 bg-green-50 text-green-700 rounded text-xs">{{ __('Bayar') }}</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="p-8 text-center text-gray-500">
                    {{ __('Tiada ahli berdaftar') }}
                </div>
            @endif
        </div>

        <!-- Bills -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="p-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="font-semibold text-gray-900">{{ __('messages.bills') }}</h3>
            </div>
            @if($house->bills->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('messages.bill_period') }}</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('messages.amount') }}</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">{{ __('messages.status') }}</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('messages.due_date') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($house->bills->take(12) as $bill)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 font-medium text-gray-900">{{ $bill->bill_period }}</td>
                                    <td class="px-4 py-3 text-right text-gray-600">RM {{ number_format($bill->amount, 2) }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $bill->status_badge_class }}">
                                            {{ __('messages.' . $bill->status) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right text-gray-500">{{ $bill->due_date->format('d/m/Y') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-8 text-center text-gray-500">
                    {{ __('Tiada bil') }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>

