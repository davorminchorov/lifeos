@extends('layouts.app')

@section('title', 'Add New Job Application - LifeOS')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Add New Job Application</h1>
                <p class="text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] mt-2">Track a new job opportunity</p>
            </div>
            <a href="{{ route('job-applications.index') }}"
               class="inline-flex items-center px-4 py-2 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded-md shadow-sm text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] hover:bg-[color:var(--color-primary-200)] dark:hover:bg-[color:var(--color-dark-300)]">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back to List
            </a>
        </div>

        <!-- Form -->
        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow-md rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
            <form method="POST" action="{{ route('job-applications.store') }}" class="space-y-6 p-6">
                @csrf

                <!-- Company Information -->
                <div>
                    <h3 class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-4">Company Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="company_name" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Company Name *</label>
                            <input type="text" name="company_name" id="company_name" required
                                   value="{{ old('company_name') }}"
                                   class="mt-1 block w-full px-3 py-2 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded-md shadow-sm focus:outline-none focus:ring-[color:var(--color-accent-500)] focus:border-[color:var(--color-accent-500)] bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]"
                                   placeholder="e.g., Google, Microsoft">
                            @error('company_name')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="company_website" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Company Website</label>
                            <input type="url" name="company_website" id="company_website"
                                   value="{{ old('company_website') }}"
                                   class="mt-1 block w-full px-3 py-2 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded-md shadow-sm focus:outline-none focus:ring-[color:var(--color-accent-500)] focus:border-[color:var(--color-accent-500)] bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]"
                                   placeholder="https://example.com">
                            @error('company_website')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Job Details -->
                <div class="border-t border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)] pt-6">
                    <h3 class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-4">Job Details</h3>

                    <div class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="job_title" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Job Title *</label>
                                <input type="text" name="job_title" id="job_title" required
                                       value="{{ old('job_title') }}"
                                       class="mt-1 block w-full px-3 py-2 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded-md shadow-sm focus:outline-none focus:ring-[color:var(--color-accent-500)] focus:border-[color:var(--color-accent-500)] bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]"
                                       placeholder="e.g., Senior Software Engineer">
                                @error('job_title')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="job_url" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Job Posting URL</label>
                                <input type="url" name="job_url" id="job_url"
                                       value="{{ old('job_url') }}"
                                       class="mt-1 block w-full px-3 py-2 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded-md shadow-sm focus:outline-none focus:ring-[color:var(--color-accent-500)] focus:border-[color:var(--color-accent-500)] bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]"
                                       placeholder="https://careers.example.com/job/123">
                                @error('job_url')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div>
                            <label for="job_description" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Job Description</label>
                            <textarea name="job_description" id="job_description" rows="4"
                                      class="mt-1 block w-full px-3 py-2 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded-md shadow-sm focus:outline-none focus:ring-[color:var(--color-accent-500)] focus:border-[color:var(--color-accent-500)] bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]"
                                      placeholder="Key responsibilities and requirements...">{{ old('job_description') }}</textarea>
                            @error('job_description')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="location" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Location</label>
                                <input type="text" name="location" id="location"
                                       value="{{ old('location') }}"
                                       class="mt-1 block w-full px-3 py-2 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded-md shadow-sm focus:outline-none focus:ring-[color:var(--color-accent-500)] focus:border-[color:var(--color-accent-500)] bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]"
                                       placeholder="e.g., San Francisco, CA">
                                @error('location')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="flex items-end">
                                <label class="flex items-center">
                                    <input type="checkbox" name="remote" id="remote" value="1" {{ old('remote') ? 'checked' : '' }}
                                           class="rounded border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] text-[color:var(--color-accent-500)] focus:ring-[color:var(--color-accent-500)] bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)]">
                                    <span class="ml-2 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Remote Position</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Salary Information -->
                <div class="border-t border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)] pt-6">
                    <h3 class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-4">Salary Expectations</h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label for="salary_min" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Minimum Salary</label>
                            <input type="number" step="0.01" name="salary_min" id="salary_min" min="0"
                                   value="{{ old('salary_min') }}"
                                   class="mt-1 block w-full px-3 py-2 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded-md shadow-sm focus:outline-none focus:ring-[color:var(--color-accent-500)] focus:border-[color:var(--color-accent-500)] bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]"
                                   placeholder="50000">
                            @error('salary_min')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="salary_max" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Maximum Salary</label>
                            <input type="number" step="0.01" name="salary_max" id="salary_max" min="0"
                                   value="{{ old('salary_max') }}"
                                   class="mt-1 block w-full px-3 py-2 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded-md shadow-sm focus:outline-none focus:ring-[color:var(--color-accent-500)] focus:border-[color:var(--color-accent-500)] bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]"
                                   placeholder="80000">
                            @error('salary_max')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="currency" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Currency</label>
                            <select name="currency" id="currency"
                                    class="mt-1 block w-full px-3 py-2 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded-md shadow-sm focus:outline-none focus:ring-[color:var(--color-accent-500)] focus:border-[color:var(--color-accent-500)] bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                @foreach($currencies as $curr)
                                    <option value="{{ $curr }}" {{ old('currency', 'USD') === $curr ? 'selected' : '' }}>{{ $curr }}</option>
                                @endforeach
                            </select>
                            @error('currency')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Application Details -->
                <div class="border-t border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)] pt-6">
                    <h3 class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-4">Application Details</h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label for="status" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Status *</label>
                            <select name="status" id="status" required
                                    class="mt-1 block w-full px-3 py-2 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded-md shadow-sm focus:outline-none focus:ring-[color:var(--color-accent-500)] focus:border-[color:var(--color-accent-500)] bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                @foreach($statuses as $statusOption)
                                    <option value="{{ $statusOption->value }}" {{ old('status', 'wishlist') === $statusOption->value ? 'selected' : '' }}>
                                        {{ $statusOption->label() }}
                                    </option>
                                @endforeach
                            </select>
                            @error('status')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="source" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Source *</label>
                            <select name="source" id="source" required
                                    class="mt-1 block w-full px-3 py-2 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded-md shadow-sm focus:outline-none focus:ring-[color:var(--color-accent-500)] focus:border-[color:var(--color-accent-500)] bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                @foreach($sources as $sourceOption)
                                    <option value="{{ $sourceOption->value }}" {{ old('source') === $sourceOption->value ? 'selected' : '' }}>
                                        {{ ucfirst(str_replace('_', ' ', $sourceOption->value)) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('source')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="priority" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Priority</label>
                            <select name="priority" id="priority"
                                    class="mt-1 block w-full px-3 py-2 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded-md shadow-sm focus:outline-none focus:ring-[color:var(--color-accent-500)] focus:border-[color:var(--color-accent-500)] bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                <option value="0" {{ old('priority', 0) == 0 ? 'selected' : '' }}>Low</option>
                                <option value="1" {{ old('priority') == 1 ? 'selected' : '' }}>Medium</option>
                                <option value="2" {{ old('priority') == 2 ? 'selected' : '' }}>High</option>
                                <option value="3" {{ old('priority') == 3 ? 'selected' : '' }}>Urgent</option>
                            </select>
                            @error('priority')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                        <div>
                            <label for="applied_at" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Applied Date</label>
                            <input type="date" name="applied_at" id="applied_at"
                                   value="{{ old('applied_at') }}"
                                   class="mt-1 block w-full px-3 py-2 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded-md shadow-sm focus:outline-none focus:ring-[color:var(--color-accent-500)] focus:border-[color:var(--color-accent-500)] bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                            @error('applied_at')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="next_action_at" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Next Action Date</label>
                            <input type="datetime-local" name="next_action_at" id="next_action_at"
                                   value="{{ old('next_action_at') }}"
                                   class="mt-1 block w-full px-3 py-2 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded-md shadow-sm focus:outline-none focus:ring-[color:var(--color-accent-500)] focus:border-[color:var(--color-accent-500)] bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                            @error('next_action_at')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="border-t border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)] pt-6">
                    <h3 class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-4">Contact Information</h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label for="contact_name" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Contact Name</label>
                            <input type="text" name="contact_name" id="contact_name"
                                   value="{{ old('contact_name') }}"
                                   class="mt-1 block w-full px-3 py-2 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded-md shadow-sm focus:outline-none focus:ring-[color:var(--color-accent-500)] focus:border-[color:var(--color-accent-500)] bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]"
                                   placeholder="Recruiter or Hiring Manager">
                            @error('contact_name')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="contact_email" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Contact Email</label>
                            <input type="email" name="contact_email" id="contact_email"
                                   value="{{ old('contact_email') }}"
                                   class="mt-1 block w-full px-3 py-2 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded-md shadow-sm focus:outline-none focus:ring-[color:var(--color-accent-500)] focus:border-[color:var(--color-accent-500)] bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]"
                                   placeholder="recruiter@example.com">
                            @error('contact_email')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="contact_phone" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Contact Phone</label>
                            <input type="tel" name="contact_phone" id="contact_phone"
                                   value="{{ old('contact_phone') }}"
                                   class="mt-1 block w-full px-3 py-2 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded-md shadow-sm focus:outline-none focus:ring-[color:var(--color-accent-500)] focus:border-[color:var(--color-accent-500)] bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]"
                                   placeholder="+1 (555) 123-4567">
                            @error('contact_phone')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Notes and Tags -->
                <div class="border-t border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)] pt-6">
                    <h3 class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-4">Additional Information</h3>

                    <div class="space-y-6">
                        <div>
                            <label for="notes" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Notes</label>
                            <textarea name="notes" id="notes" rows="4"
                                      class="mt-1 block w-full px-3 py-2 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded-md shadow-sm focus:outline-none focus:ring-[color:var(--color-accent-500)] focus:border-[color:var(--color-accent-500)] bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]"
                                      placeholder="Any additional notes about this application...">{{ old('notes') }}</textarea>
                            @error('notes')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="tags" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Tags</label>
                            <input type="text" name="tags" id="tags"
                                   value="{{ old('tags') }}"
                                   class="mt-1 block w-full px-3 py-2 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded-md shadow-sm focus:outline-none focus:ring-[color:var(--color-accent-500)] focus:border-[color:var(--color-accent-500)] bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]"
                                   placeholder="e.g., startup, remote-first, senior-level (comma separated)">
                            <p class="mt-1 text-xs text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-400)]">Separate tags with commas</p>
                            @error('tags')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="border-t border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)] pt-6 flex justify-end gap-3">
                    <a href="{{ route('job-applications.index') }}"
                       class="inline-flex justify-center items-center px-6 py-3 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded-md shadow-sm text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] hover:bg-[color:var(--color-primary-200)] dark:hover:bg-[color:var(--color-dark-300)] transition-colors">
                        Cancel
                    </a>
                    <button type="submit"
                            class="inline-flex justify-center items-center px-6 py-3 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-[color:var(--color-accent-500)] hover:bg-[color:var(--color-accent-600)] transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Create Application
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
