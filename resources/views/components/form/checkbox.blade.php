@props([
    'name',
    'label',
    'value' => '1',
    'checked' => false,
    'helpText' => null,
])

<div class="flex items-start">
    <div class="flex items-center h-5">
        <input
            type="checkbox"
            name="{{ $name }}"
            id="{{ $name }}"
            value="{{ $value }}"
            {{ old($name, $checked) ? 'checked' : '' }}
            {{ $attributes->merge(['class' => 'h-4 w-4 text-[color:var(--color-accent-600)] focus:ring-[color:var(--color-accent-500)] border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded']) }}
        >
    </div>
    <div class="ml-3 text-sm">
        <label for="{{ $name }}" class="font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
            {{ $label }}
        </label>
        @if($helpText)
            <p class="text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">{{ $helpText }}</p>
        @endif
        @error($name)
            <p class="mt-1 text-sm text-[color:var(--color-danger-600)] dark:text-[color:var(--color-danger-500)]">{{ $message }}</p>
        @enderror
    </div>
</div>
