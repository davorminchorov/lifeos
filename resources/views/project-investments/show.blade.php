@extends('layouts.app')

@section('title', $projectInvestment->name . ' - Project Investments - LifeOS')

@section('header')
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                {{ $projectInvestment->name }}
            </h1>
            <p class="mt-2 text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">
                {{ $projectInvestment->project_type ?? 'Project' }} Investment
                @if($projectInvestment->stage)
                    - {{ $projectInvestment->stage_label }}
                @endif
            </p>
        </div>
        <div class="flex flex-col sm:flex-row gap-2 sm:gap-3">
            <x-button href="{{ route('project-investments.edit', $projectInvestment) }}" variant="primary" class="w-full sm:w-auto">Edit</x-button>
            <x-button href="{{ route('project-investments.index') }}" variant="secondary" class="w-full sm:w-auto">Back to List</x-button>
        </div>
    </div>
@endsection

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Performance Overview -->
        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                    Investment Overview
                </h3>
                <p class="mt-1 max-w-2xl text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">
                    Current value and returns.
                </p>
            </div>
            <div class="border-t border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)]">
                <dl>
                    <div class="bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Investment Amount</dt>
                        <dd class="mt-1 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] sm:mt-0 sm:col-span-2">
                            <div class="text-lg font-semibold">{{ $projectInvestment->formatted_investment_amount }}</div>
                        </dd>
                    </div>
                    <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Current Value</dt>
                        <dd class="mt-1 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] sm:mt-0 sm:col-span-2">
                            <div class="text-lg font-semibold">{{ $projectInvestment->formatted_current_value }}</div>
                        </dd>
                    </div>
                    <div class="bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Gain/Loss</dt>
                        <dd class="mt-1 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] sm:mt-0 sm:col-span-2">
                            @php
                                $gainLoss = $projectInvestment->gain_loss;
                                $gainLossPercent = $projectInvestment->gain_loss_percentage;
                            @endphp
                            <div class="text-lg font-semibold {{ $gainLoss >= 0 ? 'text-[color:var(--color-success-600)] dark:text-green-400' : 'text-[color:var(--color-danger-600)] dark:text-[color:var(--color-danger-400)]' }}">
                                {{ $gainLoss >= 0 ? '+' : '' }}{{ $projectInvestment->formatted_gain_loss }}
                                ({{ $gainLoss >= 0 ? '+' : '' }}{{ number_format($gainLossPercent, 2) }}%)
                            </div>
                        </dd>
                    </div>
                    @if($projectInvestment->equity_percentage)
                        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Equity Stake</dt>
                            <dd class="mt-1 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] sm:mt-0 sm:col-span-2">
                                <div class="text-lg font-semibold">{{ number_format($projectInvestment->equity_percentage, 2) }}%</div>
                            </dd>
                        </div>
                    @endif
                </dl>
            </div>
        </div>

        <!-- Project Details -->
        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                    Project Details
                </h3>
                <p class="mt-1 max-w-2xl text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">
                    Information about this project.
                </p>
            </div>
            <div class="border-t border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)]">
                <dl>
                    <div class="bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Status</dt>
                        <dd class="mt-1 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] sm:mt-0 sm:col-span-2">
                            @if($projectInvestment->status === 'active')
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-[color:var(--color-success-50)] text-[color:var(--color-success-600)] dark:bg-[color:var(--color-success-900)] dark:text-[color:var(--color-success-200)]">
                                    Active
                                </span>
                            @elseif($projectInvestment->status === 'completed')
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-[color:var(--color-info-50)] text-[color:var(--color-info-600)] dark:bg-[color:var(--color-info-900)] dark:text-[color:var(--color-info-200)]">
                                    Completed
                                </span>
                            @elseif($projectInvestment->status === 'sold')
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-[color:var(--color-warning-50)] text-[color:var(--color-warning-600)] dark:bg-[color:var(--color-warning-900)] dark:text-[color:var(--color-warning-200)]">
                                    Sold
                                </span>
                            @else
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-[color:var(--color-danger-50)] text-[color:var(--color-danger-600)] dark:bg-[color:var(--color-danger-900)] dark:text-[color:var(--color-danger-200)]">
                                    Abandoned
                                </span>
                            @endif
                        </dd>
                    </div>
                    @if($projectInvestment->project_type)
                        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Project Type</dt>
                            <dd class="mt-1 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] sm:mt-0 sm:col-span-2">{{ $projectInvestment->project_type }}</dd>
                        </div>
                    @endif
                    @if($projectInvestment->stage)
                        <div class="bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Stage</dt>
                            <dd class="mt-1 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] sm:mt-0 sm:col-span-2">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-[color:var(--color-info-50)] text-[color:var(--color-info-600)] dark:bg-[color:var(--color-info-900)] dark:text-[color:var(--color-info-200)]">
                                    {{ $projectInvestment->stage_label }}
                                </span>
                            </dd>
                        </div>
                    @endif
                    @if($projectInvestment->business_model)
                        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Business Model</dt>
                            <dd class="mt-1 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] sm:mt-0 sm:col-span-2">{{ $projectInvestment->business_model_label }}</dd>
                        </div>
                    @endif
                    @if($projectInvestment->start_date)
                        <div class="bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Start Date</dt>
                            <dd class="mt-1 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] sm:mt-0 sm:col-span-2">{{ $projectInvestment->start_date->format('M j, Y') }}</dd>
                        </div>
                    @endif
                    @if($projectInvestment->end_date)
                        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">End Date</dt>
                            <dd class="mt-1 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] sm:mt-0 sm:col-span-2">{{ $projectInvestment->end_date->format('M j, Y') }}</dd>
                        </div>
                    @endif
                </dl>
            </div>
        </div>

        <!-- Links -->
        @if($projectInvestment->website_url || $projectInvestment->repository_url)
            <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow overflow-hidden sm:rounded-lg">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                        Project Links
                    </h3>
                </div>
                <div class="border-t border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)]">
                    <dl>
                        @if($projectInvestment->website_url)
                            <div class="bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Website</dt>
                                <dd class="mt-1 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] sm:mt-0 sm:col-span-2">
                                    <a href="{{ $projectInvestment->website_url }}" target="_blank" class="text-[color:var(--color-info-600)] hover:text-[color:var(--color-info-700)] dark:text-blue-400 dark:hover:text-blue-300">
                                        {{ $projectInvestment->website_url }}
                                    </a>
                                </dd>
                            </div>
                        @endif
                        @if($projectInvestment->repository_url)
                            <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Repository</dt>
                                <dd class="mt-1 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] sm:mt-0 sm:col-span-2">
                                    <a href="{{ $projectInvestment->repository_url }}" target="_blank" class="text-[color:var(--color-info-600)] hover:text-[color:var(--color-info-700)] dark:text-blue-400 dark:hover:text-blue-300">
                                        {{ $projectInvestment->repository_url }}
                                    </a>
                                </dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>
        @endif

        <!-- Notes -->
        @if($projectInvestment->notes)
            <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow overflow-hidden sm:rounded-lg">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                        Notes
                    </h3>
                </div>
                <div class="border-t border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)] px-4 py-5 sm:px-6">
                    <p class="text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] whitespace-pre-wrap">{{ $projectInvestment->notes }}</p>
                </div>
            </div>
        @endif
    </div>

    <!-- Investment History -->
    <div class="mt-8 bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow overflow-hidden sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6 flex justify-between items-center">
            <div>
                <h3 class="text-lg leading-6 font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                    Investment History
                </h3>
                <p class="mt-1 max-w-2xl text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">
                    Track all investments made in this project over time.
                </p>
            </div>
            <x-button href="{{ route('project-investment-transactions.create', $projectInvestment) }}" variant="primary">
                Add Investment
            </x-button>
        </div>
        <div class="border-t border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)]">
            @if($projectInvestment->transactions->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-[color:var(--color-primary-200)] dark:divide-[color:var(--color-dark-300)]">
                        <thead class="bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)]">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">
                                    Date
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">
                                    Amount
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">
                                    Currency
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">
                                    Notes
                                </th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] divide-y divide-[color:var(--color-primary-200)] dark:divide-[color:var(--color-dark-300)]">
                            @foreach($projectInvestment->transactions as $transaction)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                        {{ $transaction->transaction_date->format('M j, Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                        {{ $transaction->formatted_amount }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                        {{ $transaction->currency }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                        {{ $transaction->notes ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex justify-end gap-2">
                                            <a href="{{ route('project-investment-transactions.edit', $transaction) }}"
                                               class="text-[color:var(--color-info-600)] hover:text-[color:var(--color-info-700)] dark:text-blue-400 dark:hover:text-blue-300">
                                                Edit
                                            </a>
                                            <form method="POST" action="{{ route('project-investment-transactions.destroy', $transaction) }}"
                                                  onsubmit="return confirm('Are you sure you want to delete this transaction?');"
                                                  class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="text-[color:var(--color-danger-600)] hover:text-[color:var(--color-danger-700)] dark:text-[color:var(--color-danger-400)] dark:hover:text-[color:var(--color-danger-300)]">
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)]">
                            <tr>
                                <td colspan="1" class="px-6 py-4 text-sm font-bold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                    Total Invested
                                </td>
                                <td colspan="4" class="px-6 py-4 text-sm font-bold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                    {{ $projectInvestment->formatted_investment_amount }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @else
                <div class="px-4 py-8 text-center">
                    <p class="text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">
                        No investment transactions recorded yet.
                    </p>
                    <div class="mt-4">
                        <x-button href="{{ route('project-investment-transactions.create', $projectInvestment) }}" variant="primary">
                            Add First Investment
                        </x-button>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="mt-8 flex flex-wrap gap-4" x-data="{}">
        <button type="button"
                x-on:click="$dispatch('open-modal', { id: 'updateValueModal' })"
                class="bg-[color:var(--color-info-600)] hover:bg-[color:var(--color-info-700)] text-white px-4 py-2 rounded-md text-sm font-medium">
            Update Value
        </button>
    </div>

    <!-- Update Value Modal -->
    <x-modal id="updateValueModal" title="Update Current Value">
        <form method="POST" action="{{ route('project-investments.update-value', $projectInvestment) }}">
            @csrf
            <div class="mb-4">
                <label for="current_value" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Current Value ({{ $projectInvestment->primary_currency }})</label>
                <div class="mt-1 relative rounded-md shadow-sm">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] sm:text-sm">{{ $projectInvestment->primary_currency }}</span>
                    </div>
                    <input type="number" step="0.01" name="current_value" id="current_value" required min="0"
                           value="{{ $projectInvestment->current_value ?? $projectInvestment->total_invested }}"
                           class="block w-full pl-12 pr-3 py-2 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded-md focus:outline-none focus:ring-[color:var(--color-accent-500)] focus:border-[color:var(--color-accent-500)] bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                </div>
            </div>
            <div class="flex justify-end gap-3">
                <x-button type="button" variant="secondary" x-on:click="$dispatch('close-modal', { id: 'updateValueModal' })">Cancel</x-button>
                <x-button type="submit" variant="primary">Update Value</x-button>
            </div>
        </form>
    </x-modal>
@endsection
