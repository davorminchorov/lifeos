@extends('layouts.app')

@section('title', 'Account Settings - LifeOS')

@section('header')
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                Account Settings
            </h1>
            <p class="mt-2 text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">
                Manage your personal information and account security
            </p>
        </div>
        <a href="{{ route('settings.index') }}" class="bg-[color:var(--color-primary-200)] hover:bg-[color:var(--color-primary-300)] text-[color:var(--color-primary-700)] dark:bg-[color:var(--color-dark-300)] dark:hover:bg-[color:var(--color-dark-400)] dark:text-[color:var(--color-dark-600)] px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200">
            Back to Settings
        </a>
    </div>
@endsection

@section('content')
    <div class="space-y-6">

        <!-- Personal Information -->
        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow overflow-hidden sm:rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex items-center mb-4">
                    <div class="w-10 h-10 bg-[color:var(--color-info-500)] dark:bg-[color:var(--color-info-600)] rounded-full flex items-center justify-center mr-4">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                            Personal Information
                        </h3>
                        <p class="text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">
                            Update your name and email address
                        </p>
                    </div>
                </div>

                <form method="POST" action="{{ route('profile.update') }}" class="space-y-4">
                    @csrf
                    @method('PATCH')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-form.input
                            name="name"
                            label="Full Name"
                            type="text"
                            :value="auth()->user()->name"
                            :required="true"
                        />

                        <x-form.input
                            name="email"
                            label="Email Address"
                            type="email"
                            :value="auth()->user()->email"
                            :required="true"
                        />
                    </div>

                    <div class="flex justify-end">
                        <x-button type="submit" variant="primary">
                            Update Information
                        </x-button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Password Security -->
        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow overflow-hidden sm:rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex items-center mb-4">
                    <div class="w-10 h-10 bg-[color:var(--color-accent-500)] dark:bg-[color:var(--color-accent-600)] rounded-full flex items-center justify-center mr-4">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                            Password Security
                        </h3>
                        <p class="text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">
                            Change your account password
                        </p>
                    </div>
                </div>

                <form method="POST" action="{{ route('profile.password.update') }}" class="space-y-4">
                    @csrf
                    @method('PATCH')

                    <x-form.input
                        name="current_password"
                        label="Current Password"
                        type="password"
                        :required="true"
                        containerClass="md:w-1/2"
                    />

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-form.input
                            name="password"
                            label="New Password"
                            type="password"
                            :required="true"
                        />

                        <x-form.input
                            name="password_confirmation"
                            label="Confirm New Password"
                            type="password"
                            :required="true"
                        />
                    </div>

                    <div class="bg-[color:var(--color-warning-50)] dark:bg-[color:var(--color-warning-500)]/20 border border-[color:var(--color-warning-500)]/30 dark:border-[color:var(--color-warning-600)] rounded-md p-4">
                        <div class="flex">
                            <svg class="w-5 h-5 text-[color:var(--color-warning-500)] dark:text-[color:var(--color-warning-600)] mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            <div class="text-sm">
                                <p class="text-[color:var(--color-warning-600)] dark:text-[color:var(--color-warning-500)] font-medium">Password Requirements:</p>
                                <ul class="mt-1 text-[color:var(--color-warning-600)] dark:text-[color:var(--color-warning-500)] list-disc list-inside">
                                    <li>At least 8 characters long</li>
                                    <li>Include uppercase and lowercase letters</li>
                                    <li>Include at least one number</li>
                                    <li>Include at least one special character</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <x-button type="submit" variant="primary">
                            Update Password
                        </x-button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Account Statistics -->
        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow overflow-hidden sm:rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex items-center mb-4">
                    <div class="w-10 h-10 bg-[color:var(--color-success-500)] dark:bg-[color:var(--color-success-600)] rounded-full flex items-center justify-center mr-4">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                            Account Overview
                        </h3>
                        <p class="text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">
                            Your account information and statistics
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="text-center p-4 bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] rounded-lg">
                        <div class="text-2xl font-bold text-[color:var(--color-accent-600)]">{{ auth()->user()->created_at->format('M Y') }}</div>
                        <div class="text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">Member Since</div>
                    </div>
                    <div class="text-center p-4 bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] rounded-lg">
                        <div class="text-2xl font-bold text-[color:var(--color-accent-600)]">{{ auth()->user()->subscriptions()->count() }}</div>
                        <div class="text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">Subscriptions</div>
                    </div>
                    <div class="text-center p-4 bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] rounded-lg">
                        <div class="text-2xl font-bold text-[color:var(--color-accent-600)]">{{ auth()->user()->contracts()->count() }}</div>
                        <div class="text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">Contracts</div>
                    </div>
                    <div class="text-center p-4 bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] rounded-lg">
                        <div class="text-2xl font-bold text-[color:var(--color-accent-600)]">{{ auth()->user()->notifications()->count() }}</div>
                        <div class="text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">Notifications</div>
                    </div>
                </div>

                <div class="mt-4 pt-4 border-t border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
                    <div class="flex items-center justify-between text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">
                        <span>Account Created:</span>
                        <span>{{ auth()->user()->created_at->format('F j, Y \a\t g:i A') }}</span>
                    </div>
                    <div class="flex items-center justify-between text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] mt-2">
                        <span>Last Updated:</span>
                        <span>{{ auth()->user()->updated_at->format('F j, Y \a\t g:i A') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="flex flex-wrap gap-4 justify-center">
            <a href="{{ route('profile.show') }}" class="bg-[color:var(--color-primary-300)] hover:bg-[color:var(--color-primary-400)] text-[color:var(--color-primary-700)] dark:bg-[color:var(--color-dark-300)] dark:hover:bg-[color:var(--color-dark-400)] dark:text-[color:var(--color-dark-600)] px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200">
                View Profile
            </a>
            <a href="{{ route('notifications.preferences') }}" class="bg-[color:var(--color-primary-300)] hover:bg-[color:var(--color-primary-400)] text-[color:var(--color-primary-700)] dark:bg-[color:var(--color-dark-300)] dark:hover:bg-[color:var(--color-dark-400)] dark:text-[color:var(--color-dark-600)] px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200">
                Notification Settings
            </a>
            <a href="{{ route('dashboard') }}" class="bg-[color:var(--color-primary-300)] hover:bg-[color:var(--color-primary-400)] text-[color:var(--color-primary-700)] dark:bg-[color:var(--color-dark-300)] dark:hover:bg-[color:var(--color-dark-400)] dark:text-[color:var(--color-dark-600)] px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200">
                Back to Dashboard
            </a>
        </div>

    </div>
@endsection
