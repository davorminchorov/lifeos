@extends('layouts.app')

@section('title', 'Edit Profile - LifeOS')

@section('header')
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                Edit Profile
            </h1>
            <p class="mt-2 text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">
                Update your account settings and personal information
            </p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('profile.show') }}" class="bg-[color:var(--color-primary-600)] hover:bg-[color:var(--color-primary-700)] text-white px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200">
                Back to Profile
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

    <div class="max-w-2xl mx-auto">
        <!-- Edit Profile Form -->
        <div class="bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                    Personal Information
                </h3>
                <p class="mt-1 max-w-2xl text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">
                    Update your basic account information and contact details.
                </p>
            </div>
            <div class="border-t border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)] px-4 py-5 sm:px-6">
                <form method="POST" action="{{ route('profile.update') }}" class="space-y-6">
                    @csrf
                    @method('PATCH')

                    <div>
                        <label for="name" class="block text-sm font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-400)]">
                            Full Name
                        </label>
                        <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required
                               class="mt-1 block w-full px-3 py-2 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-400)] rounded-md shadow-sm bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] focus:outline-none focus:ring-[color:var(--color-accent-500)] focus:border-[color:var(--color-accent-500)]">
                        @error('name')
                            <p class="mt-1 text-sm text-[color:var(--color-danger-600)]">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-400)]">
                            Email Address
                        </label>
                        <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required
                               class="mt-1 block w-full px-3 py-2 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-400)] rounded-md shadow-sm bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] focus:outline-none focus:ring-[color:var(--color-accent-500)] focus:border-[color:var(--color-accent-500)]">
                        @error('email')
                            <p class="mt-1 text-sm text-[color:var(--color-danger-600)]">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end space-x-3">
                        <a href="{{ route('profile.show') }}"
                           class="px-4 py-2 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-400)] rounded-md shadow-sm text-sm font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-400)] bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] hover:bg-[color:var(--color-primary-100)] dark:hover:bg-[color:var(--color-dark-200)] transition-colors duration-200">
                            Cancel
                        </a>
                        <button type="submit"
                                class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-[color:var(--color-accent-600)] hover:bg-[color:var(--color-accent-700)] transition-colors duration-200">
                            Update Profile
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Account Information -->
        <div class="mt-8 bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                    Account Information
                </h3>
                <p class="mt-1 max-w-2xl text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">
                    Read-only account information and security details.
                </p>
            </div>
            <div class="border-t border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)]">
                <dl>
                    <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-400)]">Member Since</dt>
                        <dd class="mt-1 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] sm:mt-0 sm:col-span-2">{{ $user->created_at->format('F j, Y') }}</dd>
                    </div>
                    <div class="bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-400)]">Last Updated</dt>
                        <dd class="mt-1 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] sm:mt-0 sm:col-span-2">{{ $user->updated_at->format('F j, Y \a\t g:i A') }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
@endsection
