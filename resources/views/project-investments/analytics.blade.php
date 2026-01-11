@extends('layouts.app')

@section('title', 'Project Investments Analytics - LifeOS')

@section('header')
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                Project Investments Analytics
            </h1>
            <p class="mt-2 text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">
                Overview of your project investment portfolio
            </p>
        </div>
        <div class="flex flex-col sm:flex-row gap-3 sm:gap-2 flex-shrink-0">
            <x-button href="{{ route('project-investments.index') }}" variant="secondary" class="w-full sm:w-auto">View All Projects</x-button>
            <x-button href="{{ route('project-investments.create') }}" variant="primary" class="w-full sm:w-auto">Add Project</x-button>
        </div>
    </div>
@endsection

@section('content')
    <!-- Summary Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow rounded-lg p-6 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
            <div class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Total Projects</div>
            <div class="text-3xl font-bold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mt-2">{{ $analytics['total_projects'] }}</div>
            <div class="text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] mt-1">
                {{ $analytics['active_projects'] }} active
            </div>
        </div>
        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow rounded-lg p-6 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
            <div class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Total Invested</div>
            <div class="text-3xl font-bold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mt-2">{{ app(\App\Services\CurrencyService::class)->format($analytics['total_invested']) }}</div>
        </div>
        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow rounded-lg p-6 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
            <div class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Current Value</div>
            <div class="text-3xl font-bold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mt-2">{{ app(\App\Services\CurrencyService::class)->format($analytics['total_current_value']) }}</div>
        </div>
        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow rounded-lg p-6 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
            <div class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Total Gain/Loss</div>
            <div class="text-3xl font-bold {{ $analytics['total_gain_loss'] >= 0 ? 'text-[color:var(--color-success-600)] dark:text-green-400' : 'text-[color:var(--color-danger-600)] dark:text-[color:var(--color-danger-400)]' }} mt-2">
                {{ $analytics['total_gain_loss'] >= 0 ? '+' : '' }}{{ app(\App\Services\CurrencyService::class)->format($analytics['total_gain_loss']) }}
            </div>
            @if($analytics['total_invested'] > 0)
                <div class="text-sm {{ $analytics['total_gain_loss'] >= 0 ? 'text-[color:var(--color-success-600)] dark:text-green-400' : 'text-[color:var(--color-danger-600)] dark:text-[color:var(--color-danger-400)]' }} mt-1">
                    {{ $analytics['total_gain_loss'] >= 0 ? '+' : '' }}{{ number_format(($analytics['total_gain_loss'] / $analytics['total_invested']) * 100, 1) }}%
                </div>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- By Status -->
        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow rounded-lg p-6 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
            <h3 class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-4">Projects by Status</h3>
            <div class="space-y-4">
                <div class="flex justify-between items-center">
                    <span class="text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">Active</span>
                    <span class="font-semibold text-[color:var(--color-success-600)] dark:text-green-400">{{ $analytics['active_projects'] }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">Completed</span>
                    <span class="font-semibold text-[color:var(--color-info-600)] dark:text-blue-400">{{ $analytics['completed_projects'] }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">Sold</span>
                    <span class="font-semibold text-[color:var(--color-warning-600)] dark:text-yellow-400">{{ $analytics['sold_projects'] }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">Abandoned</span>
                    <span class="font-semibold text-[color:var(--color-danger-600)] dark:text-[color:var(--color-danger-400)]">{{ $analytics['abandoned_projects'] }}</span>
                </div>
            </div>
        </div>

        <!-- By Stage -->
        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow rounded-lg p-6 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
            <h3 class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-4">Projects by Stage</h3>
            @if($analytics['by_stage']->count() > 0)
                <div class="space-y-4">
                    @foreach($analytics['by_stage'] as $stage => $data)
                        @if($stage)
                            <div class="flex justify-between items-center">
                                <span class="text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">{{ ucfirst($stage) }}</span>
                                <div class="text-right">
                                    <span class="font-semibold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">{{ $data['count'] }} projects</span>
                                    <span class="text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] ml-2">
                                        ({{ app(\App\Services\CurrencyService::class)->format($data['invested']) }})
                                    </span>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            @else
                <p class="text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">No stage data available.</p>
            @endif
        </div>

        <!-- By Business Model -->
        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow rounded-lg p-6 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
            <h3 class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-4">Projects by Business Model</h3>
            @if($analytics['by_business_model']->count() > 0)
                <div class="space-y-4">
                    @foreach($analytics['by_business_model'] as $model => $data)
                        @if($model)
                            <div class="flex justify-between items-center">
                                <span class="text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">{{ ucfirst(str_replace('-', ' ', $model)) }}</span>
                                <div class="text-right">
                                    <span class="font-semibold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">{{ $data['count'] }} projects</span>
                                    <span class="text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] ml-2">
                                        ({{ app(\App\Services\CurrencyService::class)->format($data['invested']) }})
                                    </span>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            @else
                <p class="text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">No business model data available.</p>
            @endif
        </div>

        <!-- By Project Type -->
        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow rounded-lg p-6 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
            <h3 class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-4">Projects by Type</h3>
            @if($analytics['by_project_type']->count() > 0)
                <div class="space-y-4">
                    @foreach($analytics['by_project_type'] as $type => $data)
                        @if($type)
                            <div class="flex justify-between items-center">
                                <span class="text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">{{ $type }}</span>
                                <div class="text-right">
                                    <span class="font-semibold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">{{ $data['count'] }} projects</span>
                                    <span class="text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] ml-2">
                                        ({{ app(\App\Services\CurrencyService::class)->format($data['invested']) }})
                                    </span>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            @else
                <p class="text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">No project type data available.</p>
            @endif
        </div>
    </div>

    <!-- All Projects List -->
    @if($analytics['projects']->count() > 0)
        <div class="mt-8 bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow rounded-lg p-6 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
            <h3 class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-4">All Projects (by Investment Amount)</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-[color:var(--color-primary-300)] dark:divide-[color:var(--color-dark-300)]">
                    <thead>
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">Project</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">Stage</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">Invested</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">Current Value</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">Gain/Loss</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[color:var(--color-primary-200)] dark:divide-[color:var(--color-dark-300)]">
                        @foreach($analytics['projects'] as $project)
                            <tr class="hover:bg-[color:var(--color-primary-50)] dark:hover:bg-[color:var(--color-dark-100)]">
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <a href="{{ route('project-investments.show', $project) }}" class="text-[color:var(--color-accent-600)] hover:text-[color:var(--color-accent-700)] font-medium">
                                        {{ $project->name }}
                                    </a>
                                    @if($project->project_type)
                                        <div class="text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">{{ $project->project_type }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    @if($project->stage)
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-[color:var(--color-info-50)] text-[color:var(--color-info-600)] dark:bg-[color:var(--color-info-900)] dark:text-[color:var(--color-info-200)]">
                                            {{ $project->stage_label }}
                                        </span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                    {{ $project->formatted_investment_amount }}
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                    {{ $project->formatted_current_value }}
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    @php
                                        $gainLoss = $project->gain_loss;
                                        $gainLossPercent = $project->gain_loss_percentage;
                                    @endphp
                                    <span class="{{ $gainLoss >= 0 ? 'text-[color:var(--color-success-600)] dark:text-green-400' : 'text-[color:var(--color-danger-600)] dark:text-[color:var(--color-danger-400)]' }}">
                                        {{ $gainLoss >= 0 ? '+' : '' }}{{ $project->formatted_gain_loss }}
                                        ({{ $gainLoss >= 0 ? '+' : '' }}{{ number_format($gainLossPercent, 1) }}%)
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endsection
