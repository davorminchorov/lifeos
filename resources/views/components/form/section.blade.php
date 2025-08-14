@props(['title', 'description'])

<div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow rounded-lg mb-6 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
    <div class="px-4 py-5 sm:px-6">
        <h3 class="text-lg leading-6 font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">{{ $title }}</h3>
        @if($description)
            <p class="mt-1 text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">{{ $description }}</p>
        @endif
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 px-4 py-5 sm:px-6">
        {{ $slot }}
    </div>
</div>
