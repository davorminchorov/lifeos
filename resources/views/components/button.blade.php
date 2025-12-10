@props([
    'variant' => 'primary', // primary | secondary | danger | subtle
    'size' => 'md', // sm | md
    'href' => null,
    'type' => 'button',
    'disabled' => false,
    'loading' => false,
])

@php
    $base = 'inline-flex items-center justify-center rounded-md font-medium transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2';

    $variants = [
        'primary' => 'bg-[color:var(--color-accent-600)] hover:bg-[color:var(--color-accent-700)] text-white focus:ring-[color:var(--color-accent-500)]',
        'secondary' => 'bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] hover:bg-[color:var(--color-primary-100)] dark:hover:bg-[color:var(--color-dark-200)] focus:ring-[color:var(--color-primary-300)]',
        'danger' => 'bg-[color:var(--color-danger-500)] hover:bg-[color:var(--color-danger-600)] text-white focus:ring-[color:var(--color-danger-500)]',
        'subtle' => 'bg-transparent text-[color:var(--color-primary-600)] hover:text-[color:var(--color-accent-600)] focus:ring-[color:var(--color-primary-300)]',
    ];

    $sizes = [
        'sm' => 'text-sm px-3 py-1.5',
        'md' => 'text-sm px-4 py-2',
    ];

    $classes = $base.' '.($variants[$variant] ?? $variants['primary']).' '.($sizes[$size] ?? $sizes['md']);
    if ($disabled || $loading) {
        $classes .= ' opacity-50 cursor-not-allowed pointer-events-none';
    }
@endphp

@if($href)
    <a href="{{ $href }}"
       {{ $attributes->merge(['class' => $classes, 'aria-busy' => $loading ? 'true' : 'false']) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" @if($disabled || $loading) disabled @endif
            {{ $attributes->merge(['class' => $classes, 'aria-busy' => $loading ? 'true' : 'false']) }}>
        {{ $slot }}
    </button>
@endif
