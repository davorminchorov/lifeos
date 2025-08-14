@extends('layouts.app')

@section('title', 'Login - LifeOS')

@section('content')
<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div>
            <div class="mx-auto h-12 w-12 flex items-center justify-center rounded-full bg-[color:var(--color-accent-100)] dark:bg-[color:var(--color-accent-900)]">
                <svg class="h-6 w-6 text-[color:var(--color-accent-600)] dark:text-[color:var(--color-accent-400)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
            <h2 class="mt-6 text-center text-3xl font-bold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                Sign in to LifeOS
            </h2>
            <p class="mt-2 text-center text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">
                Welcome back to your personal life management platform
            </p>
        </div>

        <form class="mt-8 space-y-6" action="{{ route('login') }}" method="POST">
            @csrf
            <div class="space-y-4">
                <div>
                    <label for="email" class="block text-sm font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-600)]">
                        Email address
                    </label>
                    <input
                        id="email"
                        name="email"
                        type="email"
                        autocomplete="email"
                        required
                        value="{{ old('email') }}"
                        class="mt-1 appearance-none relative block w-full px-3 py-2 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] placeholder-[color:var(--color-primary-400)] dark:placeholder-[color:var(--color-dark-400)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] rounded-md focus:outline-none focus:ring-[color:var(--color-accent-500)] focus:border-[color:var(--color-accent-500)] focus:z-10 sm:text-sm transition-colors duration-200"
                        placeholder="Enter your email"
                    >
                    @error('email')
                        <p class="mt-1 text-sm text-[color:var(--color-danger-600)]">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-600)]">
                        Password
                    </label>
                    <input
                        id="password"
                        name="password"
                        type="password"
                        autocomplete="current-password"
                        required
                        class="mt-1 appearance-none relative block w-full px-3 py-2 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] placeholder-[color:var(--color-primary-400)] dark:placeholder-[color:var(--color-dark-400)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] rounded-md focus:outline-none focus:ring-[color:var(--color-accent-500)] focus:border-[color:var(--color-accent-500)] focus:z-10 sm:text-sm transition-colors duration-200"
                        placeholder="Enter your password"
                    >
                    @error('password')
                        <p class="mt-1 text-sm text-[color:var(--color-danger-600)]">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input
                        id="remember"
                        name="remember"
                        type="checkbox"
                        value="1"
                        class="h-4 w-4 text-[color:var(--color-accent-600)] focus:ring-[color:var(--color-accent-500)] border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded"
                    >
                    <label for="remember" class="ml-2 block text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">
                        Remember me
                    </label>
                </div>

                <div class="text-sm">
                    <a href="#" class="font-medium text-[color:var(--color-accent-600)] hover:text-[color:var(--color-accent-500)] transition-colors duration-200">
                        Forgot your password?
                    </a>
                </div>
            </div>

            <div>
                <button
                    type="submit"
                    class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-[color:var(--color-accent-600)] hover:bg-[color:var(--color-accent-700)] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[color:var(--color-accent-500)] transition-colors duration-200"
                >
                    <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                        <svg class="h-5 w-5 text-[color:var(--color-accent-500)] group-hover:text-[color:var(--color-accent-400)]" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                        </svg>
                    </span>
                    Sign in
                </button>
            </div>

            <div class="text-center">
                <p class="text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">
                    Don't have an account?
                    <a href="{{ route('register') }}" class="font-medium text-[color:var(--color-accent-600)] hover:text-[color:var(--color-accent-500)] transition-colors duration-200">
                        Create one now
                    </a>
                </p>
            </div>
        </form>
    </div>
</div>
@endsection
