@extends('layouts.app')

@section('title', 'Gmail Receipt Integration - LifeOS')

@section('header')
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                Gmail Receipt Integration
            </h1>
            <p class="mt-2 text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">
                Automatically import expenses from Gmail receipts
            </p>
        </div>
        <a href="{{ route('settings.index') }}" class="inline-flex items-center px-4 py-2 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded-md shadow-sm text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] hover:bg-[color:var(--color-primary-200)] dark:hover:bg-[color:var(--color-dark-300)]">
            Back to Settings
        </a>
    </div>
@endsection

@section('content')
    <div class="space-y-6">

        <!-- Success/Error Messages -->
        @if(session('success'))
            <div class="rounded-md bg-[color:var(--color-success-50)] dark:bg-[color:var(--color-success-900)] p-4 border border-[color:var(--color-success-300)] dark:border-[color:var(--color-success-700)]">
                <div class="flex">
                    <svg class="h-5 w-5 text-[color:var(--color-success-400)]" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <p class="ml-3 text-sm text-[color:var(--color-success-800)] dark:text-[color:var(--color-success-100)]">
                        {{ session('success') }}
                    </p>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="rounded-md bg-[color:var(--color-error-50)] dark:bg-[color:var(--color-error-900)] p-4 border border-[color:var(--color-error-300)] dark:border-[color:var(--color-error-700)]">
                <div class="flex">
                    <svg class="h-5 w-5 text-[color:var(--color-error-400)]" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <p class="ml-3 text-sm text-[color:var(--color-error-800)] dark:text-[color:var(--color-error-100)]">
                        {{ session('error') }}
                    </p>
                </div>
            </div>
        @endif

        <!-- Connection Status Card -->
        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow overflow-hidden sm:rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-[color:var(--color-accent-500)] dark:bg-[color:var(--color-accent-600)] rounded-full flex items-center justify-center mr-4">
                            <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/>
                                <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                Gmail Connection Status
                            </h3>
                            @if($connection)
                                <p class="text-sm text-[color:var(--color-success-600)] dark:text-[color:var(--color-success-400)] font-semibold">
                                    Connected: {{ $connection->email_address }}
                                </p>
                            @else
                                <p class="text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">
                                    Not connected
                                </p>
                            @endif
                        </div>
                    </div>
                    <div>
                        @if($connection)
                            <form action="{{ route('settings.gmail-receipts.disconnect') }}" method="POST" onsubmit="return confirm('Are you sure you want to disconnect Gmail? You can reconnect anytime.')">
                                @csrf
                                <button type="submit" class="inline-flex items-center px-4 py-2 border border-[color:var(--color-error-300)] rounded-md shadow-sm text-sm font-medium text-white bg-[color:var(--color-error-600)] hover:bg-[color:var(--color-error-700)]">
                                    Disconnect
                                </button>
                            </form>
                        @else
                            <form action="{{ route('settings.gmail-receipts.connect') }}" method="POST">
                                @csrf
                                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-[color:var(--color-accent-600)] hover:bg-[color:var(--color-accent-700)]">
                                    Connect Gmail
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

                @if($connection)
                    <!-- Stats -->
                    <div class="mt-6 grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-300)] p-4 rounded-lg">
                            <p class="text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">Total Processed</p>
                            <p class="text-2xl font-bold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">{{ $stats['total_processed'] ?? 0 }}</p>
                        </div>
                        <div class="bg-[color:var(--color-warning-50)] dark:bg-[color:var(--color-warning-900)] p-4 rounded-lg">
                            <p class="text-sm text-[color:var(--color-warning-600)] dark:text-[color:var(--color-warning-400)]">Pending</p>
                            <p class="text-2xl font-bold text-[color:var(--color-warning-700)] dark:text-[color:var(--color-warning-300)]">{{ $stats['pending'] ?? 0 }}</p>
                        </div>
                        <div class="bg-[color:var(--color-error-50)] dark:bg-[color:var(--color-error-900)] p-4 rounded-lg">
                            <p class="text-sm text-[color:var(--color-error-600)] dark:text-[color:var(--color-error-400)]">Failed</p>
                            <p class="text-2xl font-bold text-[color:var(--color-error-700)] dark:text-[color:var(--color-error-300)]">{{ $stats['failed'] ?? 0 }}</p>
                        </div>
                        <div class="bg-[color:var(--color-info-50)] dark:bg-[color:var(--color-info-900)] p-4 rounded-lg">
                            <p class="text-sm text-[color:var(--color-info-600)] dark:text-[color:var(--color-info-400)]">Last Synced</p>
                            <p class="text-sm font-semibold text-[color:var(--color-info-700)] dark:text-[color:var(--color-info-300)]">{{ $stats['last_synced'] ?? 'Never' }}</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        @if($connection)
            <!-- Settings Card -->
            <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow overflow-hidden sm:rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-4">
                        Sync Settings
                    </h3>

                    <div class="space-y-4">
                        <!-- Auto-sync Toggle -->
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                    Automatic Sync
                                </p>
                                <p class="text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">
                                    Automatically sync receipts every hour
                                </p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" class="sr-only peer" id="autoSyncToggle" {{ $connection->sync_enabled ? 'checked' : '' }} onchange="toggleAutoSync(this)">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-[color:var(--color-accent-600)]"></div>
                            </label>
                        </div>

                        <!-- Manual Sync Button -->
                        <div class="pt-4 border-t border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
                            <form action="{{ route('settings.gmail-receipts.sync') }}" method="POST">
                                @csrf
                                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-[color:var(--color-accent-600)] hover:bg-[color:var(--color-accent-700)]">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                    </svg>
                                    Sync Now
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- How It Works -->
            <div class="bg-[color:var(--color-info-50)] dark:bg-[color:var(--color-info-900)] shadow overflow-hidden sm:rounded-lg border border-[color:var(--color-info-300)] dark:border-[color:var(--color-info-700)]">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg font-medium text-[color:var(--color-info-700)] dark:text-[color:var(--color-info-300)] mb-4">
                        How It Works
                    </h3>
                    <ul class="space-y-2 text-sm text-[color:var(--color-info-700)] dark:text-[color:var(--color-info-200)]">
                        <li class="flex items-start">
                            <svg class="w-5 h-5 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span>LifeOS scans your Gmail for receipt emails from common merchants</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span>Automatically extracts amount, merchant, date, and category</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span>Downloads and attaches receipt PDFs and images</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span>Creates expenses with "pending" status for your review</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span>Review and confirm expenses in the Expenses section</span>
                        </li>
                    </ul>
                </div>
            </div>
        @endif

    </div>

    @if($connection)
        <script>
            function toggleAutoSync(checkbox) {
                fetch('{{ route('settings.gmail-receipts.toggle-auto-sync') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        sync_enabled: checkbox.checked
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (!data.success) {
                        checkbox.checked = !checkbox.checked;
                        alert('Failed to update auto-sync setting');
                    }
                })
                .catch(error => {
                    checkbox.checked = !checkbox.checked;
                    alert('Failed to update auto-sync setting');
                });
            }
        </script>
    @endif
@endsection
