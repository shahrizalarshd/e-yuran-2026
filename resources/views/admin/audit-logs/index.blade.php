<x-app-layout>
    <x-slot name="title">{{ __('messages.audit_logs') }}</x-slot>

    <div class="space-y-4">
        <!-- Filters -->
        <div class="bg-white rounded-xl shadow-sm p-4">
            <form action="{{ route('admin.audit-logs.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.user') }}</label>
                    <select name="user_id" class="w-full rounded-lg border-gray-300 text-sm focus:ring-primary-500 focus:border-primary-500">
                        <option value="">{{ __('messages.all') }}</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" @selected(request('user_id') == $user->id)>{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.action') }}</label>
                    <select name="action" class="w-full rounded-lg border-gray-300 text-sm focus:ring-primary-500 focus:border-primary-500">
                        <option value="">{{ __('messages.all') }}</option>
                        @foreach($actions as $action)
                            <option value="{{ $action }}" @selected(request('action') == $action)>{{ ucfirst($action) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.model') }}</label>
                    <select name="model_type" class="w-full rounded-lg border-gray-300 text-sm focus:ring-primary-500 focus:border-primary-500">
                        <option value="">{{ __('messages.all') }}</option>
                        @foreach($modelTypes as $type)
                            <option value="{{ $type }}" @selected(request('model_type') == $type)>{{ $type }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.from_date') }}</label>
                    <input type="date" name="from_date" value="{{ request('from_date') }}" class="w-full rounded-lg border-gray-300 text-sm focus:ring-primary-500 focus:border-primary-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.to_date') }}</label>
                    <input type="date" name="to_date" value="{{ request('to_date') }}" class="w-full rounded-lg border-gray-300 text-sm focus:ring-primary-500 focus:border-primary-500">
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition min-h-touch">
                        {{ __('messages.filter') }}
                    </button>
                    <a href="{{ route('admin.audit-logs.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition min-h-touch">
                        {{ __('messages.reset') }}
                    </a>
                </div>
            </form>
        </div>

        <!-- Logs Table -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="p-4 border-b border-gray-100">
                <h2 class="font-semibold text-gray-900">{{ __('messages.audit_logs') }} ({{ $logs->total() }})</h2>
            </div>

            @if($logs->isEmpty())
                <div class="p-8 text-center text-gray-500">
                    {{ __('messages.no_data') }}
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.date_time') }}</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.user') }}</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.action') }}</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.model') }}</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.description') }}</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">IP</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($logs as $log)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 whitespace-nowrap text-gray-600">
                                        {{ $log->created_at->format('d/m/Y H:i:s') }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        @if($log->user)
                                            <span class="font-medium text-gray-900">{{ $log->user->name }}</span>
                                        @else
                                            <span class="text-gray-400">{{ __('messages.system') }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        @php
                                            $actionColors = [
                                                'create' => 'bg-green-100 text-green-700',
                                                'update' => 'bg-blue-100 text-blue-700',
                                                'delete' => 'bg-red-100 text-red-700',
                                                'login' => 'bg-purple-100 text-purple-700',
                                                'logout' => 'bg-gray-100 text-gray-700',
                                            ];
                                            $color = $actionColors[$log->action] ?? 'bg-gray-100 text-gray-700';
                                        @endphp
                                        <span class="px-2 py-1 rounded-full text-xs font-medium {{ $color }}">
                                            {{ ucfirst($log->action) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-gray-600">
                                        @if($log->model_type)
                                            {{ class_basename($log->model_type) }}
                                            @if($log->model_id)
                                                <span class="text-gray-400">#{{ $log->model_id }}</span>
                                            @endif
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-gray-600 max-w-xs truncate">
                                        {{ $log->description ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-gray-500 text-xs font-mono">
                                        {{ $log->ip_address ?? '-' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="p-4 border-t border-gray-100">
                    {{ $logs->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>

