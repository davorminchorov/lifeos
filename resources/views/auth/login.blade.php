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
                <x-form.input
                    name="email"
                    label="Email address"
                    type="email"
                    placeholder="Enter your email"
                    :required="true"
                    inputClass="appearance-none relative focus:z-10"
                />

                <x-form.input
                    name="password"
                    label="Password"
                    type="password"
                    placeholder="Enter your password"
                    :required="true"
                    inputClass="appearance-none relative focus:z-10"
                />
            </div>

            <div class="flex items-center justify-between">
                <x-form.checkbox
                    name="remember"
                    label="Remember me"
                />

                <div class="text-sm">
                    <a href="#" class="font-medium text-[color:var(--color-accent-600)] hover:text-[color:var(--color-accent-500)] transition-colors duration-200">
                        Forgot your password?
                    </a>
                </div>
            </div>

            <div>
                <x-button type="submit" variant="primary" class="w-full group relative">
                    <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                        <svg class="h-5 w-5 text-[color:var(--color-accent-500)] group-hover:text-[color:var(--color-accent-400)]" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                        </svg>
                    </span>
                    Sign in
                </x-button>
            </div>

        </form>
    </div>
</div>
@endsection
