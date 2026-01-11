@extends('layouts.app')

@section('title', 'Edit Job Application - LifeOS')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Edit Job Application</h1>
                <p class="text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] mt-2">Update application details</p>
            </div>
            <x-button href="{{ route('job-applications.show', $application) }}" variant="secondary">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back to Details
            </x-button>
        </div>

        <!-- Form -->
        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow-md rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
            <form method="POST" action="{{ route('job-applications.update', $application) }}" class="space-y-6 p-6">
                @csrf
                @method('PUT')

                <!-- Company Information -->
                <div>
                    <h3 class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-4">Company Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <x-form.input
                            name="company_name"
                            label="Company Name"
                            type="text"
                            :required="true"
                            :value="old('company_name', $application->company_name)"
                            placeholder="e.g., Google, Microsoft"
                        />

                        <x-form.input
                            name="company_website"
                            label="Company Website"
                            type="url"
                            :value="old('company_website', $application->company_website)"
                            placeholder="https://example.com"
                        />
                    </div>
                </div>

                <!-- Job Details -->
                <div class="border-t border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)] pt-6">
                    <h3 class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-4">Job Details</h3>

                    <div class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <x-form.input
                                name="job_title"
                                label="Job Title"
                                type="text"
                                :required="true"
                                :value="old('job_title', $application->job_title)"
                                placeholder="e.g., Senior Software Engineer"
                            />

                            <x-form.input
                                name="job_url"
                                label="Job Posting URL"
                                type="url"
                                :value="old('job_url', $application->job_url)"
                                placeholder="https://careers.example.com/job/123"
                            />
                        </div>

                        <x-form.input
                            name="job_description"
                            label="Job Description"
                            type="textarea"
                            :rows="4"
                            :value="old('job_description', $application->job_description)"
                            placeholder="Key responsibilities and requirements..."
                        />

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <x-form.input
                                name="location"
                                label="Location"
                                type="text"
                                :value="old('location', $application->location)"
                                placeholder="e.g., San Francisco, CA"
                            />

                            <x-form.checkbox
                                name="remote"
                                label="Remote Position"
                                :checked="old('remote', $application->remote)"
                                containerClass="flex items-end"
                            />
                        </div>
                    </div>
                </div>

                <!-- Salary Information -->
                <div class="border-t border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)] pt-6">
                    <h3 class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-4">Salary Expectations</h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <x-form.input
                            name="salary_min"
                            label="Minimum Salary"
                            type="number"
                            :min="0"
                            step="0.01"
                            :value="old('salary_min', $application->salary_min)"
                            placeholder="50000"
                        />

                        <x-form.input
                            name="salary_max"
                            label="Maximum Salary"
                            type="number"
                            :min="0"
                            step="0.01"
                            :value="old('salary_max', $application->salary_max)"
                            placeholder="80000"
                        />

                        <x-form.select
                            name="currency"
                            label="Currency"
                        >
                            @foreach(['MKD', 'USD', 'EUR', 'GBP', 'CAD', 'AUD', 'JPY', 'CHF', 'RSD', 'BGN'] as $curr)
                                <option value="{{ $curr }}" {{ old('currency', $application->currency) === $curr ? 'selected' : '' }}>{{ $curr }}</option>
                            @endforeach
                        </x-form.select>
                    </div>
                </div>

                <!-- Application Details -->
                <div class="border-t border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)] pt-6">
                    <h3 class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-4">Application Details</h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <x-form.select
                            name="status"
                            label="Status"
                            :required="true"
                        >
                            @foreach(\App\Enums\ApplicationStatus::cases() as $statusOption)
                                <option value="{{ $statusOption->value }}" {{ old('status', $application->status->value) === $statusOption->value ? 'selected' : '' }}>
                                    {{ $statusOption->label() }}
                                </option>
                            @endforeach
                        </x-form.select>

                        <x-form.select
                            name="source"
                            label="Source"
                            :required="true"
                        >
                            @foreach(\App\Enums\ApplicationSource::cases() as $sourceOption)
                                <option value="{{ $sourceOption->value }}" {{ old('source', $application->source->value) === $sourceOption->value ? 'selected' : '' }}>
                                    {{ ucfirst(str_replace('_', ' ', $sourceOption->value)) }}
                                </option>
                            @endforeach
                        </x-form.select>

                        <x-form.select
                            name="priority"
                            label="Priority"
                        >
                            <option value="0" {{ old('priority', $application->priority) == 0 ? 'selected' : '' }}>Low</option>
                            <option value="1" {{ old('priority', $application->priority) == 1 ? 'selected' : '' }}>Medium</option>
                            <option value="2" {{ old('priority', $application->priority) == 2 ? 'selected' : '' }}>High</option>
                            <option value="3" {{ old('priority', $application->priority) == 3 ? 'selected' : '' }}>Urgent</option>
                        </x-form.select>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                        <x-form.input
                            name="applied_at"
                            label="Applied Date"
                            type="date"
                            :value="old('applied_at', $application->applied_at?->format('Y-m-d'))"
                        />

                        <x-form.input
                            name="next_action_at"
                            label="Next Action Date"
                            type="datetime-local"
                            :value="old('next_action_at', $application->next_action_at?->format('Y-m-d\TH:i'))"
                        />
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="border-t border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)] pt-6">
                    <h3 class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-4">Contact Information</h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <x-form.input
                            name="contact_name"
                            label="Contact Name"
                            type="text"
                            :value="old('contact_name', $application->contact_name)"
                            placeholder="Recruiter or Hiring Manager"
                        />

                        <x-form.input
                            name="contact_email"
                            label="Contact Email"
                            type="email"
                            :value="old('contact_email', $application->contact_email)"
                            placeholder="recruiter@example.com"
                        />

                        <x-form.input
                            name="contact_phone"
                            label="Contact Phone"
                            type="tel"
                            :value="old('contact_phone', $application->contact_phone)"
                            placeholder="+1 (555) 123-4567"
                        />
                    </div>
                </div>

                <!-- Notes and Tags -->
                <div class="border-t border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)] pt-6">
                    <h3 class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-4">Additional Information</h3>

                    <div class="space-y-6">
                        <x-form.input
                            name="notes"
                            label="Notes"
                            type="textarea"
                            :rows="4"
                            :value="old('notes', $application->notes)"
                            placeholder="Any additional notes about this application..."
                        />

                        <x-form.input
                            name="tags"
                            label="Tags"
                            type="text"
                            :value="old('tags', is_array($application->tags) ? implode(', ', $application->tags) : '')"
                            placeholder="e.g., startup, remote-first, senior-level (comma separated)"
                            helpText="Separate tags with commas"
                        />
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="border-t border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)] pt-6 flex justify-end gap-3">
                    <x-button href="{{ route('job-applications.show', $application) }}" variant="secondary">
                        Cancel
                    </x-button>
                    <x-button type="submit" variant="primary">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Update Application
                    </x-button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
