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
            {{ $attributes->merge(['class' => 'h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 dark:border-gray-700 rounded']) }}
        >
    </div>
    <div class="ml-3 text-sm">
        <label for="{{ $name }}" class="font-medium text-gray-700 dark:text-gray-300">
            {{ $label }}
        </label>
        @if($helpText)
            <p class="text-gray-500 dark:text-gray-400">{{ $helpText }}</p>
        @endif
        @error($name)
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>
</div>
