@props(['status'])

@php
    $colors = [
        'gray' => 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200',
        'blue' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
        'indigo' => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200',
        'purple' => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200',
        'yellow' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
        'green' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
        'emerald' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-200',
        'red' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
        'orange' => 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200',
        'slate' => 'bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-200',
    ];

    $colorClass = $colors[$status->color()] ?? $colors['gray'];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ' . $colorClass]) }}>
    {{ $status->label() }}
</span>
