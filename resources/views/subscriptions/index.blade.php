@extends('layouts.app')

@section('title', 'Subscriptions - LifeOS')

@section('header')
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                Subscriptions
            </h1>
            <p class="mt-2 text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">
                Manage your recurring subscriptions and track spending
            </p>
        </div>
        <div class="flex-shrink-0">
            <a href="{{ route('subscriptions.create') }}" class="w-full sm:w-auto inline-flex justify-center items-center bg-[color:var(--color-accent-500)] hover:bg-[color:var(--color-accent-600)] text-white px-6 py-3 sm:px-4 sm:py-2 rounded-lg sm:rounded-md text-base sm:text-sm font-medium transition-colors duration-200 shadow-sm touch-manipulation">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Add Subscription
            </a>
        </div>
    </div>
@endsection

@section('content')
    <!-- Filters and Search -->
    <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow rounded-lg mb-6 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
        <div class="px-4 py-5 sm:px-6">
            <form method="GET" action="{{ route('subscriptions.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Search -->
                <div>
                    <x-form.input
                        name="search"
                        label="Search"
                        type="text"
                        placeholder="Search subscriptions..."
                    />
                </div>

                <!-- Status Filter -->
                <div>
                    <x-form.select
                        name="status"
                        label="Status"
                        placeholder="All Status"
                    >
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        <option value="paused" {{ request('status') === 'paused' ? 'selected' : '' }}>Paused</option>
                    </x-form.select>
                </div>

                <!-- Category Filter -->
                <div>
                    <x-form.select
                        name="category"
                        label="Category"
                        placeholder="All Categories"
                    >
                        <option value="Entertainment" {{ request('category') === 'Entertainment' ? 'selected' : '' }}>Entertainment</option>
                        <option value="Software" {{ request('category') === 'Software' ? 'selected' : '' }}>Software</option>
                        <option value="Fitness" {{ request('category') === 'Fitness' ? 'selected' : '' }}>Fitness</option>
                        <option value="Storage" {{ request('category') === 'Storage' ? 'selected' : '' }}>Storage</option>
                        <option value="Productivity" {{ request('category') === 'Productivity' ? 'selected' : '' }}>Productivity</option>
                    </x-form.select>
                </div>

                <!-- Due Soon -->
                <div>
                    <x-form.select
                        name="due_soon"
                        label="Due Soon"
                        placeholder="All"
                    >
                        <option value="7" {{ request('due_soon') === '7' ? 'selected' : '' }}>Due in 7 days</option>
                        <option value="14" {{ request('due_soon') === '14' ? 'selected' : '' }}>Due in 14 days</option>
                        <option value="30" {{ request('due_soon') === '30' ? 'selected' : '' }}>Due in 30 days</option>
                    </x-form.select>
                </div>

                <div class="col-span-full">
                    <div class="flex flex-col sm:flex-row gap-3 sm:gap-2">
                        <button type="submit" class="w-full sm:w-auto inline-flex justify-center items-center bg-[color:var(--color-accent-500)] hover:bg-[color:var(--color-accent-600)] text-white px-6 py-3 sm:px-4 sm:py-2 rounded-lg sm:rounded-md text-base sm:text-sm font-medium transition-colors duration-200 shadow-sm touch-manipulation">
                            Apply Filters
                        </button>
                        <a href="{{ route('subscriptions.index') }}" class="w-full sm:w-auto inline-flex justify-center items-center bg-[color:var(--color-primary-200)] hover:bg-[color:var(--color-primary-300)] text-[color:var(--color-primary-700)] dark:bg-[color:var(--color-dark-300)] dark:hover:bg-[color:var(--color-dark-400)] dark:text-[color:var(--color-dark-600)] px-6 py-3 sm:px-4 sm:py-2 rounded-lg sm:rounded-md text-base sm:text-sm font-medium transition-colors duration-200 touch-manipulation">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            Clear Filters
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Subscriptions Table -->
    <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow overflow-hidden sm:rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]" x-data="{}">
        <div class="px-4 py-5 sm:p-6">
            @if($subscriptions->count() > 0)
                <!-- Mobile Card Layout (visible on small screens) -->
                <div class="block sm:hidden space-y-4">
                    @foreach($subscriptions as $subscription)
                        <div class="bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded-lg p-4 space-y-3">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <h3 class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                        {{ $subscription->service_name }}
                                    </h3>
                                    @if($subscription->description)
                                        <p class="text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] mt-1">
                                            {{ Str::limit($subscription->description, 80) }}
                                        </p>
                                    @endif
                                </div>
                                <div class="flex-shrink-0 ml-4">
                                    @if($subscription->status === 'active')
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-[color:var(--color-success-50)] text-[color:var(--color-success-600)] dark:bg-[color:var(--color-dark-300)] dark:text-[color:var(--color-success-500)]">
                                            Active
                                        </span>
                                    @elseif($subscription->status === 'cancelled')
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-[color:var(--color-danger-50)] text-[color:var(--color-danger-600)] dark:bg-[color:var(--color-dark-300)] dark:text-[color:var(--color-danger-500)]">
                                            Cancelled
                                        </span>
                                    @else
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-[color:var(--color-warning-50)] text-[color:var(--color-warning-600)] dark:bg-[color:var(--color-dark-300)] dark:text-[color:var(--color-warning-500)]">
                                            Paused
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div>
                                    <span class="text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Category:</span>
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-[color:var(--color-info-50)] text-[color:var(--color-info-600)] dark:bg-[color:var(--color-dark-300)] dark:text-[color:var(--color-info-500)] ml-2">
                                        {{ $subscription->category }}
                                    </span>
                                </div>
                                <div>
                                    <span class="text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Cost:</span>
                                    <span class="font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] ml-2">
                                        @php
                                            $currencyService = app(\App\Services\CurrencyService::class);
                                            $currency = $subscription->currency ?? config('currency.default', 'MKD');
                                            $costInMKD = $currencyService->convertToDefault($subscription->cost, $currency);
                                        @endphp
                                        {{ $currencyService->format($costInMKD) }}
                                    </span>
                                    <div class="text-xs text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">{{ ucfirst($subscription->billing_cycle) }}</div>
                                </div>
                                <div class="col-span-2">
                                    <span class="text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Next Billing:</span>
                                    <span class="font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] ml-2">
                                        {{ $subscription->next_billing_date->format('M j, Y') }}
                                    </span>
                                    @php
                                        $daysUntil = now()->diffInDays($subscription->next_billing_date, false);
                                    @endphp
                                    @if($daysUntil <= 7 && $daysUntil >= 0)
                                        <span class="text-xs text-[color:var(--color-warning-600)] ml-2">Due in {{ $daysUntil }} days</span>
                                    @endif
                                </div>
                            </div>

                            <div class="flex flex-col sm:flex-row sm:justify-end gap-2 pt-3 border-t border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-400)]">
                                <a href="{{ route('subscriptions.show', $subscription) }}" class="w-full sm:w-auto inline-flex justify-center bg-[color:var(--color-accent-100)] hover:bg-[color:var(--color-accent-200)] text-[color:var(--color-accent-700)] px-4 py-3 sm:px-3 sm:py-2 rounded-md text-base sm:text-sm font-medium transition-colors duration-200 touch-manipulation">View</a>
                                <a href="{{ route('subscriptions.edit', $subscription) }}" class="w-full sm:w-auto inline-flex justify-center bg-[color:var(--color-warning-100)] hover:bg-[color:var(--color-warning-200)] text-[color:var(--color-warning-700)] px-4 py-3 sm:px-3 sm:py-2 rounded-md text-base sm:text-sm font-medium transition-colors duration-200 touch-manipulation">Edit</a>
                                @if($subscription->status === 'active')
                                    <button type="button"
                                            class="w-full sm:w-auto inline-flex justify-center bg-[color:var(--color-warning-100)] hover:bg-[color:var(--color-warning-200)] text-[color:var(--color-warning-700)] px-4 py-3 sm:px-3 sm:py-2 rounded-md text-base sm:text-sm font-medium transition-colors duration-200 touch-manipulation"
                                            x-on:click="$dispatch('open-modal', { id: 'pauseModal-{{ $subscription->id }}' })">
                                        Pause
                                    </button>
                                @elseif($subscription->status === 'paused')
                                    <button type="button"
                                            class="w-full sm:w-auto inline-flex justify-center bg-[color:var(--color-success-100)] hover:bg-[color:var(--color-success-200)] text-[color:var(--color-success-700)] px-4 py-3 sm:px-3 sm:py-2 rounded-md text-base sm:text-sm font-medium transition-colors duration-200 touch-manipulation"
                                            x-on:click="$dispatch('open-modal', { id: 'resumeModal-{{ $subscription->id }}' })">
                                        Resume
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Desktop Table Layout (hidden on small screens) -->
                <div class="hidden sm:block overflow-x-auto">
                    <table class="min-w-full divide-y divide-[color:var(--color-primary-300)] dark:divide-[color:var(--color-dark-300)]">
                        <thead class="bg-[color:var(--color-primary-200)] dark:bg-[color:var(--color-dark-300)]">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">Service</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">Category</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">Cost</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">Next Billing</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] divide-y divide-[color:var(--color-primary-300)] dark:divide-[color:var(--color-dark-300)]">
                            @foreach($subscriptions as $subscription)
                                <tr class="hover:bg-[color:var(--color-primary-200)] dark:hover:bg-[color:var(--color-dark-300)]">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div>
                                                <div class="text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                                    {{ $subscription->service_name }}
                                                </div>
                                                @if($subscription->description)
                                                    <div class="text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">
                                                        {{ Str::limit($subscription->description, 50) }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-[color:var(--color-info-50)] text-[color:var(--color-info-600)] dark:bg-[color:var(--color-dark-300)] dark:text-[color:var(--color-info-500)]">
                                            {{ $subscription->category }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                        @php
                                            $currencyService = app(\App\Services\CurrencyService::class);
                                            $currency = $subscription->currency ?? config('currency.default', 'MKD');
                                            $costInMKD = $currencyService->convertToDefault($subscription->cost, $currency);
                                        @endphp
                                        <div>{{ $currencyService->format($costInMKD) }}</div>
                                        <div class="text-xs text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">{{ ucfirst($subscription->billing_cycle) }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                        {{ $subscription->next_billing_date->format('M j, Y') }}
                                        @php
                                            $daysUntil = now()->diffInDays($subscription->next_billing_date, false);
                                        @endphp
                                        @if($daysUntil <= 7 && $daysUntil >= 0)
                                            <div class="text-xs text-[color:var(--color-warning-600)]">Due in {{ $daysUntil }} days</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($subscription->status === 'active')
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-[color:var(--color-success-50)] text-[color:var(--color-success-600)] dark:bg-[color:var(--color-dark-300)] dark:text-[color:var(--color-success-500)]">
                                                Active
                                            </span>
                                        @elseif($subscription->status === 'cancelled')
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-[color:var(--color-danger-50)] text-[color:var(--color-danger-600)] dark:bg-[color:var(--color-dark-300)] dark:text-[color:var(--color-danger-500)]">
                                                Cancelled
                                            </span>
                                        @else
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-[color:var(--color-warning-50)] text-[color:var(--color-warning-600)] dark:bg-[color:var(--color-dark-300)] dark:text-[color:var(--color-warning-500)]">
                                                Paused
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <a href="{{ route('subscriptions.show', $subscription) }}" class="text-[color:var(--color-accent-600)] hover:text-[color:var(--color-accent-700)]">View</a>
                                            <a href="{{ route('subscriptions.edit', $subscription) }}" class="text-[color:var(--color-warning-600)] hover:text-[color:var(--color-warning-700)]">Edit</a>
                                            @if($subscription->status === 'active')
                                                <button type="button"
                                                        class="text-[color:var(--color-warning-600)] hover:text-[color:var(--color-warning-700)] cursor-pointer hover:underline focus:outline-none focus:ring-2 focus:ring-[color:var(--color-warning-500)] focus:ring-opacity-50 rounded transition-colors duration-200"
                                                        x-on:click="$dispatch('open-modal', { id: 'pauseModal-{{ $subscription->id }}' })">
                                                    Pause
                                                </button>
                                            @elseif($subscription->status === 'paused')
                                                <button type="button"
                                                        class="text-[color:var(--color-success-600)] hover:text-[color:var(--color-success-700)] cursor-pointer hover:underline focus:outline-none focus:ring-2 focus:ring-[color:var(--color-success-500)] focus:ring-opacity-50 rounded transition-colors duration-200"
                                                        x-on:click="$dispatch('open-modal', { id: 'resumeModal-{{ $subscription->id }}' })">
                                                    Resume
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-6">
                    {{ $subscriptions->withQueryString()->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-[color:var(--color-primary-400)] dark:text-[color:var(--color-dark-400)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">No subscriptions</h3>
                    <p class="mt-1 text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Get started by creating your first subscription.</p>
                    <div class="mt-6">
                        <a href="{{ route('subscriptions.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-[color:var(--color-accent-600)] hover:bg-[color:var(--color-accent-700)]">
                            Add Subscription
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Summary Stats -->
    @if($subscriptions->count() > 0)
        <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] overflow-hidden shadow rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-[color:var(--color-info-600)] dark:text-[color:var(--color-info-500)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] truncate">Total Monthly Cost</dt>
                                <dd class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                    @php
                                        $currencyService = app(\App\Services\CurrencyService::class);
                                        $totalMonthlyMKD = 0;
                                        foreach ($subscriptions->where('status', 'active') as $subscription) {
                                            $currency = $subscription->currency ?? config('currency.default', 'MKD');
                                            $costInMKD = $currencyService->convertToDefault($subscription->cost, $currency);
                                            $monthlyCostMKD = match ($subscription->billing_cycle) {
                                                'monthly' => $costInMKD,
                                                'yearly' => $costInMKD / 12,
                                                'weekly' => $costInMKD * 4.33,
                                                'custom' => $subscription->billing_cycle_days ? ($costInMKD * 30.44) / $subscription->billing_cycle_days : 0,
                                                default => 0,
                                            };
                                            $totalMonthlyMKD += $monthlyCostMKD;
                                        }
                                    @endphp
                                    {{ $currencyService->format($totalMonthlyMKD) }}
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
                            <svg class="h-6 w-6 text-[color:var(--color-success-600)] dark:text-[color:var(--color-success-500)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] truncate">Active Subscriptions</dt>
                                <dd class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">{{ $subscriptions->where('status', 'active')->count() }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] overflow-hidden shadow rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-[color:var(--color-warning-600)] dark:text-[color:var(--color-warning-500)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] truncate">Due This Week</dt>
                                <dd class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                    {{ $subscriptions->filter(function($sub) { return now()->diffInDays($sub->next_billing_date, false) <= 7 && now()->diffInDays($sub->next_billing_date, false) >= 0; })->count() }}
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Modals for each subscription -->
    @foreach($subscriptions as $subscription)
        @if($subscription->status === 'active')
            <x-confirmation-modal
                id="pauseModal-{{ $subscription->id }}"
                title="Pause Subscription"
                message="Are you sure you want to pause {{ $subscription->service_name }}? You can resume it later."
                confirm-text="Pause"
                confirm-button-class="bg-[color:var(--color-warning-500)] hover:bg-[color:var(--color-warning-600)] text-white"
                :action="route('subscriptions.pause', $subscription)"
                method="PATCH"
            />
        @elseif($subscription->status === 'paused')
            <x-confirmation-modal
                id="resumeModal-{{ $subscription->id }}"
                title="Resume Subscription"
                message="Are you sure you want to resume {{ $subscription->service_name }}?"
                confirm-text="Resume"
                confirm-button-class="bg-[color:var(--color-success-500)] hover:bg-[color:var(--color-success-600)] text-white"
                :action="route('subscriptions.resume', $subscription)"
                method="PATCH"
            />
        @endif
    @endforeach
@endsection
