@extends('layouts.app')

@section('title', 'Contracts - LifeOS')

@section('header')
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                Contracts
            </h1>
            <p class="mt-2 text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">
                Manage your legal agreements and contracts
            </p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('contracts.create') }}" class="bg-[color:var(--color-accent-600)] hover:bg-[color:var(--color-accent-700)] text-white px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200 shadow-sm">
                Add Contract
            </a>
        </div>
    </div>
@endsection

@section('content')
    <!-- Filter and Search -->
    <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow rounded-lg mb-6 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
        <div class="px-4 py-5 sm:px-6">
            <form method="GET" action="{{ route('contracts.index') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Search -->
                <div>
                    <x-form.input
                        name="search"
                        label="Search"
                        type="text"
                        placeholder="Contract title, counterparty..."
                    />
                </div>

                <!-- Status Filter -->
                <div>
                    <x-form.select
                        name="status"
                        label="Status"
                        placeholder="All Statuses"
                    >
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expired</option>
                        <option value="terminated" {{ request('status') === 'terminated' ? 'selected' : '' }}>Terminated</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    </x-form.select>
                </div>

                <!-- Contract Type Filter -->
                <div>
                    <x-form.select
                        name="contract_type"
                        label="Type"
                        placeholder="All Types"
                    >
                        <option value="lease" {{ request('contract_type') === 'lease' ? 'selected' : '' }}>Lease</option>
                        <option value="employment" {{ request('contract_type') === 'employment' ? 'selected' : '' }}>Employment</option>
                        <option value="service" {{ request('contract_type') === 'service' ? 'selected' : '' }}>Service</option>
                        <option value="insurance" {{ request('contract_type') === 'insurance' ? 'selected' : '' }}>Insurance</option>
                        <option value="maintenance" {{ request('contract_type') === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                        <option value="other" {{ request('contract_type') === 'other' ? 'selected' : '' }}>Other</option>
                    </x-form.select>
                </div>

                <!-- Sort By -->
                <div>
                    <x-form.select
                        name="sort_by"
                        label="Sort By"
                    >
                        <option value="end_date" {{ request('sort_by', 'end_date') === 'end_date' ? 'selected' : '' }}>End Date</option>
                        <option value="title" {{ request('sort_by') === 'title' ? 'selected' : '' }}>Title</option>
                        <option value="counterparty" {{ request('sort_by') === 'counterparty' ? 'selected' : '' }}>Counterparty</option>
                        <option value="start_date" {{ request('sort_by') === 'start_date' ? 'selected' : '' }}>Start Date</option>
                        <option value="contract_value" {{ request('sort_by') === 'contract_value' ? 'selected' : '' }}>Value</option>
                    </x-form.select>
                </div>

                <div class="flex items-end space-x-2">
                    <button type="submit" class="bg-[color:var(--color-accent-500)] hover:bg-[color:var(--color-accent-600)] text-white px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200 shadow-sm">
                        Apply Filters
                    </button>
                    <a href="{{ route('contracts.index') }}" class="bg-[color:var(--color-primary-200)] hover:bg-[color:var(--color-primary-300)] text-[color:var(--color-primary-700)] dark:bg-[color:var(--color-dark-300)] dark:hover:bg-[color:var(--color-dark-400)] dark:text-[color:var(--color-dark-600)] px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200">
                        Clear
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="mb-6 flex space-x-3">
        <a href="{{ route('contracts.index', ['expiring_soon' => 30]) }}"
           class="inline-flex items-center px-3 py-2 border border-[color:var(--color-warning-300)] shadow-sm text-sm leading-4 font-medium rounded-md text-[color:var(--color-warning-700)] bg-[color:var(--color-warning-50)] hover:bg-[color:var(--color-warning-100)] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[color:var(--color-warning-500)] transition-colors duration-200">
            <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
            </svg>
            Expiring Soon
        </a>
        <a href="{{ route('contracts.index', ['requiring_notice' => true]) }}"
           class="inline-flex items-center px-3 py-2 border border-[color:var(--color-danger-300)] shadow-sm text-sm leading-4 font-medium rounded-md text-[color:var(--color-danger-700)] bg-[color:var(--color-danger-50)] hover:bg-[color:var(--color-danger-100)] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[color:var(--color-danger-500)] transition-colors duration-200">
            <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            Requiring Notice
        </a>
    </div>

    <!-- Contracts Table -->
    <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow overflow-hidden sm:rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]" x-data="{}">
        <div class="px-4 py-5 sm:p-6">
            @if($contracts->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-[color:var(--color-primary-300)] dark:divide-[color:var(--color-dark-300)]">
                        <thead class="bg-[color:var(--color-primary-200)] dark:bg-[color:var(--color-dark-300)]">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">Title</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">Counterparty</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">End Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">Value</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] divide-y divide-[color:var(--color-primary-300)] dark:divide-[color:var(--color-dark-300)]">
                            @foreach($contracts as $contract)
                                <tr class="hover:bg-[color:var(--color-primary-200)] dark:hover:bg-[color:var(--color-dark-300)]">
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-[color:var(--color-accent-600)] dark:text-[color:var(--color-accent-500)]">
                                            <a href="{{ route('contracts.show', $contract) }}" class="hover:text-[color:var(--color-accent-700)]">
                                                {{ $contract->title }}
                                            </a>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded-full bg-[color:var(--color-info-50)] text-[color:var(--color-info-600)] dark:bg-[color:var(--color-dark-300)] dark:text-[color:var(--color-info-500)]">
                                            {{ ucfirst(str_replace('_', ' ', $contract->contract_type)) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                        {{ $contract->counterparty }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold
                                            @if($contract->status === 'active') bg-[color:var(--color-success-50)] text-[color:var(--color-success-600)] dark:bg-[color:var(--color-dark-300)] dark:text-[color:var(--color-success-500)]
                                            @elseif($contract->status === 'expired') bg-[color:var(--color-danger-50)] text-[color:var(--color-danger-600)] dark:bg-[color:var(--color-dark-300)] dark:text-[color:var(--color-danger-500)]
                                            @elseif($contract->status === 'terminated') bg-[color:var(--color-primary-200)] text-[color:var(--color-primary-700)] dark:bg-[color:var(--color-dark-300)] dark:text-[color:var(--color-primary-700)]
                                            @else bg-[color:var(--color-warning-50)] text-[color:var(--color-warning-600)] dark:bg-[color:var(--color-dark-300)] dark:text-[color:var(--color-warning-500)] @endif">
                                            {{ ucfirst($contract->status) }}
                                        </span>
                                        @if($contract->notice_deadline && $contract->notice_deadline->isFuture() && $contract->notice_deadline->diffInDays(now()) <= 30)
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-[color:var(--color-danger-50)] text-[color:var(--color-danger-600)] dark:bg-[color:var(--color-dark-300)] dark:text-[color:var(--color-danger-500)] ml-1">
                                                Notice Required
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                        @if($contract->end_date)
                                            <div>
                                                {{ $contract->end_date->format('M j, Y') }}
                                                @if($contract->days_until_expiration !== null)
                                                    @if($contract->days_until_expiration < 0)
                                                        <div class="text-xs text-[color:var(--color-danger-600)] dark:text-[color:var(--color-danger-400)] font-medium">
                                                            Expired {{ abs($contract->days_until_expiration) }} days ago
                                                        </div>
                                                    @elseif($contract->days_until_expiration <= 30)
                                                        <div class="text-xs text-[color:var(--color-warning-600)] dark:text-[color:var(--color-warning-400)] font-medium">
                                                            {{ $contract->days_until_expiration }} days remaining
                                                        </div>
                                                    @endif
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">No end date</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                        {{ $contract->contract_value ? $contract->formatted_contract_value : '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <a href="{{ route('contracts.show', $contract) }}" class="text-[color:var(--color-accent-600)] hover:text-[color:var(--color-accent-700)]">View</a>
                                            <a href="{{ route('contracts.edit', $contract) }}" class="text-[color:var(--color-warning-600)] hover:text-[color:var(--color-warning-700)]">Edit</a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-6">
                    {{ $contracts->withQueryString()->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <div class="text-[color:var(--color-primary-400)] dark:text-[color:var(--color-dark-400)] mb-4">
                        <svg class="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-2">No contracts found</h3>
                    <p class="text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] mb-4">Get started by adding your first contract.</p>
                    <a href="{{ route('contracts.create') }}" class="bg-[color:var(--color-accent-600)] hover:bg-[color:var(--color-accent-700)] text-white px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200">
                        Add Contract
                    </a>
                </div>
            @endif
        </div>
    </div>
@endsection
