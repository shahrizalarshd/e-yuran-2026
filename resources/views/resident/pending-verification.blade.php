<x-app-layout>
    <x-slot name="title">{{ __('Menunggu Pengesahan') }}</x-slot>

    <div class="max-w-lg mx-auto">
        <div class="bg-white rounded-2xl shadow-sm p-8 text-center">
            <div class="w-20 h-20 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-10 h-10 text-yellow-600 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h2 class="text-xl font-bold text-gray-900 mb-2">{{ __('Menunggu Pengesahan') }}</h2>
            <p class="text-gray-500 mb-6">{{ __('Pendaftaran anda sedang diproses oleh pentadbir taman.') }}</p>
        </div>

        @if($pendingMemberships->count() > 0)
        <div class="mt-6 bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="p-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900">{{ __('Permohonan Anda') }}</h3>
            </div>
            <div class="divide-y divide-gray-100">
                @foreach($pendingMemberships as $membership)
                    <div class="p-4 flex items-center justify-between">
                        <div>
                            <p class="font-medium text-gray-900">{{ $membership->house->full_address }}</p>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="text-sm text-gray-500">{{ __('messages.' . $membership->relationship) }}</span>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
                                    {{ __('messages.pending') }}
                                </span>
                            </div>
                        </div>
                        <span class="text-xs text-gray-400">{{ $membership->created_at->diffForHumans() }}</span>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        <div class="mt-6 p-4 bg-blue-50 rounded-lg">
            <div class="flex gap-3">
                <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div class="text-sm text-blue-800">
                    <p class="font-medium mb-1">{{ __('Apa yang seterusnya?') }}</p>
                    <p>{{ __('Anda akan menerima notifikasi apabila pendaftaran anda diluluskan. Selepas itu, anda boleh melihat dan membayar bil yuran.') }}</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

