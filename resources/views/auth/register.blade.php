@extends('layouts.app')

@section('title', 'Register - LifeOS')

@section('content')
<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div>
            <div class="mx-auto h-12 w-12 flex items-center justify-center rounded-full bg-[color:var(--color-accent-100)] dark:bg-[color:var(--color-accent-900)]">
                <svg class="h-6 w-6 text-[color:var(--color-accent-600)] dark:text-[color:var(--color-accent-400)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                </svg>
            </div>
            <h2 class="mt-6 text-center text-3xl font-bold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                Create your LifeOS account
            </h2>
            <p class="mt-2 text-center text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">
                Start managing your personal life more efficiently
            </p>
        </div>

        <form class="mt-8 space-y-6" action="{{ route('register') }}" method="POST">
            @csrf
            <div class="space-y-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-600)]">
                        Full name
                    </label>
                    <input
                        id="name"
                        name="name"
                        type="text"
                        autocomplete="name"
                        required
                        value="{{ old('name') }}"
                        class="mt-1 appearance-none relative block w-full px-3 py-2 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] placeholder-[color:var(--color-primary-400)] dark:placeholder-[color:var(--color-dark-400)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] rounded-md focus:outline-none focus:ring-[color:var(--color-accent-500)] focus:border-[color:var(--color-accent-500)] focus:z-10 sm:text-sm transition-colors duration-200"
                        placeholder="Enter your full name"
                    >
                    @error('name')
                        <p class="mt-1 text-sm text-[color:var(--color-danger-600)]">{{ $message }}</p>
                    @enderror
                </div>

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
                        autocomplete="new-password"
                        required
                        class="mt-1 appearance-none relative block w-full px-3 py-2 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] placeholder-[color:var(--color-primary-400)] dark:placeholder-[color:var(--color-dark-400)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] rounded-md focus:outline-none focus:ring-[color:var(--color-accent-500)] focus:border-[color:var(--color-accent-500)] focus:z-10 sm:text-sm transition-colors duration-200"
                        placeholder="Create a password"
                    >
                    @error('password')
                        <p class="mt-1 text-sm text-[color:var(--color-danger-600)]">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-600)]">
                        Confirm password
                    </label>
                    <input
                        id="password_confirmation"
                        name="password_confirmation"
                        type="password"
                        autocomplete="new-password"
                        required
                        class="mt-1 appearance-none relative block w-full px-3 py-2 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] placeholder-[color:var(--color-primary-400)] dark:placeholder-[color:var(--color-dark-400)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] rounded-md focus:outline-none focus:ring-[color:var(--color-accent-500)] focus:border-[color:var(--color-accent-500)] focus:z-10 sm:text-sm transition-colors duration-200"
                        placeholder="Confirm your password"
                    >
                    @error('password_confirmation')
                        <p class="mt-1 text-sm text-[color:var(--color-danger-600)]">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <div class="flex items-center">
                    <input
                        id="terms"
                        name="terms"
                        type="checkbox"
                        value="1"
                        required
                        class="h-4 w-4 text-[color:var(--color-accent-600)] focus:ring-[color:var(--color-accent-500)] border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded"
                    >
                    <label for="terms" class="ml-2 block text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">
                        I agree to the
                        <a href="#" class="font-medium text-[color:var(--color-accent-600)] hover:text-[color:var(--color-accent-500)] transition-colors duration-200">
                            Terms of Service
                        </a>
                        and
                        <a href="#" class="font-medium text-[color:var(--color-accent-600)] hover:text-[color:var(--color-accent-500)] transition-colors duration-200">
                            Privacy Policy
                        </a>
                    </label>
                </div>
                @error('terms')
                    <p class="mt-1 text-sm text-[color:var(--color-danger-600)]">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <button
                    type="submit"
                    class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-[color:var(--color-accent-600)] hover:bg-[color:var(--color-accent-700)] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[color:var(--color-accent-500)] transition-colors duration-200"
                >
                    <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                        <svg class="h-5 w-5 text-[color:var(--color-accent-500)] group-hover:text-[color:var(--color-accent-400)]" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M8 9a3 3 0 100-6 3 3 0 000 6zM8 11a6 6 0 016 6H2a6 6 0 016-6zM16 7a1 1 0 10-2 0v1h-1a1 1 0 100 2h1v1a1 1 0 102 0v-1h1a1 1 0 100-2h-1V7z"/>
                        </svg>
                    </span>
                    Create Account
                </button>
            </div>

            <div class="text-center">
                <p class="text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">
                    Already have an account?
                    <a href="{{ route('login') }}" class="font-medium text-[color:var(--color-accent-600)] hover:text-[color:var(--color-accent-500)] transition-colors duration-200">
                        Sign in here
                    </a>
                </p>
            </div>
        </form>
    </div>
</div>
@endsection
