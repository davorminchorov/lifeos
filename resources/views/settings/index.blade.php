@extends('layouts.app')

@section('title', 'Settings - LifeOS')

@section('header')
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                Settings
            </h1>
            <p class="mt-2 text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">
                Manage your account, application preferences, and notifications
            </p>
        </div>
    </div>
@endsection

@section('content')
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

        <!-- Account Settings -->
        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow overflow-hidden sm:rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] hover:shadow-lg transition-shadow duration-200">
            <a href="{{ route('settings.account') }}" class="block">
                <div class="px-4 py-5 sm:p-6">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center mr-4">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                Account Settings
                            </h3>
                            <p class="text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">
                                Update your personal information, email, and password
                            </p>
                        </div>
                    </div>
                    <div class="mt-4 flex justify-end">
                        <svg class="w-5 h-5 text-[color:var(--color-primary-400)] dark:text-[color:var(--color-dark-400)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </div>
            </a>
        </div>

        <!-- Application Settings -->
        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow overflow-hidden sm:rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] hover:shadow-lg transition-shadow duration-200">
            <a href="{{ route('settings.application') }}" class="block">
                <div class="px-4 py-5 sm:p-6">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center mr-4">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                Application Settings
                            </h3>
                            <p class="text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">
                                Customize theme, language, and display preferences
                            </p>
                        </div>
                    </div>
                    <div class="mt-4 flex justify-end">
                        <svg class="w-5 h-5 text-[color:var(--color-primary-400)] dark:text-[color:var(--color-dark-400)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </div>
            </a>
        </div>

        <!-- Notification Settings -->
        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow overflow-hidden sm:rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] hover:shadow-lg transition-shadow duration-200">
            <a href="{{ route('notifications.preferences') }}" class="block">
                <div class="px-4 py-5 sm:p-6">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-yellow-500 rounded-full flex items-center justify-center mr-4">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM4.868 19.438A8.956 8.956 0 003 15a9 9 0 1115.86 5.438l-2.68-1.34A6 6 0 115 15a5.956 5.956 0 001.232 3.656l-1.364.782z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                Notification Preferences
                            </h3>
                            <p class="text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">
                                Configure how you receive alerts and reminders
                            </p>
                        </div>
                    </div>
                    <div class="mt-4 flex justify-end">
                        <svg class="w-5 h-5 text-[color:var(--color-primary-400)] dark:text-[color:var(--color-dark-400)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </div>
            </a>
        </div>

        <!-- Privacy & Security -->
        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow overflow-hidden sm:rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] hover:shadow-lg transition-shadow duration-200 opacity-50">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-red-500 rounded-full flex items-center justify-center mr-4">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                            Privacy & Security
                        </h3>
                        <p class="text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">
                            Manage data privacy and security settings
                        </p>
                        <p class="text-xs text-[color:var(--color-primary-400)] dark:text-[color:var(--color-dark-400)] mt-1">
                            Coming Soon
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Management -->
        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow overflow-hidden sm:rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] hover:shadow-lg transition-shadow duration-200 opacity-50">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-purple-500 rounded-full flex items-center justify-center mr-4">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                            Data Management
                        </h3>
                        <p class="text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">
                            Export, import, and backup your data
                        </p>
                        <p class="text-xs text-[color:var(--color-primary-400)] dark:text-[color:var(--color-dark-400)] mt-1">
                            Coming Soon
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- API & Integrations -->
        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow overflow-hidden sm:rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] hover:shadow-lg transition-shadow duration-200 opacity-50">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-indigo-500 rounded-full flex items-center justify-center mr-4">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                            API & Integrations
                        </h3>
                        <p class="text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">
                            Manage API keys and third-party connections
                        </p>
                        <p class="text-xs text-[color:var(--color-primary-400)] dark:text-[color:var(--color-dark-400)] mt-1">
                            Coming Soon
                        </p>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Quick Settings Summary -->
    <div class="mt-8 bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow overflow-hidden sm:rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-4">
                Quick Actions
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <a href="{{ route('profile.edit') }}" class="bg-[color:var(--color-accent-500)] hover:bg-[color:var(--color-accent-600)] text-white px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200 text-center">
                    Edit Profile
                </a>
                <a href="{{ route('notifications.preferences') }}" class="bg-[color:var(--color-primary-300)] hover:bg-[color:var(--color-primary-400)] text-[color:var(--color-primary-700)] dark:bg-[color:var(--color-dark-300)] dark:hover:bg-[color:var(--color-dark-400)] dark:text-[color:var(--color-dark-600)] px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200 text-center">
                    Notifications
                </a>
                <button onclick="document.getElementById('theme-toggle').click()" class="bg-[color:var(--color-primary-300)] hover:bg-[color:var(--color-primary-400)] text-[color:var(--color-primary-700)] dark:bg-[color:var(--color-dark-300)] dark:hover:bg-[color:var(--color-dark-400)] dark:text-[color:var(--color-dark-600)] px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200 text-center">
                    Toggle Theme
                </button>
                <a href="{{ route('dashboard') }}" class="bg-[color:var(--color-primary-300)] hover:bg-[color:var(--color-primary-400)] text-[color:var(--color-primary-700)] dark:bg-[color:var(--color-dark-300)] dark:hover:bg-[color:var(--color-dark-400)] dark:text-[color:var(--color-dark-600)] px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200 text-center">
                    Back to Dashboard
                </a>
            </div>
        </div>
    </div>

@endsection
