<x-app-layout>
    <x-slot name="title">{{ __('messages.user_verification') }}</x-slot>

    <div class="space-y-4">
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="p-4 border-b border-gray-100">
                <h2 class="font-semibold text-gray-900">{{ __('messages.pending_verification') }} ({{ $pendingMembers->total() }})</h2>
            </div>

            @if($pendingMembers->count() > 0)
                <div class="divide-y divide-gray-100">
                    @foreach($pendingMembers as $member)
                        <div class="p-4 lg:p-6" x-data="{ showReject: false }">
                            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                                <div class="flex items-start gap-4">
                                    <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center flex-shrink-0">
                                        <span class="text-gray-600 font-semibold">{{ strtoupper(substr($member->resident->name, 0, 1)) }}</span>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900">{{ $member->resident->name }}</p>
                                        <p class="text-sm text-gray-500">{{ $member->resident->email }}</p>
                                        <p class="text-sm text-gray-500">{{ $member->resident->phone ?? '-' }}</p>
                                        <div class="flex items-center gap-2 mt-2">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                                {{ __('messages.' . $member->relationship) }}
                                            </span>
                                            <span class="text-xs text-gray-400">{{ $member->created_at->diffForHumans() }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex-shrink-0">
                                    <div class="p-3 bg-gray-50 rounded-lg">
                                        <p class="text-xs text-gray-500 mb-1">{{ __('messages.house') }}</p>
                                        <p class="font-medium text-gray-900">{{ $member->house->full_address }}</p>
                                    </div>
                                </div>

                                @if(auth()->user()->canVerifyUsers())
                                <div class="flex gap-2 lg:flex-col">
                                    <form action="{{ route('admin.verifications.approve', $member) }}" method="POST" class="flex-1">
                                        @csrf
                                        <button type="submit" class="w-full px-4 py-2 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 transition min-h-touch flex items-center justify-center gap-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            {{ __('messages.approve') }}
                                        </button>
                                    </form>
                                    
                                    <button @click="showReject = !showReject" type="button" class="flex-1 px-4 py-2 bg-red-100 text-red-700 font-medium rounded-lg hover:bg-red-200 transition min-h-touch flex items-center justify-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                        {{ __('messages.reject') }}
                                    </button>
                                </div>
                                @endif
                            </div>

                            <!-- Reject Form -->
                            <div x-show="showReject" x-cloak class="mt-4 p-4 bg-red-50 rounded-lg">
                                <form action="{{ route('admin.verifications.reject', $member) }}" method="POST" class="space-y-3">
                                    @csrf
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Sebab Penolakan') }} ({{ __('Pilihan') }})</label>
                                        <textarea name="rejection_reason" rows="2" class="w-full rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500" placeholder="{{ __('Nyatakan sebab penolakan...') }}"></textarea>
                                    </div>
                                    <div class="flex gap-2">
                                        <button type="button" @click="showReject = false" class="flex-1 px-4 py-2 bg-white text-gray-700 font-medium rounded-lg border border-gray-300 hover:bg-gray-50 transition min-h-touch">
                                            {{ __('messages.cancel') }}
                                        </button>
                                        <button type="submit" class="flex-1 px-4 py-2 bg-red-600 text-white font-medium rounded-lg hover:bg-red-700 transition min-h-touch">
                                            {{ __('Sahkan Tolak') }}
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="p-8 text-center">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-1">{{ __('Tiada permohonan') }}</h3>
                    <p class="text-gray-500 text-sm">{{ __('Semua permohonan pendaftaran telah diproses') }}</p>
                </div>
            @endif
        </div>

        <!-- Pagination -->
        @if($pendingMembers->hasPages())
        <div class="mt-4">
            {{ $pendingMembers->links() }}
        </div>
        @endif
    </div>
</x-app-layout>

