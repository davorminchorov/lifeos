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
                    <label for="search" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Search</label>
                    <input type="text" name="search" id="search" value="{{ request('search') }}"
                           placeholder="Contract title, counterparty..."
                           class="mt-1 block w-full rounded-md border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] shadow-sm focus:border-[color:var(--color-accent-500)] focus:ring-[color:var(--color-accent-500)]">
                </div>

                <!-- Status Filter -->
                <div>
                    <label for="status" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Status</label>
                    <select name="status" id="status"
                            class="mt-1 block w-full rounded-md border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] shadow-sm focus:border-[color:var(--color-accent-500)] focus:ring-[color:var(--color-accent-500)]">
                        <option value="">All Statuses</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expired</option>
                        <option value="terminated" {{ request('status') === 'terminated' ? 'selected' : '' }}>Terminated</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    </select>
                </div>

                <!-- Contract Type Filter -->
                <div>
                    <label for="contract_type" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Type</label>
                    <select name="contract_type" id="contract_type"
                            class="mt-1 block w-full rounded-md border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] shadow-sm focus:border-[color:var(--color-accent-500)] focus:ring-[color:var(--color-accent-500)]">
                        <option value="">All Types</option>
                        <option value="lease" {{ request('contract_type') === 'lease' ? 'selected' : '' }}>Lease</option>
                        <option value="employment" {{ request('contract_type') === 'employment' ? 'selected' : '' }}>Employment</option>
                        <option value="service" {{ request('contract_type') === 'service' ? 'selected' : '' }}>Service</option>
                        <option value="insurance" {{ request('contract_type') === 'insurance' ? 'selected' : '' }}>Insurance</option>
                        <option value="maintenance" {{ request('contract_type') === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                        <option value="other" {{ request('contract_type') === 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>

                <!-- Sort By -->
                <div>
                    <label for="sort_by" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Sort By</label>
                    <select name="sort_by" id="sort_by"
                            class="mt-1 block w-full rounded-md border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] shadow-sm focus:border-[color:var(--color-accent-500)] focus:ring-[color:var(--color-accent-500)]">
                        <option value="end_date" {{ request('sort_by', 'end_date') === 'end_date' ? 'selected' : '' }}>End Date</option>
                        <option value="title" {{ request('sort_by') === 'title' ? 'selected' : '' }}>Title</option>
                        <option value="counterparty" {{ request('sort_by') === 'counterparty' ? 'selected' : '' }}>Counterparty</option>
                        <option value="start_date" {{ request('sort_by') === 'start_date' ? 'selected' : '' }}>Start Date</option>
                        <option value="contract_value" {{ request('sort_by') === 'contract_value' ? 'selected' : '' }}>Value</option>
                    </select>
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

    <!-- Contracts List -->
    @if($contracts->count() > 0)
        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow overflow-hidden sm:rounded-md border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
            <ul class="divide-y divide-[color:var(--color-primary-200)] dark:divide-[color:var(--color-dark-300)]">
                @foreach($contracts as $contract)
                    <li class="px-6 py-4 hover:bg-[color:var(--color-primary-200)] dark:hover:bg-[color:var(--color-dark-300)] transition-colors duration-200">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center min-w-0 flex-1">
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center">
                                        <p class="text-sm font-medium text-[color:var(--color-accent-600)] dark:text-[color:var(--color-accent-500)] truncate">
                                            <a href="{{ route('contracts.show', $contract) }}" class="hover:text-[color:var(--color-accent-700)]">
                                                {{ $contract->title }}
                                            </a>
                                        </p>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ml-2
                                            @if($contract->status === 'active') bg-[color:var(--color-success-50)] text-[color:var(--color-success-800)] dark:bg-[color:var(--color-success-800)] dark:text-[color:var(--color-success-100)]
                                            @elseif($contract->status === 'expired') bg-[color:var(--color-danger-50)] text-[color:var(--color-danger-800)] dark:bg-[color:var(--color-danger-800)] dark:text-[color:var(--color-danger-100)]
                                            @elseif($contract->status === 'terminated') bg-[color:var(--color-primary-100)] text-[color:var(--color-primary-800)] dark:bg-[color:var(--color-primary-800)] dark:text-[color:var(--color-primary-100)]
                                            @else bg-[color:var(--color-warning-50)] text-[color:var(--color-warning-800)] dark:bg-[color:var(--color-warning-800)] dark:text-[color:var(--color-warning-100)] @endif">
                                            {{ ucfirst($contract->status) }}
                                        </span>
                                    </div>
                                    <div class="mt-2 flex items-center text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">
                                        <div class="flex items-center">
                                            <svg class="flex-shrink-0 mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0H5m14 0v-4a2 2 0 00-2-2H7a2 2 0 00-2 2v4"></path>
                                            </svg>
                                            <span>{{ ucfirst(str_replace('_', ' ', $contract->contract_type)) }}</span>
                                        </div>
                                        <span class="mx-2">•</span>
                                        <div class="flex items-center">
                                            <svg class="flex-shrink-0 mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                            </svg>
                                            <span>{{ $contract->counterparty }}</span>
                                        </div>
                                        @if($contract->contract_value)
                                            <span class="mx-2">•</span>
                                            <div class="flex items-center">
                                                <svg class="flex-shrink-0 mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                                </svg>
                                                <span>{{ $contract->formatted_contract_value }}</span>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="mt-2 flex items-center text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">
                                        @if($contract->end_date)
                                            <div class="flex items-center">
                                                <svg class="flex-shrink-0 mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                </svg>
                                                <span>Ends {{ $contract->end_date->format('M j, Y') }}</span>
                                                @if($contract->days_until_expiration !== null)
                                                    @if($contract->days_until_expiration < 0)
                                                        <span class="ml-2 text-[color:var(--color-danger-600)] dark:text-[color:var(--color-danger-400)] font-medium">
                                                            (Expired {{ abs($contract->days_until_expiration) }} days ago)
                                                        </span>
                                                    @elseif($contract->days_until_expiration <= 30)
                                                        <span class="ml-2 text-[color:var(--color-warning-600)] dark:text-[color:var(--color-warning-400)] font-medium">
                                                            ({{ $contract->days_until_expiration }} days remaining)
                                                        </span>
                                                    @endif
                                                @endif
                                            </div>
                                        @else
                                            <div class="flex items-center">
                                                <svg class="flex-shrink-0 mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                </svg>
                                                <span>No end date</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="ml-5 flex-shrink-0 flex items-center space-x-2">
                                @if($contract->notice_deadline && $contract->notice_deadline->isFuture() && $contract->notice_deadline->diffInDays(now()) <= 30)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-[color:var(--color-danger-50)] text-[color:var(--color-danger-800)] dark:bg-[color:var(--color-danger-800)] dark:text-[color:var(--color-danger-100)]">
                                        Notice Required
                                    </span>
                                @endif
                                <div class="flex space-x-2">
                                    <a href="{{ route('contracts.show', $contract) }}" class="text-[color:var(--color-accent-600)] hover:text-[color:var(--color-accent-700)] dark:text-[color:var(--color-accent-500)] dark:hover:text-[color:var(--color-accent-400)]">
                                        View
                                    </a>
                                    <a href="{{ route('contracts.edit', $contract) }}" class="text-[color:var(--color-primary-600)] hover:text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-500)] dark:hover:text-[color:var(--color-dark-400)]">
                                        Edit
                                    </a>
                                </div>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $contracts->withQueryString()->links() }}
        </div>
    @else
        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow overflow-hidden sm:rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-[color:var(--color-primary-400)] dark:text-[color:var(--color-dark-400)]" fill="none" stroke="currentColor" viewBox="0 0 48 48">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">No contracts found</h3>
                <p class="mt-1 text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">
                    @if(request()->hasAny(['search', 'status', 'contract_type']))
                        No contracts match your current filters.
                    @else
                        Get started by adding your first contract.
                    @endif
                </p>
                <div class="mt-6">
                    @if(request()->hasAny(['search', 'status', 'contract_type']))
                        <a href="{{ route('contracts.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-[color:var(--color-accent-600)] hover:bg-[color:var(--color-accent-700)] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[color:var(--color-accent-500)] transition-colors duration-200">
                            Clear Filters
                        </a>
                    @else
                        <a href="{{ route('contracts.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-[color:var(--color-accent-600)] hover:bg-[color:var(--color-accent-700)] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[color:var(--color-accent-500)] transition-colors duration-200">
                            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Add Contract
                        </a>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <!-- Summary Stats -->
    <div class="mt-8 grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] overflow-hidden shadow rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-400)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] truncate">Total Contracts</dt>
                            <dd class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">{{ $contracts->total() }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] overflow-hidden shadow rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-[color:var(--color-success-500)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] truncate">Active</dt>
                            <dd class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                {{ $contracts->where('status', 'active')->count() }}
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] overflow-hidden shadow rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-[color:var(--color-warning-500)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] truncate">Expiring Soon</dt>
                            <dd class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                {{ $contracts->filter(function($contract) {
                                    return $contract->end_date && $contract->end_date->diffInDays(now(), false) <= 30 && $contract->status === 'active';
                                })->count() }}
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] overflow-hidden shadow rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-[color:var(--color-danger-500)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] truncate">Expired</dt>
                            <dd class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                {{ $contracts->where('status', 'expired')->count() }}
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
