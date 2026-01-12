@extends('layouts.app')

@section('title', 'Profile - LifeOS')

@section('header')
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                Profile
            </h1>
            <p class="mt-2 text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">
                Manage your account settings and personal information
            </p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('profile.edit') }}" class="bg-[color:var(--color-accent-600)] hover:bg-[color:var(--color-accent-700)] text-white px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200">
                Edit Profile
            </a>
        </div>
    </div>
@endsection

@section('content')
    @if(session('status'))
        <div class="mb-6 bg-[color:var(--color-success-100)] border border-[color:var(--color-success-300)] text-[color:var(--color-success-700)] px-4 py-3 rounded-md">
            {{ session('status') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Personal Information -->
        <div class="bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                    Personal Information
                </h3>
                <p class="mt-1 max-w-2xl text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">
                    Your basic account information and contact details.
                </p>
            </div>
            <div class="border-t border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)]">
                <dl>
                    <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-400)]">Full Name</dt>
                        <dd class="mt-1 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] sm:mt-0 sm:col-span-2">{{ $user->name }}</dd>
                    </div>
                    <div class="bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-400)]">Email Address</dt>
                        <dd class="mt-1 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] sm:mt-0 sm:col-span-2">{{ $user->email }}</dd>
                    </div>
                    <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-400)]">Member Since</dt>
                        <dd class="mt-1 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] sm:mt-0 sm:col-span-2">{{ $user->created_at->format('F j, Y') }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- Account Security -->
        <div class="bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                    Account Security
                </h3>
                <p class="mt-1 max-w-2xl text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">
                    Security settings and password information.
                </p>
            </div>
            <div class="border-t border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)]">
                <dl>
                    <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-400)]">Password</dt>
                        <dd class="mt-1 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] sm:mt-0 sm:col-span-2">
                            <div class="flex items-center justify-between">
                                <span>••••••••</span>
                                <button onclick="document.getElementById('password-form').style.display = document.getElementById('password-form').style.display === 'none' ? 'block' : 'none'" class="text-[color:var(--color-accent-600)] hover:text-[color:var(--color-accent-700)] text-sm font-medium">
                                    Change Password
                                </button>
                            </div>
                        </dd>
                    </div>
                    <div class="bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-400)]">Last Login</dt>
                        <dd class="mt-1 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] sm:mt-0 sm:col-span-2">
                            {{ $user->updated_at->format('F j, Y \a\t g:i A') }}
                        </dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>

    <!-- Change Password Form (Hidden by default) -->
    <div id="password-form" class="mt-8 bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] shadow overflow-hidden sm:rounded-lg" style="display: none;">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg leading-6 font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                Change Password
            </h3>
            <p class="mt-1 max-w-2xl text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">
                Update your account password for enhanced security.
            </p>
        </div>
        <div class="border-t border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)] px-4 py-5 sm:px-6">
            <form method="POST" action="{{ route('profile.password.update') }}" class="space-y-6">
                @csrf
                @method('PATCH')

                <div>
                    <label for="current_password" class="block text-sm font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-400)]">
                        Current Password
                    </label>
                    <x-form.input type="password" name="current_password" id="current_password" required />
                    @error('current_password')
                        <p class="mt-1 text-sm text-[color:var(--color-danger-600)]">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-400)]">
                        New Password
                    </label>
                    <x-form.input type="password" name="password" id="password" required />
                    @error('password')
                        <p class="mt-1 text-sm text-[color:var(--color-danger-600)]">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-400)]">
                        Confirm New Password
                    </label>
                    <x-form.input type="password" name="password_confirmation" id="password_confirmation" required />
                </div>

                <div class="flex justify-end space-x-3">
                    <x-button type="button" variant="secondary" onclick="document.getElementById('password-form').style.display = 'none'">
                        Cancel
                    </x-button>
                    <x-button type="submit" variant="primary">
                        Update Password
                    </x-button>
                </div>
            </form>
        </div>
    </div>
@endsection
