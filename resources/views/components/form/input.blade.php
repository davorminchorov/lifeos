@props([
    'name',
    'label',
    'type' => 'text',
    'required' => false,
    'placeholder' => '',
    'helpText' => '',
    'prefix' => '',
    'rows' => 3,
    'min' => '',
    'max' => '',
    'step' => '',
    'onchange' => '',
    // Optional extras for more flexible usage
    'id' => null,
    'value' => null,
    'inputClass' => '',
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

    <div class="mt-1 {{ $prefix ? 'relative' : '' }}">
        @if($prefix)
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <span class="text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] sm:text-sm">{{ $prefix }}</span>
            </div>
        @endif

        @if($type === 'textarea')
            <textarea
                name="{{ $name }}"
                id="{{ $fieldId }}"
                rows="{{ $rows }}"
                placeholder="{{ $placeholder }}"
                class="block w-full rounded-md border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] shadow-sm focus:border-[color:var(--color-accent-500)] focus:ring-[color:var(--color-accent-500)] {{ $prefix ? 'pl-8' : '' }} {{ $inputClass }}"
                @if($required) required @endif
            >{{ old($name, $value) }}</textarea>
        @else
            <input
                type="{{ $type }}"
                name="{{ $name }}"
                id="{{ $fieldId }}"
                value="{{ old($name, $value) }}"
                placeholder="{{ $placeholder }}"
                @if($min) min="{{ $min }}" @endif
                @if($max) max="{{ $max }}" @endif
                @if($step) step="{{ $step }}" @endif
                @if($onchange) onchange="{{ $onchange }}" @endif
                class="block w-full rounded-md border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] shadow-sm focus:border-[color:var(--color-accent-500)] focus:ring-[color:var(--color-accent-500)] {{ $prefix ? 'pl-8' : '' }} {{ $inputClass }}"
                @if($required) required @endif
            />
        @endif
    </div>

    @if($helpText)
        <p class="mt-2 text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">{{ $helpText }}</p>
    @endif

    @error($name)
        <p class="mt-2 text-sm text-[color:var(--color-danger-600)]">{{ $message }}</p>
    @enderror
</div>
