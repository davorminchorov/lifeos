@extends('layouts.app')

@section('title', $application->job_title . ' at ' . $application->company_name . ' - Job Applications - LifeOS')

@section('header')
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                {{ $application->job_title }}
            </h1>
            <p class="mt-2 text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">
                {{ $application->company_name }}
            </p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('job-applications.edit', $application) }}" class="bg-[color:var(--color-accent-500)] hover:bg-[color:var(--color-accent-600)] text-white px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200">
                Edit
            </a>
            <a href="{{ route('job-applications.index') }}" class="bg-[color:var(--color-primary-500)] hover:bg-[color:var(--color-primary-600)] text-white px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200">
                Back to List
            </a>
        </div>
    </div>
@endsection

@section('content')
    <div x-data="{ activeTab: 'overview' }">
        <!-- Tab Navigation -->
        <div class="mb-6 border-b border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
            <nav class="-mb-px flex space-x-8">
                <button @click="activeTab = 'overview'" :class="activeTab === 'overview' ? 'border-[color:var(--color-accent-500)] text-[color:var(--color-accent-600)]' : 'border-transparent text-[color:var(--color-primary-500)] hover:text-[color:var(--color-primary-700)] hover:border-[color:var(--color-primary-300)]'" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm dark:text-[color:var(--color-dark-500)] dark:hover:text-[color:var(--color-dark-600)]">
                    Overview
                </button>
                <button @click="activeTab = 'timeline'" :class="activeTab === 'timeline' ? 'border-[color:var(--color-accent-500)] text-[color:var(--color-accent-600)]' : 'border-transparent text-[color:var(--color-primary-500)] hover:text-[color:var(--color-primary-700)] hover:border-[color:var(--color-primary-300)]'" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm dark:text-[color:var(--color-dark-500)] dark:hover:text-[color:var(--color-dark-600)]">
                    Timeline
                    <span class="ml-2 bg-[color:var(--color-primary-200)] dark:bg-[color:var(--color-dark-300)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] py-0.5 px-2 rounded-full text-xs">
                        {{ $application->statusHistories->count() }}
                    </span>
                </button>
                <button @click="activeTab = 'interviews'" :class="activeTab === 'interviews' ? 'border-[color:var(--color-accent-500)] text-[color:var(--color-accent-600)]' : 'border-transparent text-[color:var(--color-primary-500)] hover:text-[color:var(--color-primary-700)] hover:border-[color:var(--color-primary-300)]'" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm dark:text-[color:var(--color-dark-500)] dark:hover:text-[color:var(--color-dark-600)]">
                    Interviews
                    <span class="ml-2 bg-[color:var(--color-primary-200)] dark:bg-[color:var(--color-dark-300)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] py-0.5 px-2 rounded-full text-xs">
                        {{ $application->interviews->count() }}
                    </span>
                </button>
                <button @click="activeTab = 'offer'" :class="activeTab === 'offer' ? 'border-[color:var(--color-accent-500)] text-[color:var(--color-accent-600)]' : 'border-transparent text-[color:var(--color-primary-500)] hover:text-[color:var(--color-primary-700)] hover:border-[color:var(--color-primary-300)]'" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm dark:text-[color:var(--color-dark-500)] dark:hover:text-[color:var(--color-dark-600)]">
                    Offer
                    @if($application->offer)
                        <span class="ml-2 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 py-0.5 px-2 rounded-full text-xs">✓</span>
                    @endif
                </button>
            </nav>
        </div>

        <!-- Overview Tab -->
        <div x-show="activeTab === 'overview'" class="space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Application Status -->
                <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow overflow-hidden sm:rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
                    <div class="px-4 py-5 sm:px-6">
                        <h3 class="text-lg leading-6 font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                            Application Status
                        </h3>
                    </div>
                    <div class="border-t border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)]">
                        <dl>
                            <div class="bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Current Status</dt>
                                <dd class="mt-1 text-sm sm:mt-0 sm:col-span-2">
                                    <x-job-application.status-badge :status="$application->status" />
                                </dd>
                            </div>
                            <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Priority</dt>
                                <dd class="mt-1 text-sm sm:mt-0 sm:col-span-2">
                                    <x-job-application.priority-badge :priority="$application->priority" />
                                </dd>
                            </div>
                            <div class="bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Source</dt>
                                <dd class="mt-1 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] sm:mt-0 sm:col-span-2">
                                    {{ $application->source->label() }}
                                </dd>
                            </div>
                            @if($application->applied_at)
                                <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Applied Date</dt>
                                    <dd class="mt-1 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] sm:mt-0 sm:col-span-2">
                                        {{ $application->applied_at->format('F j, Y') }}
                                        @if($application->days_since_applied)
                                            <span class="text-xs text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-400)]">({{ $application->days_since_applied }} days ago)</span>
                                        @endif
                                    </dd>
                                </div>
                            @endif
                            <div class="bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Days in Status</dt>
                                <dd class="mt-1 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] sm:mt-0 sm:col-span-2">
                                    {{ $application->days_in_current_status }} days
                                </dd>
                            </div>
                            @if($application->next_action_at)
                                <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Next Action</dt>
                                    <dd class="mt-1 text-sm sm:mt-0 sm:col-span-2">
                                        <span class="{{ $application->next_action_at->isPast() ? 'text-red-600 dark:text-red-400 font-medium' : 'text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]' }}">
                                            {{ $application->next_action_at->format('F j, Y g:i A') }}
                                            @if($application->next_action_at->isPast())
                                                (Overdue!)
                                            @endif
                                        </span>
                                    </dd>
                                </div>
                            @endif
                        </dl>
                    </div>
                </div>

                <!-- Job Details -->
                <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow overflow-hidden sm:rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
                    <div class="px-4 py-5 sm:px-6">
                        <h3 class="text-lg leading-6 font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                            Job Details
                        </h3>
                    </div>
                    <div class="border-t border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)]">
                        <dl>
                            @if($application->company_website)
                                <div class="bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Company Website</dt>
                                    <dd class="mt-1 text-sm sm:mt-0 sm:col-span-2">
                                        <a href="{{ $application->company_website }}" target="_blank" class="text-[color:var(--color-accent-500)] hover:text-[color:var(--color-accent-600)] dark:text-[color:var(--color-accent-400)]">
                                            {{ $application->company_website }}
                                            <svg class="inline w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                            </svg>
                                        </a>
                                    </dd>
                                </div>
                            @endif
                            @if($application->job_url)
                                <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Job Posting</dt>
                                    <dd class="mt-1 text-sm sm:mt-0 sm:col-span-2">
                                        <a href="{{ $application->job_url }}" target="_blank" class="text-[color:var(--color-accent-500)] hover:text-[color:var(--color-accent-600)] dark:text-[color:var(--color-accent-400)]">
                                            View Posting
                                            <svg class="inline w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                            </svg>
                                        </a>
                                    </dd>
                                </div>
                            @endif
                            @if($application->location)
                                <div class="bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Location</dt>
                                    <dd class="mt-1 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] sm:mt-0 sm:col-span-2">
                                        {{ $application->location }}
                                        @if($application->remote)
                                            <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                                Remote
                                            </span>
                                        @endif
                                    </dd>
                                </div>
                            @endif
                            @if($application->formatted_salary_range)
                                <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Salary Range</dt>
                                    <dd class="mt-1 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] sm:mt-0 sm:col-span-2">
                                        {{ $application->formatted_salary_range }}
                                    </dd>
                                </div>
                            @endif
                            @if($application->job_description)
                                <div class="bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] px-4 py-5 sm:px-6">
                                    <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] mb-2">Description</dt>
                                    <dd class="mt-1 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] whitespace-pre-wrap">
                                        {{ $application->job_description }}
                                    </dd>
                                </div>
                            @endif
                        </dl>
                    </div>
                </div>
            </div>

            <!-- Contact Information & Notes -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                @if($application->contact_name || $application->contact_email || $application->contact_phone)
                    <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow overflow-hidden sm:rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
                        <div class="px-4 py-5 sm:px-6">
                            <h3 class="text-lg leading-6 font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                Contact Information
                            </h3>
                        </div>
                        <div class="border-t border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)]">
                            <dl>
                                @if($application->contact_name)
                                    <div class="bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                        <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Name</dt>
                                        <dd class="mt-1 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] sm:mt-0 sm:col-span-2">
                                            {{ $application->contact_name }}
                                        </dd>
                                    </div>
                                @endif
                                @if($application->contact_email)
                                    <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                        <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Email</dt>
                                        <dd class="mt-1 text-sm sm:mt-0 sm:col-span-2">
                                            <a href="mailto:{{ $application->contact_email }}" class="text-[color:var(--color-accent-500)] hover:text-[color:var(--color-accent-600)] dark:text-[color:var(--color-accent-400)]">
                                                {{ $application->contact_email }}
                                            </a>
                                        </dd>
                                    </div>
                                @endif
                                @if($application->contact_phone)
                                    <div class="bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                        <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Phone</dt>
                                        <dd class="mt-1 text-sm sm:mt-0 sm:col-span-2">
                                            <a href="tel:{{ $application->contact_phone }}" class="text-[color:var(--color-accent-500)] hover:text-[color:var(--color-accent-600)] dark:text-[color:var(--color-accent-400)]">
                                                {{ $application->contact_phone }}
                                            </a>
                                        </dd>
                                    </div>
                                @endif
                            </dl>
                        </div>
                    </div>
                @endif

                @if($application->notes || $application->tags)
                    <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow overflow-hidden sm:rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
                        <div class="px-4 py-5 sm:px-6">
                            <h3 class="text-lg leading-6 font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                Additional Information
                            </h3>
                        </div>
                        <div class="border-t border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)]">
                            <dl>
                                @if($application->tags)
                                    <div class="bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] px-4 py-5 sm:px-6">
                                        <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] mb-2">Tags</dt>
                                        <dd class="mt-1 flex flex-wrap gap-2">
                                            @foreach($application->tags as $tag)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-[color:var(--color-primary-200)] text-[color:var(--color-primary-700)] dark:bg-[color:var(--color-dark-300)] dark:text-[color:var(--color-dark-600)]">
                                                    {{ $tag }}
                                                </span>
                                            @endforeach
                                        </dd>
                                    </div>
                                @endif
                                @if($application->notes)
                                    <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] px-4 py-5 sm:px-6">
                                        <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] mb-2">Notes</dt>
                                        <dd class="mt-1 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] whitespace-pre-wrap">
                                            {{ $application->notes }}
                                        </dd>
                                    </div>
                                @endif
                            </dl>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Timeline Tab -->
        <div x-show="activeTab === 'timeline'" class="space-y-6">
            <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow overflow-hidden sm:rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                        Status History
                    </h3>
                    <p class="mt-1 max-w-2xl text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">
                        Track all status changes for this application
                    </p>
                </div>
                <div class="border-t border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)] px-4 py-5 sm:px-6">
                    @if($application->statusHistories->count() > 0)
                        <div class="flow-root">
                            <ul role="list" class="-mb-8">
                                @foreach($application->statusHistories as $history)
                                    <li>
                                        <div class="relative pb-8">
                                            @if(!$loop->last)
                                                <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-[color:var(--color-primary-300)] dark:bg-[color:var(--color-dark-300)]" aria-hidden="true"></span>
                                            @endif
                                            <div class="relative flex space-x-3">
                                                <div>
                                                    <span class="h-8 w-8 rounded-full bg-[color:var(--color-primary-200)] dark:bg-[color:var(--color-dark-300)] flex items-center justify-center ring-8 ring-[color:var(--color-primary-100)] dark:ring-[color:var(--color-dark-200)]">
                                                        <svg class="h-5 w-5 text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                                        </svg>
                                                    </span>
                                                </div>
                                                <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                                                    <div>
                                                        <p class="text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                                            @if($history->from_status)
                                                                Changed from <x-job-application.status-badge :status="$history->from_status" /> to <x-job-application.status-badge :status="$history->to_status" />
                                                            @else
                                                                Initial status set to <x-job-application.status-badge :status="$history->to_status" />
                                                            @endif
                                                        </p>
                                                        @if($history->notes)
                                                            <p class="mt-1 text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">
                                                                {{ $history->notes }}
                                                            </p>
                                                        @endif
                                                    </div>
                                                    <div class="whitespace-nowrap text-right text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-400)]">
                                                        <time datetime="{{ $history->changed_at->toIso8601String() }}">
                                                            {{ $history->changed_at->format('M j, Y') }}<br>
                                                            <span class="text-xs">{{ $history->changed_at->format('g:i A') }}</span>
                                                        </time>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @else
                        <p class="text-center text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] py-8">
                            No status history available.
                        </p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Interviews Tab -->
        <div x-show="activeTab === 'interviews'" class="space-y-6">
            <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow overflow-hidden sm:rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
                <div class="px-4 py-5 sm:px-6 flex justify-between items-center">
                    <div>
                        <h3 class="text-lg leading-6 font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                            Interviews
                        </h3>
                        <p class="mt-1 max-w-2xl text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">
                            Scheduled and completed interviews
                        </p>
                    </div>
                    <a href="{{ route('job-applications.interviews.create', $application) }}" class="inline-flex items-center px-4 py-2 bg-[color:var(--color-accent-500)] hover:bg-[color:var(--color-accent-600)] text-white text-sm font-medium rounded-md transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Schedule Interview
                    </a>
                </div>
                <div class="border-t border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)]">
                    @if($application->interviews->count() > 0)
                        <ul role="list" class="divide-y divide-[color:var(--color-primary-200)] dark:divide-[color:var(--color-dark-300)]">
                            @foreach($application->interviews as $interview)
                                <li class="px-4 py-5 sm:px-6">
                                    <div class="flex items-center justify-between">
                                        <div class="flex-1">
                                            <div class="flex items-center justify-between">
                                                <p class="text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                                    {{ ucfirst(str_replace('_', ' ', $interview->type->value)) }} Interview
                                                    @if($interview->completed)
                                                        <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                                            Completed
                                                        </span>
                                                    @endif
                                                </p>
                                                <div class="ml-2 flex-shrink-0 flex">
                                                    <a href="{{ route('job-applications.interviews.edit', [$application, $interview]) }}" class="text-[color:var(--color-accent-500)] hover:text-[color:var(--color-accent-600)] text-sm">
                                                        Edit
                                                    </a>
                                                </div>
                                            </div>
                                            <div class="mt-2 sm:flex sm:justify-between">
                                                <div class="sm:flex">
                                                    <p class="flex items-center text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">
                                                        <svg class="flex-shrink-0 mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                        </svg>
                                                        {{ $interview->scheduled_at->format('F j, Y \a\t g:i A') }}
                                                    </p>
                                                    @if($interview->duration_minutes)
                                                        <p class="mt-2 flex items-center text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] sm:mt-0 sm:ml-6">
                                                            <svg class="flex-shrink-0 mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                            </svg>
                                                            {{ $interview->duration_minutes }} minutes
                                                        </p>
                                                    @endif
                                                </div>
                                            </div>
                                            @if($interview->interviewer_name)
                                                <p class="mt-2 text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">
                                                    Interviewer: {{ $interview->interviewer_name }}
                                                </p>
                                            @endif
                                            @if($interview->outcome)
                                                <p class="mt-2 text-sm">
                                                    Outcome:
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                                        @if($interview->outcome->value === 'positive') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                                        @elseif($interview->outcome->value === 'negative') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                                        @elseif($interview->outcome->value === 'neutral') bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200
                                                        @else bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                                        @endif">
                                                        {{ ucfirst($interview->outcome->value) }}
                                                    </span>
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="px-4 py-12 text-center">
                            <svg class="mx-auto h-12 w-12 text-[color:var(--color-primary-400)] dark:text-[color:var(--color-dark-400)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">No interviews scheduled</h3>
                            <p class="mt-1 text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">
                                Get started by scheduling your first interview.
                            </p>
                            <div class="mt-6">
                                <a href="{{ route('job-applications.interviews.create', $application) }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-[color:var(--color-accent-500)] hover:bg-[color:var(--color-accent-600)]">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                    Schedule Interview
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Offer Tab -->
        <div x-show="activeTab === 'offer'" class="space-y-6">
            <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow overflow-hidden sm:rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
                <div class="px-4 py-5 sm:px-6 flex justify-between items-center">
                    <div>
                        <h3 class="text-lg leading-6 font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                            Job Offer
                        </h3>
                        <p class="mt-1 max-w-2xl text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">
                            Details of the job offer received
                        </p>
                    </div>
                    @if(!$application->offer)
                        <a href="{{ route('job-applications.offers.create', $application) }}" class="inline-flex items-center px-4 py-2 bg-[color:var(--color-accent-500)] hover:bg-[color:var(--color-accent-600)] text-white text-sm font-medium rounded-md transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Record Offer
                        </a>
                    @endif
                </div>
                <div class="border-t border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)]">
                    @if($application->offer)
                        <dl>
                            <div class="bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Base Salary</dt>
                                <dd class="mt-1 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] sm:mt-0 sm:col-span-2 font-semibold">
                                    {{ app(\App\Services\CurrencyService::class)->format($application->offer->base_salary, $application->offer->currency) }}
                                </dd>
                            </div>
                            @if($application->offer->bonus)
                                <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Bonus</dt>
                                    <dd class="mt-1 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] sm:mt-0 sm:col-span-2">
                                        {{ app(\App\Services\CurrencyService::class)->format($application->offer->bonus, $application->offer->currency) }}
                                    </dd>
                                </div>
                            @endif
                            @if($application->offer->equity)
                                <div class="bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Equity</dt>
                                    <dd class="mt-1 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] sm:mt-0 sm:col-span-2">
                                        {{ $application->offer->equity }}
                                    </dd>
                                </div>
                            @endif
                            <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Status</dt>
                                <dd class="mt-1 text-sm sm:mt-0 sm:col-span-2">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        @if($application->offer->status->value === 'accepted') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                        @elseif($application->offer->status->value === 'declined') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                        @elseif($application->offer->status->value === 'expired') bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200
                                        @elseif($application->offer->status->value === 'negotiating') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                        @else bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200
                                        @endif">
                                        {{ ucfirst($application->offer->status->value) }}
                                    </span>
                                </dd>
                            </div>
                            @if($application->offer->start_date)
                                <div class="bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Start Date</dt>
                                    <dd class="mt-1 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] sm:mt-0 sm:col-span-2">
                                        {{ $application->offer->start_date->format('F j, Y') }}
                                    </dd>
                                </div>
                            @endif
                            @if($application->offer->decision_deadline)
                                <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Decision Deadline</dt>
                                    <dd class="mt-1 text-sm sm:mt-0 sm:col-span-2">
                                        <span class="{{ $application->offer->decision_deadline->isPast() ? 'text-red-600 dark:text-red-400 font-medium' : 'text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]' }}">
                                            {{ $application->offer->decision_deadline->format('F j, Y') }}
                                            @if($application->offer->decision_deadline->isPast())
                                                (Expired)
                                            @endif
                                        </span>
                                    </dd>
                                </div>
                            @endif
                            @if($application->offer->benefits)
                                <div class="bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] px-4 py-5 sm:px-6">
                                    <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] mb-2">Benefits</dt>
                                    <dd class="mt-1 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] whitespace-pre-wrap">
                                        {{ $application->offer->benefits }}
                                    </dd>
                                </div>
                            @endif
                            @if($application->offer->notes)
                                <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] px-4 py-5 sm:px-6">
                                    <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] mb-2">Notes</dt>
                                    <dd class="mt-1 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] whitespace-pre-wrap">
                                        {{ $application->offer->notes }}
                                    </dd>
                                </div>
                            @endif
                        </dl>
                        <div class="px-4 py-4 sm:px-6 flex justify-end gap-3 bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)]">
                            <a href="{{ route('job-applications.offers.edit', [$application, $application->offer]) }}" class="inline-flex items-center px-4 py-2 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded-md shadow-sm text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] hover:bg-[color:var(--color-primary-200)] dark:hover:bg-[color:var(--color-dark-300)]">
                                Edit Offer
                            </a>
                        </div>
                    @else
                        <div class="px-4 py-12 text-center">
                            <svg class="mx-auto h-12 w-12 text-[color:var(--color-primary-400)] dark:text-[color:var(--color-dark-400)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">No offer received</h3>
                            <p class="mt-1 text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">
                                Once you receive an offer, you can record the details here.
                            </p>
                            <div class="mt-6">
                                <a href="{{ route('job-applications.offers.create', $application) }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-[color:var(--color-accent-500)] hover:bg-[color:var(--color-accent-600)]">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                    Record Offer
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
