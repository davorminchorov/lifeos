@props(['priority'])

@php
    $config = [
        0 => [
            'label' => 'Low',
            'class' => 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300',
            'icon' => 'M19 9l-7 7-7-7',
        ],
        1 => [
            'label' => 'Medium',
            'class' => 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-200',
            'icon' => 'M5 12h14',
        ],
        2 => [
            'label' => 'High',
            'class' => 'bg-orange-100 text-orange-700 dark:bg-orange-900 dark:text-orange-200',
            'icon' => 'M5 15l7-7 7 7',
        ],
        3 => [
            'label' => 'Urgent',
            'class' => 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-200',
            'icon' => 'M5 10l7-7m0 0l7 7m-7-7v18',
        ],
    ];

    $priorityValue = is_int($priority) ? $priority : (int) $priority;
    $priorityConfig = $config[$priorityValue] ?? $config[0];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center px-2 py-1 rounded text-xs font-medium ' . $priorityConfig['class']]) }}>
    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $priorityConfig['icon'] }}"></path>
    </svg>
    {{ $priorityConfig['label'] }}
</span>
