@props([
    'title',
    'description' => null,
    'grid' => true,
    'gridCols' => 'md:grid-cols-2',
])

<div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg">
    <div class="px-4 py-5 sm:px-6">
        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
            {{ $title }}
        </h3>
        @if($description)
            <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
                {{ $description }}
            </p>
        @endif
    </div>
    <div class="border-t border-gray-200 dark:border-gray-700 px-4 py-5 sm:px-6">
        @if($grid)
            <div class="grid grid-cols-1 {{ $gridCols }} gap-6">
                {{ $slot }}
            </div>
        @else
            <div class="space-y-6">
                {{ $slot }}
            </div>
        @endif
    </div>
</div>
