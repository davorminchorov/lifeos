@extends('layouts.app')

@section('title', 'Page Not Found - LifeOS')

@section('content')
<div class="min-h-screen flex items-center justify-center px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 text-center">
        <div>
            <!-- LifeOS Logo/Brand -->
            <h1 class="text-6xl font-bold text-[color:var(--color-accent-500)] mb-4">404</h1>
            <h2 class="text-3xl font-semibold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-4">
                Page Not Found
            </h2>
            <p class="text-lg text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] mb-8">
                Sorry, we couldn't find the page you're looking for. It might have been moved, deleted, or you may have entered the wrong URL.
            </p>
        </div>

        <!-- Error Actions -->
        <div class="space-y-4">
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                @auth
                    <a href="{{ route('dashboard') }}"
                       class="w-full sm:w-auto inline-flex justify-center items-center bg-[color:var(--color-accent-500)] hover:bg-[color:var(--color-accent-600)] text-white px-6 py-3 sm:px-4 sm:py-2 rounded-lg sm:rounded-md text-base sm:text-sm font-medium transition-colors duration-200 touch-manipulation">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v2H8V5z"/>
                        </svg>
                        Go to Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}"
                       class="w-full sm:w-auto inline-flex justify-center items-center bg-[color:var(--color-accent-500)] hover:bg-[color:var(--color-accent-600)] text-white px-6 py-3 sm:px-4 sm:py-2 rounded-lg sm:rounded-md text-base sm:text-sm font-medium transition-colors duration-200 touch-manipulation">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                        </svg>
                        Sign In
                    </a>
                @endauth

                <a href="{{ url()->previous() }}"
                   class="w-full sm:w-auto inline-flex justify-center items-center bg-[color:var(--color-primary-200)] hover:bg-[color:var(--color-primary-300)] text-[color:var(--color-primary-700)] dark:bg-[color:var(--color-dark-200)] dark:hover:bg-[color:var(--color-dark-300)] dark:text-[color:var(--color-dark-600)] px-6 py-3 sm:px-4 sm:py-2 rounded-lg sm:rounded-md text-base sm:text-sm font-medium transition-colors duration-200 touch-manipulation">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Go Back
                </a>
            </div>

            <div class="mt-8">
                <a href="{{ url('/') }}"
                   class="text-sm text-[color:var(--color-primary-500)] hover:text-[color:var(--color-accent-500)] dark:text-[color:var(--color-dark-500)] dark:hover:text-[color:var(--color-accent-500)] transition-colors duration-200">
                    Return to Homepage
                </a>
            </div>
        </div>

        <!-- Additional Help -->
        <div class="pt-8 border-t border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
            <p class="text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">
                Need help organizing your life?
                <a href="{{ url('/') }}" class="text-[color:var(--color-accent-500)] hover:text-[color:var(--color-accent-600)] transition-colors duration-200">
                    Explore LifeOS features
                </a>
            </p>
        </div>
    </div>
</div>
@endsection
