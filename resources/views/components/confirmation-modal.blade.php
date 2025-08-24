@props([
    'id' => 'confirmationModal',
    'title' => 'Confirm Action',
    'message' => 'Are you sure you want to perform this action?',
    'confirmText' => 'Confirm',
    'cancelText' => 'Cancel',
    'confirmButtonClass' => 'bg-[color:var(--color-danger-500)] hover:bg-[color:var(--color-danger-600)] text-white',
    'action' => null,
    'method' => 'POST'
])

<div x-data="{ open: false }" x-on:open-modal.window="if ($event.detail.id === '{{ $id }}') open = true">
    <!-- Modal Background -->
    <div x-show="open"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
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
            <div class="relative inline-block align-bottom bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]"
                 x-on:click.stop>

                <!-- Modal Header -->
                <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <!-- Warning Icon -->
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-[color:var(--color-danger-50)] dark:bg-[color:var(--color-dark-300)] sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-[color:var(--color-danger-600)] dark:text-[color:var(--color-danger-500)]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                            </svg>
                        </div>

                        <!-- Content -->
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                {{ $title }}
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">
                                    {{ $message }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="bg-[color:var(--color-primary-200)] dark:bg-[color:var(--color-dark-300)] px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    @if($action)
                        <form method="POST" action="{{ $action }}" class="inline">
                            @csrf
                            @if($method !== 'POST')
                                @method($method)
                            @endif
                            <button type="submit"
                                    class="{{ $confirmButtonClass }} ml-3 w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 text-base font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[color:var(--color-danger-500)] sm:ml-3 sm:w-auto sm:text-sm transition-colors duration-200"
                                    x-on:click="open = false">
                                {{ $confirmText }}
                            </button>
                        </form>
                    @endif

                    <button type="button"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-400)] shadow-sm px-4 py-2 bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] text-base font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] hover:bg-[color:var(--color-primary-200)] dark:hover:bg-[color:var(--color-dark-300)] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[color:var(--color-accent-500)] sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-colors duration-200"
                            x-on:click="open = false">
                        {{ $cancelText }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
