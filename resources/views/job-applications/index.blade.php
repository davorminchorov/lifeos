@extends('layouts.app')

@section('title', 'Job Applications - LifeOS')

@section('header')
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                Job Applications
            </h1>
            <p class="mt-2 text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">
                Track your job applications from initial interest through offer
            </p>
        </div>
        <div class="flex gap-2 flex-shrink-0">
            <x-button href="{{ route('job-applications.kanban') }}" variant="secondary" class="w-full sm:w-auto">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"></path>
                </svg>
                Kanban View
            </x-button>
            <x-button href="{{ route('job-applications.create') }}" variant="primary" class="w-full sm:w-auto">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Add Application
            </x-button>
        </div>
    </div>
@endsection

@section('content')
    <!-- Filters and Search -->
    <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow rounded-lg mb-6 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
        <div class="px-4 py-5 sm:px-6">
            <form method="GET" action="{{ route('job-applications.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Search -->
                <div>
                    <x-form.input
                        name="search"
                        label="Search"
                        type="text"
                        placeholder="Search company or job title..."
                        value="{{ request('search') }}"
                    />
                </div>

                <!-- Status Filter -->
                <div>
                    <x-form.select
                        name="status"
                        label="Status"
                        placeholder="All Status"
                    >
                        <option value="wishlist" {{ request('status') === 'wishlist' ? 'selected' : '' }}>Wishlist</option>
                        <option value="applied" {{ request('status') === 'applied' ? 'selected' : '' }}>Applied</option>
                        <option value="screening" {{ request('status') === 'screening' ? 'selected' : '' }}>Screening</option>
                        <option value="interview" {{ request('status') === 'interview' ? 'selected' : '' }}>Interview</option>
                        <option value="assessment" {{ request('status') === 'assessment' ? 'selected' : '' }}>Assessment</option>
                        <option value="offer" {{ request('status') === 'offer' ? 'selected' : '' }}>Offer</option>
                        <option value="accepted" {{ request('status') === 'accepted' ? 'selected' : '' }}>Accepted</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                        <option value="withdrawn" {{ request('status') === 'withdrawn' ? 'selected' : '' }}>Withdrawn</option>
                        <option value="archived" {{ request('status') === 'archived' ? 'selected' : '' }}>Archived</option>
                    </x-form.select>
                </div>

                <!-- Source Filter -->
                <div>
                    <x-form.select
                        name="source"
                        label="Source"
                        placeholder="All Sources"
                    >
                        <option value="linkedin" {{ request('source') === 'linkedin' ? 'selected' : '' }}>LinkedIn</option>
                        <option value="company_website" {{ request('source') === 'company_website' ? 'selected' : '' }}>Company Website</option>
                        <option value="job_board" {{ request('source') === 'job_board' ? 'selected' : '' }}>Job Board</option>
                        <option value="referral" {{ request('source') === 'referral' ? 'selected' : '' }}>Referral</option>
                        <option value="recruiter" {{ request('source') === 'recruiter' ? 'selected' : '' }}>Recruiter</option>
                        <option value="networking" {{ request('source') === 'networking' ? 'selected' : '' }}>Networking</option>
                        <option value="other" {{ request('source') === 'other' ? 'selected' : '' }}>Other</option>
                    </x-form.select>
                </div>

                <!-- Priority Filter -->
                <div>
                    <x-form.select
                        name="priority"
                        label="Priority"
                        placeholder="All Priorities"
                    >
                        <option value="0" {{ request('priority') === '0' ? 'selected' : '' }}>Low</option>
                        <option value="1" {{ request('priority') === '1' ? 'selected' : '' }}>Medium</option>
                        <option value="2" {{ request('priority') === '2' ? 'selected' : '' }}>High</option>
                        <option value="3" {{ request('priority') === '3' ? 'selected' : '' }}>Urgent</option>
                    </x-form.select>
                </div>

                <div class="col-span-full">
                    <div class="flex flex-col sm:flex-row gap-3 sm:gap-2">
                        <x-button type="submit" variant="primary" class="w-full sm:w-auto">
                            Apply Filters
                        </x-button>
                        <x-button href="{{ route('job-applications.index') }}" variant="secondary" class="w-full sm:w-auto">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            Clear Filters
                        </x-button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Job Applications Table -->
    <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow overflow-hidden sm:rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]" x-data="{}">
        <div class="px-4 py-5 sm:p-6">
            @if($applications->count() > 0)
                <!-- Mobile Card Layout (visible on small screens) -->
                <div class="block sm:hidden space-y-4">
                    @foreach($applications as $application)
                        <div class="bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded-lg p-4 space-y-3">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <h3 class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                        {{ $application->job_title }}
                                    </h3>
                                    <p class="text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] mt-1">
                                        {{ $application->company_name }}
                                    </p>
                                </div>
                                <x-job-application.priority-badge :priority="$application->priority" />
                            </div>

                            <div class="flex items-center gap-2">
                                <x-job-application.status-badge :status="$application->status" />
                                @if($application->remote)
                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        Remote
                                    </span>
                                @endif
                            </div>

                            @if($application->location)
                                <div class="flex items-center text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                    {{ $application->location }}
                                </div>
                            @endif

                            @if($application->applied_at)
                                <div class="text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">
                                    Applied: {{ $application->applied_at->format('M d, Y') }}
                                    @if($application->days_since_applied)
                                        ({{ $application->days_since_applied }} days ago)
                                    @endif
                                </div>
                            @endif

                            @if($application->next_action_at)
                                <div class="flex items-center text-sm {{ $application->next_action_at->isPast() ? 'text-red-600 dark:text-red-400 font-medium' : 'text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]' }}">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Next action: {{ $application->next_action_at->format('M d, Y') }}
                                </div>
                            @endif

                            @if($application->formatted_salary_range)
                                <div class="text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">
                                    💰 {{ $application->formatted_salary_range }}
                                </div>
                            @endif

                            <div class="flex gap-2 pt-2">
                                <x-button href="{{ route('job-applications.show', $application) }}" variant="secondary" size="sm" class="flex-1">
                                    View Details
                                </x-button>
                                <x-button href="{{ route('job-applications.edit', $application) }}" variant="primary" size="sm" class="flex-1">
                                    Edit
                                </x-button>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Desktop Table Layout (hidden on small screens) -->
                <div class="hidden sm:block overflow-x-auto">
                    <table class="min-w-full divide-y divide-[color:var(--color-primary-300)] dark:divide-[color:var(--color-dark-300)]">
                        <thead class="bg-[color:var(--color-primary-200)] dark:bg-[color:var(--color-dark-300)]">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] uppercase tracking-wider">
                                    Company / Job Title
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] uppercase tracking-wider">
                                    Status
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] uppercase tracking-wider">
                                    Source
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] uppercase tracking-wider">
                                    Applied Date
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] uppercase tracking-wider">
                                    Next Action
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] uppercase tracking-wider">
                                    Priority
                                </th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] divide-y divide-[color:var(--color-primary-300)] dark:divide-[color:var(--color-dark-300)]">
                            @foreach($applications as $application)
                                <tr class="hover:bg-[color:var(--color-primary-100)] dark:hover:bg-[color:var(--color-dark-200)] transition-colors duration-150">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div>
                                                <div class="text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                                    {{ $application->company_name }}
                                                </div>
                                                <div class="text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">
                                                    {{ $application->job_title }}
                                                </div>
                                                @if($application->location)
                                                    <div class="text-xs text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-400)] mt-1 flex items-center">
                                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                        </svg>
                                                        {{ $application->location }}
                                                        @if($application->remote)
                                                            <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                                                Remote
                                                            </span>
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <x-job-application.status-badge :status="$application->status" />
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">
                                        {{ $application->source->label() }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">
                                        @if($application->applied_at)
                                            {{ $application->applied_at->format('M d, Y') }}
                                            @if($application->days_since_applied)
                                                <div class="text-xs text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-400)]">
                                                    {{ $application->days_since_applied }} days ago
                                                </div>
                                            @endif
                                        @else
                                            <span class="text-[color:var(--color-primary-400)] dark:text-[color:var(--color-dark-400)]">Not applied yet</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        @if($application->next_action_at)
                                            <div class="{{ $application->next_action_at->isPast() ? 'text-red-600 dark:text-red-400 font-medium' : 'text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]' }}">
                                                {{ $application->next_action_at->format('M d, Y') }}
                                                @if($application->next_action_at->isPast())
                                                    <div class="text-xs">Overdue!</div>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-[color:var(--color-primary-400)] dark:text-[color:var(--color-dark-400)]">—</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <x-job-application.priority-badge :priority="$application->priority" />
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex justify-end gap-2">
                                            <a href="{{ route('job-applications.show', $application) }}" class="text-[color:var(--color-accent-500)] hover:text-[color:var(--color-accent-600)] dark:text-[color:var(--color-accent-400)] dark:hover:text-[color:var(--color-accent-500)]">
                                                View
                                            </a>
                                            <a href="{{ route('job-applications.edit', $application) }}" class="text-[color:var(--color-accent-500)] hover:text-[color:var(--color-accent-600)] dark:text-[color:var(--color-accent-400)] dark:hover:text-[color:var(--color-accent-500)]">
                                                Edit
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-6">
                    {{ $applications->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-[color:var(--color-primary-400)] dark:text-[color:var(--color-dark-400)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">No job applications found</h3>
                    <p class="mt-1 text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">
                        @if(request()->hasAny(['search', 'status', 'source', 'priority']))
                            Try adjusting your filters or search criteria.
                        @else
                            Get started by creating your first job application.
                        @endif
                    </p>
                    <div class="mt-6">
                        @if(request()->hasAny(['search', 'status', 'source', 'priority']))
                            <x-button href="{{ route('job-applications.index') }}" variant="primary">
                                Clear Filters
                            </x-button>
                        @else
                            <x-button href="{{ route('job-applications.create') }}" variant="primary">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                Add Your First Application
                            </x-button>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
