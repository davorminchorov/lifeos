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
            <a href="{{ route('job-applications.show', $application) }}"
               class="inline-flex items-center px-4 py-2 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded-md shadow-sm text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] hover:bg-[color:var(--color-primary-200)] dark:hover:bg-[color:var(--color-dark-300)]">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back to Application
            </a>
        </div>

        <!-- Form -->
        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow-md rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
            <form method="POST" action="{{ route('job-applications.interviews.store', $application) }}" class="space-y-6 p-6">
                @csrf

                <!-- Interview Details -->
                <div>
                    <h3 class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-4">Interview Details</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="type" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Interview Type *</label>
                            <select name="type" id="type" required
                                    class="mt-1 block w-full px-3 py-2 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded-md shadow-sm focus:outline-none focus:ring-[color:var(--color-accent-500)] focus:border-[color:var(--color-accent-500)] bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                <option value="">Select Type</option>
                                @foreach($types as $typeOption)
                                    <option value="{{ $typeOption->value }}" {{ old('type') === $typeOption->value ? 'selected' : '' }}>
                                        {{ ucfirst(str_replace('_', ' ', $typeOption->value)) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('type')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="scheduled_at" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Scheduled Date & Time *</label>
                            <input type="datetime-local" name="scheduled_at" id="scheduled_at" required
                                   value="{{ old('scheduled_at') }}"
                                   class="mt-1 block w-full px-3 py-2 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded-md shadow-sm focus:outline-none focus:ring-[color:var(--color-accent-500)] focus:border-[color:var(--color-accent-500)] bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                            @error('scheduled_at')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                        <div>
                            <label for="duration_minutes" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Duration (minutes)</label>
                            <input type="number" name="duration_minutes" id="duration_minutes" min="1" max="480"
                                   value="{{ old('duration_minutes', 60) }}"
                                   class="mt-1 block w-full px-3 py-2 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded-md shadow-sm focus:outline-none focus:ring-[color:var(--color-accent-500)] focus:border-[color:var(--color-accent-500)] bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]"
                                   placeholder="60">
                            @error('duration_minutes')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="interviewer_name" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Interviewer Name</label>
                            <input type="text" name="interviewer_name" id="interviewer_name"
                                   value="{{ old('interviewer_name') }}"
                                   class="mt-1 block w-full px-3 py-2 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded-md shadow-sm focus:outline-none focus:ring-[color:var(--color-accent-500)] focus:border-[color:var(--color-accent-500)] bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]"
                                   placeholder="e.g., John Smith">
                            @error('interviewer_name')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Location Details -->
                <div class="border-t border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)] pt-6">
                    <h3 class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-4">Location Details</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="location" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Location</label>
                            <input type="text" name="location" id="location"
                                   value="{{ old('location') }}"
                                   class="mt-1 block w-full px-3 py-2 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded-md shadow-sm focus:outline-none focus:ring-[color:var(--color-accent-500)] focus:border-[color:var(--color-accent-500)] bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]"
                                   placeholder="Office address or 'Remote'">
                            @error('location')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="video_link" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Video Call Link</label>
                            <input type="url" name="video_link" id="video_link"
                                   value="{{ old('video_link') }}"
                                   class="mt-1 block w-full px-3 py-2 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded-md shadow-sm focus:outline-none focus:ring-[color:var(--color-accent-500)] focus:border-[color:var(--color-accent-500)] bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]"
                                   placeholder="https://zoom.us/j/...">
                            @error('video_link')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div class="border-t border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)] pt-6">
                    <h3 class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-4">Preparation Notes</h3>

                    <div>
                        <label for="notes" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Notes</label>
                        <textarea name="notes" id="notes" rows="4"
                                  class="mt-1 block w-full px-3 py-2 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded-md shadow-sm focus:outline-none focus:ring-[color:var(--color-accent-500)] focus:border-[color:var(--color-accent-500)] bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]"
                                  placeholder="Preparation notes, key points to mention, questions to ask...">{{ old('notes') }}</textarea>
                        @error('notes')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Post-Interview (Optional) -->
                <div class="border-t border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)] pt-6">
                    <h3 class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-4">Post-Interview (Optional)</h3>
                    <p class="text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] mb-4">
                        You can add feedback and outcome after the interview is completed.
                    </p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="outcome" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Outcome</label>
                            <select name="outcome" id="outcome"
                                    class="mt-1 block w-full px-3 py-2 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded-md shadow-sm focus:outline-none focus:ring-[color:var(--color-accent-500)] focus:border-[color:var(--color-accent-500)] bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                <option value="">Not yet determined</option>
                                @foreach($outcomes as $outcomeOption)
                                    <option value="{{ $outcomeOption->value }}" {{ old('outcome') === $outcomeOption->value ? 'selected' : '' }}>
                                        {{ ucfirst($outcomeOption->value) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('outcome')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-end">
                            <label class="flex items-center">
                                <input type="checkbox" name="completed" id="completed" value="1" {{ old('completed') ? 'checked' : '' }}
                                       class="rounded border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] text-[color:var(--color-accent-500)] focus:ring-[color:var(--color-accent-500)] bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)]">
                                <span class="ml-2 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Mark as Completed</span>
                            </label>
                        </div>
                    </div>

                    <div class="mt-6">
                        <label for="feedback" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Feedback</label>
                        <textarea name="feedback" id="feedback" rows="4"
                                  class="mt-1 block w-full px-3 py-2 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded-md shadow-sm focus:outline-none focus:ring-[color:var(--color-accent-500)] focus:border-[color:var(--color-accent-500)] bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]"
                                  placeholder="How did the interview go? What went well? Areas for improvement...">{{ old('feedback') }}</textarea>
                        @error('feedback')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="border-t border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)] pt-6 flex justify-end gap-3">
                    <a href="{{ route('job-applications.show', $application) }}"
                       class="inline-flex justify-center items-center px-6 py-3 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded-md shadow-sm text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] hover:bg-[color:var(--color-primary-200)] dark:hover:bg-[color:var(--color-dark-300)] transition-colors">
                        Cancel
                    </a>
                    <button type="submit"
                            class="inline-flex justify-center items-center px-6 py-3 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-[color:var(--color-accent-500)] hover:bg-[color:var(--color-accent-600)] transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        Schedule Interview
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
