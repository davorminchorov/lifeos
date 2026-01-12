@extends('layouts.app')

@section('title', 'Schedule Interview - LifeOS')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Schedule Interview</h1>
                <p class="text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] mt-2">
                    {{ $application->job_title }} at {{ $application->company_name }}
                </p>
            </div>
            <x-button
                href="{{ route('job-applications.show', $application) }}"
                variant="secondary">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back to Application
            </x-button>
        </div>

        <!-- Form -->
        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow-md rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
            <form method="POST" action="{{ route('job-applications.interviews.store', $application) }}" class="space-y-6 p-6">
                @csrf

                <!-- Interview Details -->
                <div>
                    <h3 class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-4">Interview Details</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <x-form.select
                            name="type"
                            label="Interview Type"
                            :required="true"
                            placeholder="Select Type">
                            @foreach($types as $typeOption)
                                <option value="{{ $typeOption->value }}" {{ old('type') === $typeOption->value ? 'selected' : '' }}>
                                    {{ ucfirst(str_replace('_', ' ', $typeOption->value)) }}
                                </option>
                            @endforeach
                        </x-form.select>

                        <x-form.input
                            name="scheduled_at"
                            type="datetime-local"
                            label="Scheduled Date & Time"
                            :required="true" />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                        <x-form.input
                            name="duration_minutes"
                            type="number"
                            label="Duration (minutes)"
                            min="1"
                            max="480"
                            value="60"
                            placeholder="60" />

                        <x-form.input
                            name="interviewer_name"
                            type="text"
                            label="Interviewer Name"
                            placeholder="e.g., John Smith" />
                    </div>
                </div>

                <!-- Location Details -->
                <div class="border-t border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)] pt-6">
                    <h3 class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-4">Location Details</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <x-form.input
                            name="location"
                            type="text"
                            label="Location"
                            placeholder="Office address or 'Remote'" />

                        <x-form.input
                            name="video_link"
                            type="url"
                            label="Video Call Link"
                            placeholder="https://zoom.us/j/..." />
                    </div>
                </div>

                <!-- Notes -->
                <div class="border-t border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)] pt-6">
                    <h3 class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-4">Preparation Notes</h3>

                    <x-form.input
                        name="notes"
                        type="textarea"
                        label="Notes"
                        rows="4"
                        placeholder="Preparation notes, key points to mention, questions to ask..." />
                </div>

                <!-- Post-Interview (Optional) -->
                <div class="border-t border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)] pt-6">
                    <h3 class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-4">Post-Interview (Optional)</h3>
                    <p class="text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] mb-4">
                        You can add feedback and outcome after the interview is completed.
                    </p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <x-form.select
                            name="outcome"
                            label="Outcome"
                            placeholder="Not yet determined">
                            @foreach($outcomes as $outcomeOption)
                                <option value="{{ $outcomeOption->value }}" {{ old('outcome') === $outcomeOption->value ? 'selected' : '' }}>
                                    {{ ucfirst($outcomeOption->value) }}
                                </option>
                            @endforeach
                        </x-form.select>

                        <div class="flex items-end">
                            <x-form.checkbox
                                name="completed"
                                label="Mark as Completed" />
                        </div>
                    </div>

                    <div class="mt-6">
                        <x-form.input
                            name="feedback"
                            type="textarea"
                            label="Feedback"
                            rows="4"
                            placeholder="How did the interview go? What went well? Areas for improvement..." />
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="border-t border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)] pt-6 flex justify-end gap-3">
                    <x-button
                        href="{{ route('job-applications.show', $application) }}"
                        variant="secondary">
                        Cancel
                    </x-button>
                    <x-button
                        type="submit"
                        variant="primary">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        Schedule Interview
                    </x-button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
