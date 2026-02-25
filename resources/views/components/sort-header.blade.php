@props(['column', 'label', 'align' => 'left'])

@php
    $currentSort = request('sort_by');
    $currentDir = request('sort_dir', 'asc');
    $isActive = $currentSort === $column;
    $nextDir = $isActive && $currentDir === 'asc' ? 'desc' : 'asc';
    
    // Build URL preserving all query params
    $params = array_merge(request()->query(), ['sort_by' => $column, 'sort_dir' => $nextDir]);
    $url = request()->url() . '?' . http_build_query($params);
    
    $alignClass = match($align) {
        'center' => 'justify-center',
        'right' => 'justify-end',
        default => 'justify-start',
    };
    $textAlign = match($align) {
        'center' => 'text-center',
        'right' => 'text-right',
        default => 'text-left',
    };
@endphp

<th class="px-4 py-3 {{ $textAlign }} text-xs font-medium text-gray-500 uppercase tracking-wider">
    <a href="{{ $url }}" class="inline-flex items-center gap-1 {{ $alignClass }} hover:text-gray-700 transition group">
        {{ $label }}
        <span class="inline-flex flex-col {{ $isActive ? 'text-primary-600' : 'text-gray-300 group-hover:text-gray-400' }}">
            <svg class="w-3 h-3 -mb-1 {{ $isActive && $currentDir === 'asc' ? 'text-primary-600' : '' }}" viewBox="0 0 12 12" fill="currentColor">
                <path d="M6 2L10 7H2L6 2Z"/>
            </svg>
            <svg class="w-3 h-3 {{ $isActive && $currentDir === 'desc' ? 'text-primary-600' : '' }}" viewBox="0 0 12 12" fill="currentColor">
                <path d="M6 10L2 5H10L6 10Z"/>
            </svg>
        </span>
    </a>
</th>
