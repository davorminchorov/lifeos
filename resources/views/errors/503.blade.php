@extends('layouts.app')

@section('title', 'Service Unavailable - LifeOS')

@section('content')
<div class="min-h-screen flex items-center justify-center px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 text-center">
        <div>
            <!-- LifeOS Logo/Brand -->
            <h1 class="text-6xl font-bold text-[color:var(--color-warning-500)] mb-4">503</h1>
            <h2 class="text-3xl font-semibold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-4">
                Service Unavailable
            </h2>
            <p class="text-lg text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] mb-8">
                LifeOS is temporarily down for maintenance. We're making improvements to better serve you. Please check back in a few minutes.
            </p>
        </div>

        <!-- Maintenance Info -->
        <div class="space-y-6">
            <div class="bg-[color:var(--color-warning-50)] dark:bg-[color:var(--color-dark-100)] border border-[color:var(--color-warning-200)] dark:border-[color:var(--color-dark-300)] rounded-lg p-6">
                <div class="flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-[color:var(--color-warning-500)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-2">
                    We're upgrading LifeOS
                </h3>
                <p class="text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">
                    Our team is working hard to improve your life management experience. This maintenance will help us serve you better.
                </p>
            </div>

            <!-- What's being improved -->
            <div class="text-left">
                <h4 class="text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-3">
                    What we're improving:
                </h4>
                <ul class="text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] space-y-2">
                    <li class="flex items-center">
                        <svg class="w-4 h-4 text-[color:var(--color-success-500)] mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Enhanced subscription tracking features
                    </li>
                    <li class="flex items-center">
                        <svg class="w-4 h-4 text-[color:var(--color-success-500)] mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Better expense management tools
                    </li>
                    <li class="flex items-center">
                        <svg class="w-4 h-4 text-[color:var(--color-success-500)] mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Improved performance and reliability
                    </li>
                </ul>
            </div>

            <!-- Actions -->
            <div class="space-y-4">
                <x-button variant="primary" onclick="window.location.reload()">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Check Again
                </x-button>
            </div>
        </div>

        <!-- Estimated time (if available) -->
        @if(isset($retryAfter))
        <div class="pt-6 border-t border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
            <p class="text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">
                <strong>Estimated completion:</strong> {{ $retryAfter }} minutes
            </p>
        </div>
        @endif

        <!-- Additional Help -->
        <div class="pt-8 border-t border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
            <p class="text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">
                Thank you for your patience. LifeOS will be back soon to help you organize your life more effectively.
            </p>
        </div>
    </div>
</div>
@endsection
