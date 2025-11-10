@extends('layouts.app')

@section('title', 'Job Applications Kanban - LifeOS')

@section('header')
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                Job Applications Kanban
            </h1>
            <p class="mt-2 text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">
                Drag and drop applications to update their status
            </p>
        </div>
        <div class="flex gap-2 flex-shrink-0">
            <a href="{{ route('job-applications.index') }}" class="w-full sm:w-auto inline-flex justify-center items-center bg-[color:var(--color-primary-200)] hover:bg-[color:var(--color-primary-300)] text-[color:var(--color-primary-700)] dark:bg-[color:var(--color-dark-300)] dark:hover:bg-[color:var(--color-dark-400)] dark:text-[color:var(--color-dark-600)] px-6 py-3 sm:px-4 sm:py-2 rounded-lg sm:rounded-md text-base sm:text-sm font-medium transition-colors duration-200 touch-manipulation">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                </svg>
                List View
            </a>
            <a href="{{ route('job-applications.create') }}" class="w-full sm:w-auto inline-flex justify-center items-center bg-[color:var(--color-accent-500)] hover:bg-[color:var(--color-accent-600)] text-white px-6 py-3 sm:px-4 sm:py-2 rounded-lg sm:rounded-md text-base sm:text-sm font-medium transition-colors duration-200 shadow-sm touch-manipulation">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Add Application
            </a>
        </div>
    </div>
@endsection

