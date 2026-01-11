@extends('layouts.app')

@section('title', 'Project Investments - LifeOS')

@section('header')
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                Project Investments
            </h1>
            <p class="mt-2 text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">
                Track your project and startup investments
            </p>
        </div>
        <div class="flex flex-col sm:flex-row gap-3 sm:gap-2 flex-shrink-0">
            <x-button href="{{ route('project-investments.analytics') }}" variant="secondary" class="w-full sm:w-auto">Analytics</x-button>
            <x-button href="{{ route('project-investments.create') }}" variant="primary" class="w-full sm:w-auto">Add Project</x-button>
        </div>
    </div>
@endsection

@section('content')
    <!-- Summary Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow rounded-lg p-4 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
            <div class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Total Projects</div>
            <div class="text-2xl font-bold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">{{ $summary['total_projects'] }}</div>
        </div>
        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow rounded-lg p-4 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
            <div class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Active Projects</div>
            <div class="text-2xl font-bold text-[color:var(--color-success-600)] dark:text-green-400">{{ $summary['active_projects'] }}</div>
        </div>
        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow rounded-lg p-4 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
            <div class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Total Invested</div>
            <div class="text-2xl font-bold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">{{ app(\App\Services\CurrencyService::class)->format($summary['total_invested']) }}</div>
        </div>
        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow rounded-lg p-4 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
            <div class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Total Gain/Loss</div>
            <div class="text-2xl font-bold {{ $summary['total_gain_loss'] >= 0 ? 'text-[color:var(--color-success-600)] dark:text-green-400' : 'text-[color:var(--color-danger-600)] dark:text-[color:var(--color-danger-400)]' }}">
                {{ $summary['total_gain_loss'] >= 0 ? '+' : '' }}{{ app(\App\Services\CurrencyService::class)->format($summary['total_gain_loss']) }}
            </div>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow rounded-lg mb-6 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
        <div class="px-4 py-5 sm:px-6">
            <form method="GET" action="{{ route('project-investments.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Search -->
                <div>
                    <x-form.input
                        name="search"
                        label="Search"
                        type="text"
                        placeholder="Search projects..."
                        :value="request('search')"
                    />
                </div>

                <!-- Stage Filter -->
                <div>
                    <x-form.select
                        name="stage"
                        label="Stage"
                        placeholder="All Stages"
                    >
                        <option value="idea" {{ request('stage') === 'idea' ? 'selected' : '' }}>Idea</option>
                        <option value="prototype" {{ request('stage') === 'prototype' ? 'selected' : '' }}>Prototype</option>
                        <option value="mvp" {{ request('stage') === 'mvp' ? 'selected' : '' }}>MVP</option>
                        <option value="growth" {{ request('stage') === 'growth' ? 'selected' : '' }}>Growth</option>
                        <option value="mature" {{ request('stage') === 'mature' ? 'selected' : '' }}>Mature</option>
                    </x-form.select>
                </div>

                <!-- Business Model Filter -->
                <div>
                    <x-form.select
                        name="business_model"
                        label="Business Model"
                        placeholder="All Models"
                    >
                        <option value="subscription" {{ request('business_model') === 'subscription' ? 'selected' : '' }}>Subscription</option>
                        <option value="ads" {{ request('business_model') === 'ads' ? 'selected' : '' }}>Advertising</option>
                        <option value="one-time" {{ request('business_model') === 'one-time' ? 'selected' : '' }}>One-time</option>
                        <option value="freemium" {{ request('business_model') === 'freemium' ? 'selected' : '' }}>Freemium</option>
                    </x-form.select>
                </div>

                <!-- Status Filter -->
                <div>
                    <x-form.select
                        name="status"
                        label="Status"
                        placeholder="All Statuses"
                    >
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="sold" {{ request('status') === 'sold' ? 'selected' : '' }}>Sold</option>
                        <option value="abandoned" {{ request('status') === 'abandoned' ? 'selected' : '' }}>Abandoned</option>
                    </x-form.select>
                </div>

                <div class="col-span-full">
                    <x-button type="submit" variant="primary">Apply Filters</x-button>
                    <x-button href="{{ route('project-investments.index') }}" variant="secondary" class="ml-2">Clear</x-button>
                </div>
            </form>
        </div>
    </div>

    <!-- Projects Table -->
    <div class="bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] shadow overflow-hidden sm:rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]" x-data="{}">
        <div class="px-4 py-5 sm:p-6">
            @if($projectInvestments->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-[color:var(--color-primary-300)] dark:divide-[color:var(--color-dark-300)]">
                        <thead class="bg-[color:var(--color-primary-200)] dark:bg-[color:var(--color-dark-200)]">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">Project</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">Stage</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">Equity</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">Invested</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">Current Value</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">Performance</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] divide-y divide-[color:var(--color-primary-300)] dark:divide-[color:var(--color-dark-300)]">
                            @foreach($projectInvestments as $project)
                                <tr class="hover:bg-[color:var(--color-primary-100)] dark:hover:bg-[color:var(--color-dark-200)]">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div>
                                                <div class="text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                                    {{ $project->name }}
                                                </div>
                                                <div class="text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">
                                                    {{ $project->project_type ?? 'Project' }}
                                                    @if($project->business_model)
                                                        · {{ $project->business_model_label }}
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($project->stage)
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-[color:var(--color-info-50)] text-[color:var(--color-info-600)] dark:bg-[color:var(--color-info-600)] dark:text-[color:var(--color-info-50)]">
                                                {{ $project->stage_label }}
                                            </span>
                                        @else
                                            <span class="text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                        @if($project->equity_percentage)
                                            {{ number_format($project->equity_percentage, 2) }}%
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                        {{ $project->formatted_investment_amount }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                        {{ $project->formatted_current_value }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        @php
                                            $gainLoss = $project->gain_loss;
                                            $gainLossPercent = $project->gain_loss_percentage;
                                        @endphp
                                        <div class="flex items-center">
                                            <span class="font-medium {{ $gainLoss >= 0 ? 'text-green-600 dark:text-green-400' : 'text-[color:var(--color-danger-600)] dark:text-[color:var(--color-danger-400)]' }}">
                                                {{ $gainLoss >= 0 ? '+' : '' }}{{ $project->formatted_gain_loss }}
                                            </span>
                                            <span class="ml-2 text-xs {{ $gainLoss >= 0 ? 'text-green-600 dark:text-green-400' : 'text-[color:var(--color-danger-600)] dark:text-[color:var(--color-danger-400)]' }}">
                                                ({{ $gainLoss >= 0 ? '+' : '' }}{{ number_format($gainLossPercent, 1) }}%)
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <a href="{{ route('project-investments.show', $project) }}" class="text-[color:var(--color-accent-500)] hover:text-[color:var(--color-accent-600)] dark:text-[color:var(--color-accent-500)] dark:hover:text-[color:var(--color-accent-600)] transition-colors duration-200">View</a>
                                            <a href="{{ route('project-investments.edit', $project) }}" class="text-[color:var(--color-accent-500)] hover:text-[color:var(--color-accent-600)] dark:text-[color:var(--color-accent-500)] dark:hover:text-[color:var(--color-accent-600)] transition-colors duration-200">Edit</a>
                                            <button type="button"
                                                    class="text-[color:var(--color-danger-500)] hover:text-[color:var(--color-danger-600)] dark:text-[color:var(--color-danger-500)] dark:hover:text-[color:var(--color-danger-600)] transition-colors duration-200"
                                                    x-on:click="$dispatch('open-modal', { id: 'deleteProjectModal-{{ $project->id }}' })">
                                                Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-6">
                    {{ $projectInvestments->appends(request()->query())->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <div class="text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-400)] mb-4">
                        <svg class="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-2">No project investments found</h3>
                    <p class="text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] mb-4">Get started by adding your first project investment.</p>
                    <x-button href="{{ route('project-investments.create') }}" variant="primary">Add Project</x-button>
                </div>
            @endif
        </div>
    </div>

    <!-- Delete Modals for each project -->
    @foreach($projectInvestments as $project)
        <x-confirmation-modal
            id="deleteProjectModal-{{ $project->id }}"
            title="Delete Project Investment"
            message="Are you sure you want to delete the project '{{ $project->name }}'? This action cannot be undone."
            confirm-text="Delete"
            confirm-button-class="bg-[color:var(--color-danger-500)] hover:bg-[color:var(--color-danger-600)] text-white"
            :action="route('project-investments.destroy', $project)"
            method="DELETE"
        />
    @endforeach
@endsection
