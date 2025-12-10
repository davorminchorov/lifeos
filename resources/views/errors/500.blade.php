@extends('layouts.app')

@section('title', 'Server Error - LifeOS')

@section('content')
<div class="min-h-screen flex items-center justify-center px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 text-center">
        <div>
            <!-- LifeOS Logo/Brand -->
            <h1 class="text-6xl font-bold text-[color:var(--color-danger-500)] mb-4">500</h1>
            <h2 class="text-3xl font-semibold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-4">
                Server Error
            </h2>
            <p class="text-lg text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] mb-8">
                Oops! Something went wrong on our end. We're working to fix this issue. Please try again in a few minutes.
            </p>
        </div>

        <!-- Error Actions -->
        <div class="space-y-4">
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <x-button variant="primary" onclick="window.location.reload()">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Try Again
                </x-button>

                <x-button href="{{ url()->previous() }}" variant="secondary">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Go Back
                </x-button>
            </div>

            <div class="mt-8">
                @auth
                    <a href="{{ route('dashboard') }}"
                       class="text-sm text-[color:var(--color-primary-500)] hover:text-[color:var(--color-accent-500)] dark:text-[color:var(--color-dark-500)] dark:hover:text-[color:var(--color-accent-500)] transition-colors duration-200">
                        Return to Dashboard
                    </a>
                @else
                    <a href="{{ url('/') }}"
                       class="text-sm text-[color:var(--color-primary-500)] hover:text-[color:var(--color-accent-500)] dark:text-[color:var(--color-dark-500)] dark:hover:text-[color:var(--color-accent-500)] transition-colors duration-200">
                        Return to Homepage
                    </a>
                @endauth
            </div>
        </div>

        <!-- Technical Details (only in debug mode) -->
        @if(config('app.debug') && isset($exception))
        <div class="mt-8 pt-8 border-t border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
            <details class="text-left">
                <summary class="cursor-pointer text-sm font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] hover:text-[color:var(--color-accent-500)] transition-colors duration-200">
                    Technical Details
                </summary>
                <div class="mt-4 p-4 bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-100)] rounded-lg text-xs text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] font-mono">
                    <p><strong>Error:</strong> {{ get_class($exception) }}</p>
                    <p><strong>Message:</strong> {{ $exception->getMessage() }}</p>
                    <p><strong>File:</strong> {{ $exception->getFile() }}:{{ $exception->getLine() }}</p>
                </div>
            </details>
        </div>
        @endif

        <!-- Additional Help -->
        <div class="pt-8 border-t border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
            <p class="text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">
                If this problem persists, try refreshing the page or
                <a href="{{ url('/') }}" class="text-[color:var(--color-accent-500)] hover:text-[color:var(--color-accent-600)] transition-colors duration-200">
                    return to the homepage
                </a>
            </p>
        </div>
    </div>
</div>
@endsection