@section('content')
    <!-- Filters -->
    <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow rounded-lg mb-6 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
        <div class="px-4 py-5 sm:px-6">
            <form method="GET" action="{{ route('job-applications.kanban') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
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

                <div class="flex items-end">
                    <button type="submit" class="w-full inline-flex justify-center items-center bg-[color:var(--color-accent-500)] hover:bg-[color:var(--color-accent-600)] text-white px-6 py-3 sm:px-4 sm:py-2 rounded-lg sm:rounded-md text-base sm:text-sm font-medium transition-colors duration-200 shadow-sm touch-manipulation">
                        Apply Filters
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Kanban Board -->
    <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] p-4"
         x-data="kanbanBoard()"
         x-init="init()">

        <!-- Desktop: Horizontal scrolling columns -->
        <div class="hidden md:block overflow-x-auto pb-4">
            <div class="flex gap-4 min-w-max">
                @foreach($columns as $columnKey => $column)
                    <div class="flex-shrink-0 w-80">
                        <!-- Column Header -->
                        <div class="bg-[color:var(--color-primary-200)] dark:bg-[color:var(--color-dark-300)] rounded-t-lg px-4 py-3 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $column['color'] }}-100 text-{{ $column['color'] }}-800 dark:bg-{{ $column['color'] }}-900 dark:text-{{ $column['color'] }}-200">
                                    {{ $column['label'] }}
                                </span>
                                <span class="text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">
                                    ({{ $column['applications']->count() }})
                                </span>
                            </div>
                        </div>

                        <!-- Column Body -->
                        <div class="bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] rounded-b-lg p-3 min-h-[600px] space-y-3"
                             data-status="{{ $columnKey }}"
                             @drop.prevent="handleDrop($event, '{{ $columnKey }}')"
                             @dragover.prevent
                             @dragenter.prevent="handleDragEnter($event)"
                             @dragleave="handleDragLeave($event)">

                            @forelse($column['applications'] as $application)
                                <!-- Application Card -->
                                <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] rounded-lg shadow-sm border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] p-4 cursor-move hover:shadow-md transition-shadow duration-200"
                                     draggable="true"
                                     data-application-id="{{ $application->id }}"
                                     @dragstart="handleDragStart($event, {{ $application->id }})"
                                     @dragend="handleDragEnd($event)">

                                    <div class="flex items-start justify-between mb-2">
                                        <h4 class="text-sm font-semibold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] flex-1">
                                            {{ $application->company_name }}
                                        </h4>
                                        <x-job-application.priority-badge :priority="$application->priority" />
                                    </div>

                                    <p class="text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] mb-3">
                                        {{ $application->job_title }}
                                    </p>

                                    @if($application->location)
                                        <div class="flex items-center text-xs text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-400)] mb-2">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            </svg>
                                            {{ $application->location }}
                                            @if($application->remote)
                                                <span class="ml-1 text-blue-600 dark:text-blue-400">• Remote</span>
                                            @endif
                                        </div>
                                    @endif

                                    @if($application->formatted_salary_range)
                                        <div class="text-xs text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] mb-2">
                                            💰 {{ $application->formatted_salary_range }}
                                        </div>
                                    @endif

                                    <div class="flex items-center justify-between text-xs text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-400)] pt-2 border-t border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)]">
                                        <span>{{ $application->days_in_current_status }} days in stage</span>
                                        @if($application->interviews->count() > 0)
                                            <span class="inline-flex items-center">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                </svg>
                                                {{ $application->interviews->count() }} interview{{ $application->interviews->count() > 1 ? 's' : '' }}
                                            </span>
                                        @endif
                                    </div>

                                    <div class="mt-3 flex gap-2">
                                        <a href="{{ route('job-applications.show', $application) }}" class="flex-1 text-center px-3 py-1.5 bg-[color:var(--color-primary-200)] hover:bg-[color:var(--color-primary-300)] text-[color:var(--color-primary-700)] dark:bg-[color:var(--color-dark-300)] dark:hover:bg-[color:var(--color-dark-400)] dark:text-[color:var(--color-dark-600)] rounded text-xs font-medium transition-colors">
                                            View
                                        </a>
                                        <a href="{{ route('job-applications.edit', $application) }}" class="flex-1 text-center px-3 py-1.5 bg-[color:var(--color-accent-500)] hover:bg-[color:var(--color-accent-600)] text-white rounded text-xs font-medium transition-colors">
                                            Edit
                                        </a>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-8 text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-400)] text-sm">
                                    <svg class="w-8 h-8 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    No applications
                                </div>
                            @endforelse
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Mobile: Vertical stacked columns -->
        <div class="md:hidden space-y-6">
            @foreach($columns as $columnKey => $column)
                <div class="bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
                    <!-- Column Header -->
                    <div class="bg-[color:var(--color-primary-200)] dark:bg-[color:var(--color-dark-300)] px-4 py-3 flex items-center justify-between rounded-t-lg">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $column['color'] }}-100 text-{{ $column['color'] }}-800 dark:bg-{{ $column['color'] }}-900 dark:text-{{ $column['color'] }}-200">
                                {{ $column['label'] }}
                            </span>
                            <span class="text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">
                                ({{ $column['applications']->count() }})
                            </span>
                        </div>
                    </div>

                    <!-- Column Body -->
                    <div class="p-3 space-y-3">
                        @forelse($column['applications'] as $application)
                            <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] rounded-lg shadow-sm border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] p-4">
                                <div class="flex items-start justify-between mb-2">
                                    <h4 class="text-sm font-semibold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] flex-1">
                                        {{ $application->company_name }}
                                    </h4>
                                    <x-job-application.priority-badge :priority="$application->priority" />
                                </div>

                                <p class="text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] mb-3">
                                    {{ $application->job_title }}
                                </p>

                                @if($application->location)
                                    <div class="flex items-center text-xs text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-400)] mb-2">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                        {{ $application->location }}
                                        @if($application->remote)
                                            <span class="ml-1 text-blue-600 dark:text-blue-400">• Remote</span>
                                        @endif
                                    </div>
                                @endif

                                @if($application->formatted_salary_range)
                                    <div class="text-xs text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] mb-2">
                                        💰 {{ $application->formatted_salary_range }}
                                    </div>
                                @endif

                                <div class="flex items-center justify-between text-xs text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-400)] pt-2 border-t border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)]">
                                    <span>{{ $application->days_in_current_status }} days in stage</span>
                                    @if($application->interviews->count() > 0)
                                        <span class="inline-flex items-center">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                            {{ $application->interviews->count() }} interview{{ $application->interviews->count() > 1 ? 's' : '' }}
                                        </span>
                                    @endif
                                </div>

                                <!-- Mobile status selector -->
                                <div class="mt-3">
                                    <select class="w-full px-3 py-2 text-xs border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded-md bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]"
                                            @change="updateStatus({{ $application->id }}, $event.target.value)">
                                        @foreach($columns as $statusKey => $statusColumn)
                                            <option value="{{ $statusKey }}" {{ $statusKey === $columnKey ? 'selected' : '' }}>
                                                Move to {{ $statusColumn['label'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mt-3 flex gap-2">
                                    <a href="{{ route('job-applications.show', $application) }}" class="flex-1 text-center px-3 py-1.5 bg-[color:var(--color-primary-200)] hover:bg-[color:var(--color-primary-300)] text-[color:var(--color-primary-700)] dark:bg-[color:var(--color-dark-300)] dark:hover:bg-[color:var(--color-dark-400)] dark:text-[color:var(--color-dark-600)] rounded text-xs font-medium transition-colors">
                                        View
                                    </a>
                                    <a href="{{ route('job-applications.edit', $application) }}" class="flex-1 text-center px-3 py-1.5 bg-[color:var(--color-accent-500)] hover:bg-[color:var(--color-accent-600)] text-white rounded text-xs font-medium transition-colors">
                                        Edit
                                    </a>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-8 text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-400)] text-sm">
                                No applications
                            </div>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Success/Error Messages -->
        <div x-show="message"
             x-transition
             class="fixed bottom-4 right-4 px-6 py-3 rounded-lg shadow-lg z-50"
             :class="messageType === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'"
             x-cloak>
            <span x-text="message"></span>
        </div>
    </div>

    @push('scripts')
    <script>
        function kanbanBoard() {
            return {
                draggedApplicationId: null,
                message: '',
                messageType: 'success',

                init() {
                    // Initialization if needed
                },

                handleDragStart(event, applicationId) {
                    this.draggedApplicationId = applicationId;
                    event.target.classList.add('opacity-50');
                },

                handleDragEnd(event) {
                    event.target.classList.remove('opacity-50');
                    // Remove all drag-over styles
                    document.querySelectorAll('.drag-over').forEach(el => {
                        el.classList.remove('drag-over');
                    });
                },

                handleDragEnter(event) {
                    if (event.target.hasAttribute('data-status')) {
                        event.target.classList.add('bg-[color:var(--color-primary-200)]', 'dark:bg-[color:var(--color-dark-400)]', 'drag-over');
                    }
                },

                handleDragLeave(event) {
                    if (event.target.hasAttribute('data-status')) {
                        event.target.classList.remove('bg-[color:var(--color-primary-200)]', 'dark:bg-[color:var(--color-dark-400)]', 'drag-over');
                    }
                },

                async handleDrop(event, newStatus) {
                    const column = event.target.closest('[data-status]');
                    if (column) {
                        column.classList.remove('bg-[color:var(--color-primary-200)]', 'dark:bg-[color:var(--color-dark-400)]', 'drag-over');
                    }

                    if (!this.draggedApplicationId) return;

                    await this.updateStatus(this.draggedApplicationId, newStatus);
                },

                async updateStatus(applicationId, newStatus) {
                    try {
                        const response = await fetch(`/job-applications/${applicationId}/kanban-status`, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ status: newStatus })
                        });

                        const data = await response.json();

                        if (response.ok) {
                            this.showMessage(data.message || 'Application moved successfully!', 'success');
                            // Reload page to update the board
                            setTimeout(() => window.location.reload(), 500);
                        } else {
                            this.showMessage(data.error || 'Failed to update status', 'error');
                        }
                    } catch (error) {
                        this.showMessage('An error occurred while updating status', 'error');
                        console.error('Error:', error);
                    }
                },

                showMessage(msg, type) {
                    this.message = msg;
                    this.messageType = type;
                    setTimeout(() => {
                        this.message = '';
                    }, 3000);
                }
            }
        }
    </script>
    @endpush
@endsection
