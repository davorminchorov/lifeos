@props([
    'name',
    'label',
    'required' => false,
    'placeholder' => '',
    'helpText' => '',
    'onchange' => '',
    // Optional extras for more flexible usage
    'id' => null,
    'selectClass' => '',
    'containerClass' => ''
])

@php($fieldId = $id ?: $name)

<div class="{{ $containerClass }}">
    <label for="{{ $fieldId }}" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
        {{ $label }}
        @if($required)
            <span class="text-[color:var(--color-danger-500)]">*</span>
        @endif
    </label>

    <div class="mt-1">
        <select
            name="{{ $name }}"
            id="{{ $fieldId }}"
            @if($onchange) onchange="{{ $onchange }}" @endif
            class="block w-full px-3 py-2 rounded-md border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] shadow-sm focus:border-[color:var(--color-accent-500)] focus:ring-[color:var(--color-accent-500)] {{ $selectClass }}"
            @if($required) required @endif
        >
            @if($placeholder)
                <option value="">{{ $placeholder }}</option>
            @endif
            {{ $slot }}
        </select>
    </div>

    @if($helpText)
        <p class="mt-2 text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">{{ $helpText }}</p>
    @endif

    @error($name)
        <p class="mt-2 text-sm text-[color:var(--color-danger-600)]">{{ $message }}</p>
    @enderror
</div>
