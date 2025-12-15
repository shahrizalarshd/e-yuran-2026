<x-app-layout>
    <x-slot name="title">{{ __('messages.house_settings') }}</x-slot>

    <div class="space-y-4 lg:space-y-6">
        <!-- House Info Card -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 bg-primary-100 rounded-xl flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                </div>
                <div>
                    <h2 class="font-semibold text-gray-900 text-lg">{{ $house->house_no }}</h2>
                    <p class="text-gray-500 text-sm">{{ $house->full_address }}</p>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 mt-2">
                        {{ __('Anda adalah Pemilik') }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Payer Management Card -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="p-4 lg:p-6 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900 flex items-center gap-2">
                    <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    {{ __('Tetapan Pembayar') }}
                </h3>
                <p class="text-sm text-gray-500 mt-1">
                    {{ __('Pilih siapa yang bertanggungjawab membayar bil yuran bulanan untuk rumah ini.') }}
                </p>
            </div>

            <div class="p-4 lg:p-6">
                <!-- Current Payer Info -->
                <div class="bg-blue-50 border border-blue-100 rounded-lg p-4 mb-6">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-blue-600 font-medium">{{ __('Pembayar Semasa') }}</p>
                            <p class="font-semibold text-blue-900">
                                {{ $currentPayer ? $currentPayer->resident->name : __('Tiada pembayar ditetapkan') }}
                            </p>
                            @if($currentPayer)
                                <p class="text-xs text-blue-600">
                                    {{ $currentPayer->role === 'owner' ? __('Pemilik') : __('Penyewa') }}
                                </p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Change Payer Form -->
                <form action="{{ route('resident.house-settings.update-payer', $house) }}" method="POST">
                    @csrf

                    <label class="block text-sm font-medium text-gray-700 mb-3">
                        {{ __('Pilih Pembayar Baharu') }}
                    </label>

                    <div class="space-y-3">
                        @foreach($occupants as $occupant)
                            <label class="flex items-start gap-3 p-4 border rounded-xl cursor-pointer transition hover:border-primary-300 hover:bg-primary-50/50 {{ $occupant->is_payer ? 'border-primary-500 bg-primary-50' : 'border-gray-200' }}">
                                <input type="radio" 
                                       name="payer_resident_id" 
                                       value="{{ $occupant->resident_id }}" 
                                       class="w-5 h-5 text-primary-600 border-gray-300 focus:ring-primary-500 mt-1 flex-shrink-0"
                                       {{ $occupant->is_payer ? 'checked' : '' }}>
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium text-gray-900 break-words">{{ $occupant->resident->name }}</p>
                                    <div class="flex flex-wrap gap-1.5 mt-1.5">
                                        @if($occupant->role === 'owner')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-700">
                                                {{ __('Pemilik') }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-700">
                                                {{ __('Penyewa') }}
                                            </span>
                                        @endif
                                        @if($occupant->is_payer)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-700">
                                                {{ __('Pembayar Semasa') }}
                                            </span>
                                        @endif
                                    </div>
                                    @if($occupant->resident->email)
                                        <p class="text-sm text-gray-500 mt-1 truncate">{{ $occupant->resident->email }}</p>
                                    @endif
                                    @if($occupant->resident->phone)
                                        <p class="text-sm text-gray-500">{{ $occupant->resident->phone }}</p>
                                    @endif
                                </div>
                                <div class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center flex-shrink-0">
                                    <span class="text-gray-600 font-semibold text-sm">
                                        {{ strtoupper(substr($occupant->resident->name, 0, 1)) }}
                                    </span>
                                </div>
                            </label>
                        @endforeach
                    </div>

                    @if($occupants->count() <= 1)
                        <div class="mt-4 p-4 bg-yellow-50 border border-yellow-100 rounded-lg">
                            <div class="flex items-start gap-3">
                                <svg class="w-5 h-5 text-yellow-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                <div>
                                    <p class="text-sm font-medium text-yellow-800">{{ __('Tiada penyewa berdaftar') }}</p>
                                    <p class="text-sm text-yellow-700 mt-1">
                                        {{ __('Untuk menetapkan penyewa sebagai pembayar, sila hubungi admin untuk mendaftarkan penyewa terlebih dahulu.') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif

                    @error('payer_resident_id')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror

                    <div class="mt-6 flex flex-col sm:flex-row gap-3">
                        <button type="submit" class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-3 bg-primary-600 text-white font-semibold rounded-xl hover:bg-primary-700 transition min-h-touch">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            {{ __('Simpan Perubahan') }}
                        </button>
                        <a href="{{ route('resident.dashboard') }}" class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-3 bg-gray-100 text-gray-700 font-semibold rounded-xl hover:bg-gray-200 transition min-h-touch">
                            {{ __('Kembali') }}
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Info Card -->
        <div class="bg-gray-50 rounded-xl p-4 lg:p-6">
            <h4 class="font-medium text-gray-900 flex items-center gap-2 mb-3">
                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ __('Maklumat Penting') }}
            </h4>
            <ul class="space-y-2 text-sm text-gray-600">
                <li class="flex items-start gap-2">
                    <svg class="w-4 h-4 text-primary-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    {{ __('Pembayar adalah individu yang bertanggungjawab membayar bil yuran bulanan.') }}
                </li>
                <li class="flex items-start gap-2">
                    <svg class="w-4 h-4 text-primary-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    {{ __('Secara default, pemilik adalah pembayar. Anda boleh menetapkan penyewa sebagai pembayar jika perlu.') }}
                </li>
                <li class="flex items-start gap-2">
                    <svg class="w-4 h-4 text-primary-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    {{ __('Perubahan pembayar akan direkodkan dan dimaklumkan kepada pihak berkaitan.') }}
                </li>
                <li class="flex items-start gap-2">
                    <svg class="w-4 h-4 text-primary-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    {{ __('Hanya pembayar yang akan melihat dan boleh membayar bil melalui sistem.') }}
                </li>
            </ul>
        </div>
    </div>
</x-app-layout>

