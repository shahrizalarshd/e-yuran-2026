<x-app-layout>
    <x-slot name="title">{{ __('messages.audit_log_details') }}</x-slot>

    <div class="max-w-3xl mx-auto">
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="p-4 border-b border-gray-100 flex items-center justify-between">
                <h2 class="font-semibold text-gray-900">{{ __('messages.audit_log_details') }}</h2>
                <a href="{{ route('admin.audit-logs.index') }}" class="text-primary-600 hover:text-primary-700 text-sm font-medium">
                    &larr; {{ __('messages.back') }}
                </a>
            </div>

            <div class="p-4 space-y-4">
                <!-- Basic Info -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-500">{{ __('messages.date_time') }}</label>
                        <p class="mt-1 text-gray-900">{{ $auditLog->created_at->format('d/m/Y H:i:s') }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">{{ __('messages.user') }}</label>
                        <p class="mt-1 text-gray-900">
                            @if($auditLog->user)
                                {{ $auditLog->user->name }}
                            @else
                                <span class="text-gray-400">{{ __('messages.system') }}</span>
                            @endif
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">{{ __('messages.action') }}</label>
                        <p class="mt-1">
                            @php
                                $actionColors = [
                                    'create' => 'bg-green-100 text-green-700',
                                    'update' => 'bg-blue-100 text-blue-700',
                                    'delete' => 'bg-red-100 text-red-700',
                                    'login' => 'bg-purple-100 text-purple-700',
                                    'logout' => 'bg-gray-100 text-gray-700',
                                ];
                                $color = $actionColors[$auditLog->action] ?? 'bg-gray-100 text-gray-700';
                            @endphp
                            <span class="px-2 py-1 rounded-full text-xs font-medium {{ $color }}">
                                {{ ucfirst($auditLog->action) }}
                            </span>
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">{{ __('messages.model') }}</label>
                        <p class="mt-1 text-gray-900">
                            @if($auditLog->model_type)
                                {{ class_basename($auditLog->model_type) }}
                                @if($auditLog->model_id)
                                    <span class="text-gray-400">#{{ $auditLog->model_id }}</span>
                                @endif
                            @else
                                -
                            @endif
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">IP Address</label>
                        <p class="mt-1 text-gray-900 font-mono text-sm">{{ $auditLog->ip_address ?? '-' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">User Agent</label>
                        <p class="mt-1 text-gray-600 text-xs break-all">{{ $auditLog->user_agent ?? '-' }}</p>
                    </div>
                </div>

                <!-- Description -->
                @if($auditLog->description)
                    <div>
                        <label class="block text-sm font-medium text-gray-500">{{ __('messages.description') }}</label>
                        <p class="mt-1 text-gray-900">{{ $auditLog->description }}</p>
                    </div>
                @endif

                <!-- Old Values -->
                @if($auditLog->old_values)
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-2">{{ __('messages.old_values') }}</label>
                        <pre class="bg-red-50 rounded-lg p-4 text-sm text-red-800 overflow-x-auto">{{ json_encode($auditLog->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    </div>
                @endif

                <!-- New Values -->
                @if($auditLog->new_values)
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-2">{{ __('messages.new_values') }}</label>
                        <pre class="bg-green-50 rounded-lg p-4 text-sm text-green-800 overflow-x-auto">{{ json_encode($auditLog->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>

