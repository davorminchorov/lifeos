@extends('layouts.app')

@section('title', 'Contracts - LifeOS')

@section('header')
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                Contracts
            </h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">
                Manage your legal agreements and contracts
            </p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('contracts.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                Add Contract
            </a>
        </div>
    </div>
@endsection

@section('content')
    <!-- Filter and Search -->
    <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg mb-6">
        <div class="px-4 py-5 sm:px-6">
            <form method="GET" action="{{ route('contracts.index') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Search -->
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Search</label>
                    <input type="text" name="search" id="search" value="{{ request('search') }}"
                           placeholder="Contract title, counterparty..."
                           class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <!-- Status Filter -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                    <select name="status" id="status"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">All Statuses</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expired</option>
                        <option value="terminated" {{ request('status') === 'terminated' ? 'selected' : '' }}>Terminated</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    </select>
                </div>

                <!-- Contract Type Filter -->
                <div>
                    <label for="contract_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Type</label>
                    <select name="contract_type" id="contract_type"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
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
                    <label for="sort_by" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Sort By</label>
                    <select name="sort_by" id="sort_by"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="end_date" {{ request('sort_by', 'end_date') === 'end_date' ? 'selected' : '' }}>End Date</option>
                        <option value="title" {{ request('sort_by') === 'title' ? 'selected' : '' }}>Title</option>
                        <option value="counterparty" {{ request('sort_by') === 'counterparty' ? 'selected' : '' }}>Counterparty</option>
                        <option value="start_date" {{ request('sort_by') === 'start_date' ? 'selected' : '' }}>Start Date</option>
                        <option value="contract_value" {{ request('sort_by') === 'contract_value' ? 'selected' : '' }}>Value</option>
                    </select>
                </div>

                <div class="flex items-end space-x-2">
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                        Filter
                    </button>
                    <a href="{{ route('contracts.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-md text-sm font-medium">
                        Clear
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="mb-6 flex space-x-3">
        <a href="{{ route('contracts.index', ['expiring_soon' => 30]) }}"
           class="inline-flex items-center px-3 py-2 border border-orange-300 shadow-sm text-sm leading-4 font-medium rounded-md text-orange-700 bg-orange-50 hover:bg-orange-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
            <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
            </svg>
            Expiring Soon
        </a>
        <a href="{{ route('contracts.index', ['requiring_notice' => true]) }}"
           class="inline-flex items-center px-3 py-2 border border-red-300 shadow-sm text-sm leading-4 font-medium rounded-md text-red-700 bg-red-50 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
            <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            Requiring Notice
        </a>
    </div>

    <!-- Contracts List -->
    @if($contracts->count() > 0)
        <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-md">
            <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                @foreach($contracts as $contract)
                    <li class="px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-700">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center min-w-0 flex-1">
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center">
                                        <p class="text-sm font-medium text-indigo-600 dark:text-indigo-400 truncate">
                                            <a href="{{ route('contracts.show', $contract) }}" class="hover:text-indigo-500">
                                                {{ $contract->title }}
                                            </a>
                                        </p>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ml-2
                                            @if($contract->status === 'active') bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100
                                            @elseif($contract->status === 'expired') bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100
                                            @elseif($contract->status === 'terminated') bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-100
                                            @else bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100 @endif">
                                            {{ ucfirst($contract->status) }}
                                        </span>
                                    </div>
                                    <div class="mt-2 flex items-center text-sm text-gray-500 dark:text-gray-400">
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
                                                <span>${{ number_format($contract->contract_value, 2) }}</span>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="mt-2 flex items-center text-sm text-gray-500 dark:text-gray-400">
                                        @if($contract->end_date)
                                            <div class="flex items-center">
                                                <svg class="flex-shrink-0 mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                </svg>
                                                <span>Ends {{ $contract->end_date->format('M j, Y') }}</span>
                                                @if($contract->days_until_expiration !== null)
                                                    @if($contract->days_until_expiration < 0)
                                                        <span class="ml-2 text-red-600 dark:text-red-400 font-medium">
                                                            (Expired {{ abs($contract->days_until_expiration) }} days ago)
                                                        </span>
                                                    @elseif($contract->days_until_expiration <= 30)
                                                        <span class="ml-2 text-orange-600 dark:text-orange-400 font-medium">
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
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100">
                                        Notice Required
                                    </span>
                                @endif
                                <div class="flex space-x-2">
                                    <a href="{{ route('contracts.show', $contract) }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                        View
                                    </a>
                                    <a href="{{ route('contracts.edit', $contract) }}" class="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-300">
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
        <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg">
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 48 48">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No contracts found</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    @if(request()->hasAny(['search', 'status', 'contract_type']))
                        No contracts match your current filters.
                    @else
                        Get started by adding your first contract.
                    @endif
                </p>
                <div class="mt-6">
                    @if(request()->hasAny(['search', 'status', 'contract_type']))
                        <a href="{{ route('contracts.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Clear Filters
                        </a>
                    @else
                        <a href="{{ route('contracts.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
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
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Total Contracts</dt>
                            <dd class="text-lg font-medium text-gray-900 dark:text-white">{{ $contracts->total() }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Active</dt>
                            <dd class="text-lg font-medium text-gray-900 dark:text-white">
                                {{ $contracts->where('status', 'active')->count() }}
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Expiring Soon</dt>
                            <dd class="text-lg font-medium text-gray-900 dark:text-white">
                                {{ $contracts->filter(function($contract) {
                                    return $contract->end_date && $contract->end_date->diffInDays(now(), false) <= 30 && $contract->status === 'active';
                                })->count() }}
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Expired</dt>
                            <dd class="text-lg font-medium text-gray-900 dark:text-white">
                                {{ $contracts->where('status', 'expired')->count() }}
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
