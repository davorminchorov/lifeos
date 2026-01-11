@props([
    'id' => 'modal',
    'title' => 'Modal',
    'maxWidth' => 'lg'
])

@php
$maxWidthClass = match ($maxWidth) {
    'sm' => 'sm:max-w-sm',
    'md' => 'sm:max-w-md',
    'lg' => 'sm:max-w-lg',
    'xl' => 'sm:max-w-xl',
    '2xl' => 'sm:max-w-2xl',
    default => 'sm:max-w-lg'
};
@endphp

<div x-data="{ open: false }"
     x-on:open-modal.window="if ($event.detail.id === '{{ $id }}') open = true"
     x-on:close-modal.window="if ($event.detail.id === '{{ $id }}') open = false">
    <!-- Modal Background -->
    <div x-show="open"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-[color:var(--color-primary-600)] bg-opacity-50 overflow-y-auto h-full w-full z-50"
         x-on:click="open = false">
    </div>

    <!-- Modal Content -->
    <div x-show="open"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
         class="fixed inset-0 z-50 overflow-y-auto">

        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Modal panel -->
            <div class="relative inline-block align-bottom bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle {{ $maxWidthClass }} sm:w-full border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]"
                 x-on:click.stop>

                <!-- Modal Header -->
                <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] px-6 py-4 border-b border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg leading-6 font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                            {{ $title }}
                        </h3>
                        <button type="button"
                                class="text-[color:var(--color-primary-400)] dark:text-[color:var(--color-dark-500)] hover:text-[color:var(--color-primary-600)] dark:hover:text-[color:var(--color-dark-600)] transition-colors duration-200"
                                x-on:click="open = false">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Modal Body -->
                <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] px-6 py-4">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </div>
</div>
