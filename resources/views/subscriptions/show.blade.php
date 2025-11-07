@extends('layouts.app')

@section('title', $subscription->service_name . ' - Subscriptions - LifeOS')

@section('header')
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
        <div>
            <h1 class="text-3xl font-bold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                {{ $subscription->service_name }}
            </h1>
            <p class="mt-2 text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">
                {{ $subscription->description ?? 'Subscription details' }}
            </p>
        </div>
        <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
            <a href="{{ route('subscriptions.edit', $subscription) }}" class="w-full sm:w-auto inline-flex justify-center bg-[color:var(--color-warning-500)] hover:bg-[color:var(--color-warning-600)] text-white px-6 py-3 sm:px-4 sm:py-2 rounded-md text-base sm:text-sm font-medium transition-colors duration-200">
                Edit
            </a>
            <a href="{{ route('subscriptions.index') }}" class="w-full sm:w-auto inline-flex justify-center bg-[color:var(--color-primary-500)] hover:bg-[color:var(--color-primary-600)] text-white px-6 py-3 sm:px-4 sm:py-2 rounded-md text-base sm:text-sm font-medium transition-colors duration-200">
                Back to List
            </a>
        </div>
    </div>
@endsection

@section('content')
<div x-data="{}">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Main Details -->
        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow overflow-hidden sm:rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                    Subscription Details
                </h3>
                <p class="mt-1 max-w-2xl text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">
                    Basic information about this subscription.
                </p>
            </div>
            <div class="border-t border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
                <dl>
                    <div class="bg-[color:var(--color-primary-200)] dark:bg-[color:var(--color-dark-300)] px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">Service Name</dt>
                        <dd class="mt-1 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] sm:mt-0 sm:col-span-2">{{ $subscription->service_name }}</dd>
                    </div>
                    @if($subscription->description)
                        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">Description</dt>
                            <dd class="mt-1 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] sm:mt-0 sm:col-span-2">{{ $subscription->description }}</dd>
                        </div>
                    @endif
                    <div class="bg-[color:var(--color-primary-200)] dark:bg-[color:var(--color-dark-300)] px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">Category</dt>
                        <dd class="mt-1 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] sm:mt-0 sm:col-span-2">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-[color:var(--color-info-200)] text-[color:var(--color-info-800)] dark:bg-[color:var(--color-info-900)] dark:text-[color:var(--color-info-200)]">
                                {{ $subscription->category }}
                            </span>
                        </dd>
                    </div>
                    <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">Status</dt>
                        <dd class="mt-1 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] sm:mt-0 sm:col-span-2">
                            @if($subscription->status === 'active')
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-[color:var(--color-success-50)] text-[color:var(--color-success-600)] dark:bg-[color:var(--color-success-600)] dark:text-[color:var(--color-success-50)]">
                                    Active
                                </span>
                            @elseif($subscription->status === 'cancelled')
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-[color:var(--color-danger-50)] text-[color:var(--color-danger-600)] dark:bg-[color:var(--color-danger-600)] dark:text-[color:var(--color-danger-50)]">
                                    Cancelled
                                </span>
                            @else
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-[color:var(--color-warning-50)] text-[color:var(--color-warning-600)] dark:bg-[color:var(--color-warning-600)] dark:text-[color:var(--color-warning-50)]">
                                    Paused
                                </span>
                            @endif
                        </dd>
                    </div>
                    @if($subscription->merchant_info)
                        <div class="bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Merchant</dt>
                            <dd class="mt-1 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] sm:mt-0 sm:col-span-2">{{ $subscription->merchant_info }}</dd>
                        </div>
                    @endif
                    @if($subscription->payment_method)
                        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Payment Method</dt>
                            <dd class="mt-1 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] sm:mt-0 sm:col-span-2">{{ $subscription->payment_method }}</dd>
                        </div>
                    @endif
                </dl>
            </div>
        </div>

        <!-- Billing Information -->
        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow overflow-hidden sm:rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                    Billing Information
                </h3>
                <p class="mt-1 max-w-2xl text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">
                    Cost and billing schedule details.
                </p>
            </div>
            <div class="border-t border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
                <dl>
                    <div class="bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Cost</dt>
                        <dd class="mt-1 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] sm:mt-0 sm:col-span-2">
                            @php
                                $currencyService = app(\App\Services\CurrencyService::class);
                                $currency = $subscription->currency ?? config('currency.default', 'MKD');
                                $costInMKD = $currencyService->convertToDefault($subscription->cost, $currency);
                            @endphp
                            <div class="text-lg font-semibold">{{ $currencyService->format($costInMKD) }}</div>
                            <div class="text-xs text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-400)]">per {{ $subscription->billing_cycle }}</div>
                        </dd>
                    </div>
                    <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Monthly Cost</dt>
                        <dd class="mt-1 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] sm:mt-0 sm:col-span-2">
                            @php
                                $currencyService = app(\App\Services\CurrencyService::class);
                                $currency = $subscription->currency ?? config('currency.default', 'MKD');
                                $monthlyCostInMKD = $currencyService->convertToDefault($subscription->monthly_cost, $currency);
                            @endphp
                            <span class="text-lg font-semibold">{{ $currencyService->format($monthlyCostInMKD) }}</span>
                        </dd>
                    </div>
                    <div class="bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Yearly Cost</dt>
                        <dd class="mt-1 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] sm:mt-0 sm:col-span-2">
                            @php
                                $currencyService = app(\App\Services\CurrencyService::class);
                                $currency = $subscription->currency ?? config('currency.default', 'MKD');
                                $yearlyCostInMKD = $currencyService->convertToDefault($subscription->yearly_cost, $currency);
                            @endphp
                            <span class="text-lg font-semibold">{{ $currencyService->format($yearlyCostInMKD) }}</span>
                        </dd>
                    </div>
                    <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Billing Cycle</dt>
                        <dd class="mt-1 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] sm:mt-0 sm:col-span-2">
                            {{ ucfirst($subscription->billing_cycle) }}
                            @if($subscription->billing_cycle === 'custom' && $subscription->billing_cycle_days)
                                ({{ $subscription->billing_cycle_days }} days)
                            @endif
                        </dd>
                    </div>
                    <div class="bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Auto Renewal</dt>
                        <dd class="mt-1 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] sm:mt-0 sm:col-span-2">
                            @if($subscription->auto_renewal)
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-[color:var(--color-success-50)] text-[color:var(--color-success-600)] dark:bg-[color:var(--color-success-900)] dark:text-[color:var(--color-success-200)]">
                                    Enabled
                                </span>
                            @else
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-[color:var(--color-danger-50)] text-[color:var(--color-danger-600)] dark:bg-[color:var(--color-danger-900)] dark:text-[color:var(--color-danger-200)]">
                                    Disabled
                                </span>
                            @endif
                        </dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>

    <!-- Dates Section -->
    <div class="mt-8 bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow overflow-hidden sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg leading-6 font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                Important Dates
            </h3>
            <p class="mt-1 max-w-2xl text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-400)]">
                Key dates for this subscription.
            </p>
        </div>
        <div class="border-t border-gray-200 dark:border-gray-700">
            <dl class="grid grid-cols-1 md:grid-cols-3 gap-0">
                <div class="bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] px-4 py-5">
                    <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Start Date</dt>
                    <dd class="mt-1 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">{{ $subscription->start_date->format('M j, Y') }}</dd>
                </div>
                <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] px-4 py-5">
                    <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Next Billing Date</dt>
                    <dd class="mt-1 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                        {{ $subscription->next_billing_date->format('M j, Y') }}
                        @php
                            $daysUntil = now()->diffInDays($subscription->next_billing_date, false);
                        @endphp
                        @if($daysUntil <= 7 && $daysUntil >= 0)
                            <div class="text-xs text-orange-600 dark:text-orange-400 mt-1">Due in {{ $daysUntil }} days</div>
                        @endif
                    </dd>
                </div>
                @if($subscription->cancellation_date)
                    <div class="bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] px-4 py-5">
                        <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Cancellation Date</dt>
                        <dd class="mt-1 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">{{ $subscription->cancellation_date->format('M j, Y') }}</dd>
                    </div>
                @endif
            </dl>
        </div>
    </div>

    <!-- Management Section -->
    @if($subscription->cancellation_difficulty || $subscription->notes || $subscription->tags)
        <div class="mt-8 bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                    Management Information
                </h3>
                <p class="mt-1 max-w-2xl text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-400)]">
                    Additional details for managing this subscription.
                </p>
            </div>
            <div class="border-t border-gray-200 dark:border-gray-700">
                <dl>
                    @if($subscription->cancellation_difficulty)
                        <div class="bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Cancellation Difficulty</dt>
                            <dd class="mt-1 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] sm:mt-0 sm:col-span-2">
                                <div class="flex items-center">
                                    @for($i = 1; $i <= 5; $i++)
                                        <svg class="h-4 w-4 {{ $i <= $subscription->cancellation_difficulty ? 'text-[color:var(--color-warning-400)]' : 'text-[color:var(--color-primary-300)] dark:text-[color:var(--color-dark-400)]' }}" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                        </svg>
                                    @endfor
                                    <span class="ml-2 text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">
                                        ({{ $subscription->cancellation_difficulty }}/5 -
                                        @switch($subscription->cancellation_difficulty)
                                            @case(1) Very Easy @break
                                            @case(2) Easy @break
                                            @case(3) Moderate @break
                                            @case(4) Hard @break
                                            @case(5) Very Hard @break
                                        @endswitch
                                        )
                                    </span>
                                </div>
                            </dd>
                        </div>
                    @endif

                    @if($subscription->tags && count($subscription->tags) > 0)
                        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Tags</dt>
                            <dd class="mt-1 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] sm:mt-0 sm:col-span-2">
                                <div class="flex flex-wrap gap-2">
                                    @foreach($subscription->tags as $tag)
                                        <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-[color:var(--color-primary-200)] text-[color:var(--color-primary-700)] dark:bg-[color:var(--color-dark-300)] dark:text-[color:var(--color-dark-600)]">
                                            {{ $tag }}
                                        </span>
                                    @endforeach
                                </div>
                            </dd>
                        </div>
                    @endif

                    @if($subscription->notes)
                        <div class="bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Notes</dt>
                            <dd class="mt-1 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] sm:mt-0 sm:col-span-2">{{ $subscription->notes }}</dd>
                        </div>
                    @endif
                </dl>
            </div>
        </div>
    @endif

    <!-- Price History -->
    @if($subscription->price_history && count($subscription->price_history) > 0)
        <div class="mt-8 bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                    Price History
                </h3>
                <p class="mt-1 max-w-2xl text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-400)]">
                    Track how the subscription price has changed over time.
                </p>
            </div>
            <div class="border-t border-gray-200 dark:border-gray-700">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-[color:var(--color-primary-300)] dark:divide-[color:var(--color-dark-300)]">
                        <thead class="bg-[color:var(--color-primary-200)] dark:bg-[color:var(--color-dark-300)]">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">Price</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">Change</th>
                            </tr>
                        </thead>
                        <tbody class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] divide-y divide-[color:var(--color-primary-300)] dark:divide-[color:var(--color-dark-300)]">
                            @foreach($subscription->price_history as $index => $history)
                                <tr class="hover:bg-[color:var(--color-primary-200)] dark:hover:bg-[color:var(--color-dark-300)]">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                        {{ \Carbon\Carbon::parse($history['date'])->format('M j, Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                        {{ $subscription->currency }} {{ number_format($history['price'], 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        @if($index > 0)
                                            @php
                                                $previousPrice = $subscription->price_history[$index - 1]['price'];
                                                $change = $history['price'] - $previousPrice;
                                                $changePercent = $previousPrice > 0 ? ($change / $previousPrice) * 100 : 0;
                                            @endphp
                                            @if($change > 0)
                                                <span class="text-[color:var(--color-danger-600)] dark:text-[color:var(--color-danger-500)]">
                                                    +{{ $subscription->currency }}{{ number_format($change, 2) }} (+{{ number_format($changePercent, 1) }}%)
                                                </span>
                                            @elseif($change < 0)
                                                <span class="text-[color:var(--color-success-600)] dark:text-[color:var(--color-success-500)]">
                                                    {{ $subscription->currency }}{{ number_format($change, 2) }} ({{ number_format($changePercent, 1) }}%)
                                                </span>
                                            @else
                                                <span class="text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">No change</span>
                                            @endif
                                        @else
                                            <span class="text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Initial price</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <!-- Actions -->
    <div class="mt-8 bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow overflow-hidden sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg leading-6 font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                Actions
            </h3>
            <p class="mt-1 max-w-2xl text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-400)]">
                Manage your subscription status.
            </p>
        </div>
        <div class="border-t border-gray-200 dark:border-gray-700 px-4 py-5 sm:px-6">
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('subscriptions.edit', $subscription) }}" class="bg-[color:var(--color-warning-500)] hover:bg-[color:var(--color-warning-600)] text-white px-4 py-2 rounded-md text-sm font-medium">
                    Edit Subscription
                </a>

                @if($subscription->status === 'active')
                    <button type="button"
                            class="bg-[color:var(--color-warning-500)] hover:bg-[color:var(--color-warning-600)] text-white px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200"
                            x-on:click="$dispatch('open-modal', { id: 'pauseModal' })">
                        Pause Subscription
                    </button>
                    <button type="button"
                            class="bg-[color:var(--color-danger-600)] hover:bg-[color:var(--color-danger-700)] text-white px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200"
                            x-on:click="$dispatch('open-modal', { id: 'cancelModal' })">
                        Cancel Subscription
                    </button>
                @elseif($subscription->status === 'paused')
                    <button type="button"
                            class="bg-[color:var(--color-success-600)] hover:bg-[color:var(--color-success-700)] text-white px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200"
                            x-on:click="$dispatch('open-modal', { id: 'resumeModal' })">
                        Resume Subscription
                    </button>
                    <button type="button"
                            class="bg-[color:var(--color-danger-600)] hover:bg-[color:var(--color-danger-700)] text-white px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200"
                            x-on:click="$dispatch('open-modal', { id: 'cancelModal' })">
                        Cancel Subscription
                    </button>
                @elseif($subscription->status === 'cancelled')
                    <div class="text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">
                        This subscription has been cancelled and cannot be modified.
                    </div>
                @endif

                <button type="button"
                        class="bg-[color:var(--color-primary-600)] hover:bg-[color:var(--color-primary-700)] text-white px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200"
                        x-on:click="$dispatch('open-modal', { id: 'deleteModal' })">
                    Delete Subscription
                </button>
            </div>
        </div>
    </div>

    <!-- Modals -->
    @if($subscription->status === 'active')
        <x-confirmation-modal
            id="pauseModal"
            title="Pause Subscription"
            message="Are you sure you want to pause {{ $subscription->service_name }}? You can resume it later."
            confirm-text="Pause"
            confirm-button-class="bg-[color:var(--color-warning-500)] hover:bg-[color:var(--color-warning-600)] text-white"
            :action="route('subscriptions.pause', $subscription)"
            method="PATCH"
        />

        <x-confirmation-modal
            id="cancelModal"
            title="Cancel Subscription"
            message="Are you sure you want to cancel {{ $subscription->service_name }}? This action cannot be undone."
            confirm-text="Cancel Subscription"
            confirm-button-class="bg-[color:var(--color-danger-500)] hover:bg-[color:var(--color-danger-600)] text-white"
            :action="route('subscriptions.cancel', $subscription)"
            method="PATCH"
        />
    @elseif($subscription->status === 'paused')
        <x-confirmation-modal
            id="resumeModal"
            title="Resume Subscription"
            message="Are you sure you want to resume {{ $subscription->service_name }}?"
            confirm-text="Resume"
            confirm-button-class="bg-[color:var(--color-success-500)] hover:bg-[color:var(--color-success-600)] text-white"
            :action="route('subscriptions.resume', $subscription)"
            method="PATCH"
        />

        <x-confirmation-modal
            id="cancelModal"
            title="Cancel Subscription"
            message="Are you sure you want to cancel {{ $subscription->service_name }}? This action cannot be undone."
            confirm-text="Cancel Subscription"
            confirm-button-class="bg-[color:var(--color-danger-500)] hover:bg-[color:var(--color-danger-600)] text-white"
            :action="route('subscriptions.cancel', $subscription)"
            method="PATCH"
        />
    @endif

    <x-confirmation-modal
        id="deleteModal"
        title="Delete Subscription"
        message="Are you sure you want to delete {{ $subscription->service_name }}? This action cannot be undone and all data will be permanently removed."
        confirm-text="Delete"
        confirm-button-class="bg-[color:var(--color-danger-500)] hover:bg-[color:var(--color-danger-600)] text-white"
        :action="route('subscriptions.destroy', $subscription)"
        method="DELETE"
    />
</div>
@endsection
