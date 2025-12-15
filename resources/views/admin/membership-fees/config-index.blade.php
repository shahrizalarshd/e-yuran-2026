<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-4">
                <a href="{{ route('admin.membership-fees.index') }}" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </a>
                <h2 class="text-xl font-semibold text-gray-800">
                    {{ __('messages.membership_fee_configuration') }}
                </h2>
            </div>
            <a href="{{ route('admin.membership-fees.config.create') }}"
                class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                {{ __('messages.add_configuration') }}
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- Success Message --}}
            @if (session('success'))
                <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Current Active Config --}}
            @if ($currentConfig)
                <div class="bg-green-50 border border-green-200 rounded-xl p-6 mb-6">
                    <div class="flex items-center gap-3 mb-2">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <h3 class="text-lg font-semibold text-green-800">{{ __('messages.current_active_config') }}</h3>
                    </div>
                    <p class="text-3xl font-bold text-green-700">RM {{ number_format($currentConfig->amount, 2) }}</p>
                    <p class="text-sm text-green-600 mt-1">{{ $currentConfig->name }}</p>
                    <p class="text-sm text-green-600">
                        {{ __('messages.effective_from') }}: {{ $currentConfig->effective_from->format('d/m/Y') }}
                        @if ($currentConfig->effective_until)
                            - {{ $currentConfig->effective_until->format('d/m/Y') }}
                        @endif
                    </p>
                </div>
            @else
                <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-6 mb-6">
                    <p class="text-yellow-800">{{ __('messages.no_active_config') }}</p>
                    <p class="text-sm text-yellow-600">{{ __('messages.default_amount_used') }}: RM 20.00</p>
                </div>
            @endif

            {{-- Configuration List --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">{{ __('messages.all_configurations') }}</h3>
                </div>

                @if ($configurations->isEmpty())
                    <div class="px-6 py-12 text-center text-gray-500">
                        {{ __('messages.no_configurations_found') }}
                    </div>
                @else
                    <div class="divide-y divide-gray-200">
                        @foreach ($configurations as $config)
                            <div class="px-6 py-4 flex items-center justify-between hover:bg-gray-50">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <h4 class="font-medium text-gray-900">{{ $config->name }}</h4>
                                        @if ($config->is_active)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                                {{ __('messages.active') }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600">
                                                {{ __('messages.inactive') }}
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-lg font-semibold text-gray-700">RM {{ number_format($config->amount, 2) }}</p>
                                    <p class="text-sm text-gray-500">
                                        {{ $config->effective_from->format('d/m/Y') }}
                                        @if ($config->effective_until)
                                            - {{ $config->effective_until->format('d/m/Y') }}
                                        @else
                                            - {{ __('messages.no_end_date') }}
                                        @endif
                                    </p>
                                    @if ($config->description)
                                        <p class="text-sm text-gray-400 mt-1">{{ $config->description }}</p>
                                    @endif
                                </div>
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('admin.membership-fees.config.edit', $config) }}"
                                        class="p-2 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-lg transition">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </a>
                                    <form action="{{ route('admin.membership-fees.config.destroy', $config) }}" method="POST"
                                        onsubmit="return confirm('{{ __('messages.confirm_delete_config') }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 text-red-600 hover:text-red-800 hover:bg-red-50 rounded-lg transition">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>

