@extends('layouts.app')

@section('title', 'Interviews - ' . $application->job_title . ' - LifeOS')

@section('header')
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                Interviews
            </h1>
            <p class="mt-2 text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">
                {{ $application->job_title }} at {{ $application->company_name }}
            </p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('job-applications.interviews.create', $application) }}" class="bg-[color:var(--color-accent-500)] hover:bg-[color:var(--color-accent-600)] text-white px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200">
                Schedule Interview
            </a>
            <a href="{{ route('job-applications.show', $application) }}" class="bg-[color:var(--color-primary-500)] hover:bg-[color:var(--color-primary-600)] text-white px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200">
                Back to Application
            </a>
        </div>
    </div>
@endsection

@section('content')
    <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow overflow-hidden sm:rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
        @if($interviews->isEmpty())
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-[color:var(--color-primary-400)] dark:text-[color:var(--color-dark-400)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">No interviews scheduled</h3>
                <p class="mt-1 text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Get started by scheduling your first interview.</p>
                <div class="mt-6">
                    <a href="{{ route('job-applications.interviews.create', $application) }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-[color:var(--color-accent-500)] hover:bg-[color:var(--color-accent-600)]">
                        Schedule Interview
                    </a>
                </div>
            </div>
        @else
            <ul class="divide-y divide-[color:var(--color-primary-200)] dark:divide-[color:var(--color-dark-300)]">
                @foreach($interviews as $interview)
                    <li class="hover:bg-[color:var(--color-primary-50)] dark:hover:bg-[color:var(--color-dark-100)] transition-colors">
                        <a href="{{ route('job-applications.interviews.show', [$application, $interview]) }}" class="block px-6 py-4">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-[color:var(--color-primary-200)] dark:bg-[color:var(--color-dark-300)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                            {{ ucfirst(str_replace('_', ' ', $interview->type->value)) }}
                                        </span>
                                        @if($interview->completed)
                                            <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200">
                                                Completed
                                            </span>
                                        @endif
                                    </div>
                                    <div class="mt-2 flex items-center text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                        <svg class="shrink-0 mr-1.5 h-5 w-5 text-[color:var(--color-primary-400)] dark:text-[color:var(--color-dark-400)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        {{ $interview->scheduled_at->format('F j, Y g:i A') }}
                                    </div>
                                    @if($interview->interviewer_name)
                                        <div class="mt-1 flex items-center text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">
                                            <svg class="shrink-0 mr-1.5 h-5 w-5 text-[color:var(--color-primary-400)] dark:text-[color:var(--color-dark-400)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                            </svg>
                                            {{ $interview->interviewer_name }}
                                        </div>
                                    @endif
                                    @if($interview->location)
                                        <div class="mt-1 flex items-center text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">
                                            <svg class="shrink-0 mr-1.5 h-5 w-5 text-[color:var(--color-primary-400)] dark:text-[color:var(--color-dark-400)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            </svg>
                                            {{ $interview->location }}
                                        </div>
                                    @endif
                                </div>
                                <div class="ml-4 shrink-0 flex items-center space-x-2">
                                    @if($interview->outcome)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            @if($interview->outcome->value === 'positive') bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200
                                            @elseif($interview->outcome->value === 'negative') bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200
                                            @elseif($interview->outcome->value === 'neutral') bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200
                                            @else bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200
                                            @endif">
                                            {{ ucfirst($interview->outcome->value) }}
                                        </span>
                                    @endif
                                    <svg class="h-5 w-5 text-[color:var(--color-primary-400)] dark:text-[color:var(--color-dark-400)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </div>
                            </div>
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
@endsection
