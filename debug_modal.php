<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug Modal Test</title>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body>
    <div class="p-4">
        <h1>Modal Debug Test</h1>

        <!-- Test Button -->
        <button type="button"
                class="bg-blue-500 text-white px-4 py-2 rounded cursor-pointer"
                x-on:click="$dispatch('open-modal', { id: 'testModal-123' })">
            Test Modal Button
        </button>

        <!-- Test Modal -->
        <div x-data="{ open: false }" x-on:open-modal.window="console.log('Event received:', $event.detail); if ($event.detail.id === 'testModal-123') open = true">
            <!-- Modal Background -->
            <div x-show="open"
                 class="fixed inset-0 bg-gray-600 bg-opacity-50 z-50"
                 x-on:click="open = false"
                 style="display: none;">
            </div>

            <!-- Modal Content -->
            <div x-show="open"
                 class="fixed inset-0 z-50 flex items-center justify-center p-4"
                 style="display: none;">
                <div class="bg-white rounded-lg p-6 max-w-sm mx-auto" x-on:click.stop>
                    <h3 class="text-lg font-bold mb-4">Test Modal</h3>
                    <p class="mb-4">This is a test modal to debug the Alpine.js event system.</p>
                    <button type="button"
                            class="bg-gray-500 text-white px-4 py-2 rounded"
                            x-on:click="open = false">
                        Close
                    </button>
                </div>
            </div>
        </div>

        <!-- Alpine.js Debug Info -->
        <div x-data="{ version: '3.x' }" class="mt-4 p-4 bg-gray-100">
            <h2>Alpine.js Debug Info</h2>
            <p>Alpine.js loaded: <span x-text="typeof Alpine !== 'undefined' ? 'Yes' : 'No'"></span></p>
            <button type="button"
                    class="mt-2 bg-green-500 text-white px-2 py-1 rounded text-sm"
                    x-on:click="console.log('Alpine.js is working')">
                Test Alpine.js
            </button>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded');
            console.log('Alpine available:', typeof Alpine !== 'undefined');
        });
    </script>
</body>
</html>
