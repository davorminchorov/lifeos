@extends('layouts.app')

@section('title', $subscription->service_name . ' - Subscriptions - LifeOS')

@section('header')
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                {{ $subscription->service_name }}
            </h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">
                {{ $subscription->description ?? 'Subscription details' }}
            </p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('subscriptions.edit', $subscription) }}" class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                Edit
            </a>
            <a href="{{ route('subscriptions.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                Back to List
            </a>
        </div>
    </div>
@endsection

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Main Details -->
        <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                    Subscription Details
                </h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
                    Basic information about this subscription.
                </p>
            </div>
            <div class="border-t border-gray-200 dark:border-gray-700">
                <dl>
                    <div class="bg-gray-50 dark:bg-gray-700 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">Service Name</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2">{{ $subscription->service_name }}</dd>
                    </div>
                    @if($subscription->description)
                        <div class="bg-white dark:bg-gray-800 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">Description</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2">{{ $subscription->description }}</dd>
                        </div>
                    @endif
                    <div class="bg-gray-50 dark:bg-gray-700 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">Category</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                {{ $subscription->category }}
                            </span>
                        </dd>
                    </div>
                    <div class="bg-white dark:bg-gray-800 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">Status</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2">
                            @if($subscription->status === 'active')
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                    Active
                                </span>
                            @elseif($subscription->status === 'cancelled')
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                    Cancelled
                                </span>
                            @else
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                    Paused
                                </span>
                            @endif
                        </dd>
                    </div>
                    @if($subscription->merchant_info)
                        <div class="bg-gray-50 dark:bg-gray-700 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">Merchant</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2">{{ $subscription->merchant_info }}</dd>
                        </div>
                    @endif
                    @if($subscription->payment_method)
                        <div class="bg-white dark:bg-gray-800 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">Payment Method</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2">{{ $subscription->payment_method }}</dd>
                        </div>
                    @endif
                </dl>
            </div>
        </div>

        <!-- Billing Information -->
        <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                    Billing Information
                </h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
                    Cost and billing schedule details.
                </p>
            </div>
            <div class="border-t border-gray-200 dark:border-gray-700">
                <dl>
                    <div class="bg-gray-50 dark:bg-gray-700 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">Cost</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2">
                            <div class="text-lg font-semibold">{{ $subscription->currency }} {{ number_format($subscription->cost, 2) }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">per {{ $subscription->billing_cycle }}</div>
                        </dd>
                    </div>
                    <div class="bg-white dark:bg-gray-800 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">Monthly Cost</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2">
                            <span class="text-lg font-semibold">${{ number_format($subscription->monthly_cost, 2) }}</span>
                        </dd>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">Yearly Cost</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2">
                            <span class="text-lg font-semibold">${{ number_format($subscription->yearly_cost, 2) }}</span>
                        </dd>
                    </div>
                    <div class="bg-white dark:bg-gray-800 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">Billing Cycle</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2">
                            {{ ucfirst($subscription->billing_cycle) }}
                            @if($subscription->billing_cycle === 'custom' && $subscription->billing_cycle_days)
                                ({{ $subscription->billing_cycle_days }} days)
                            @endif
                        </dd>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">Auto Renewal</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2">
                            @if($subscription->auto_renewal)
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                    Enabled
                                </span>
                            @else
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
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
    <div class="mt-8 bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                Important Dates
            </h3>
            <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
                Key dates for this subscription.
            </p>
        </div>
        <div class="border-t border-gray-200 dark:border-gray-700">
            <dl class="grid grid-cols-1 md:grid-cols-3 gap-0">
                <div class="bg-gray-50 dark:bg-gray-700 px-4 py-5">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">Start Date</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $subscription->start_date->format('M j, Y') }}</dd>
                </div>
                <div class="bg-white dark:bg-gray-800 px-4 py-5">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">Next Billing Date</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">
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
                    <div class="bg-gray-50 dark:bg-gray-700 px-4 py-5">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">Cancellation Date</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $subscription->cancellation_date->format('M j, Y') }}</dd>
                    </div>
                @endif
            </dl>
        </div>
    </div>

    <!-- Management Section -->
    @if($subscription->cancellation_difficulty || $subscription->notes || $subscription->tags)
        <div class="mt-8 bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                    Management Information
                </h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
                    Additional details for managing this subscription.
                </p>
            </div>
            <div class="border-t border-gray-200 dark:border-gray-700">
                <dl>
                    @if($subscription->cancellation_difficulty)
                        <div class="bg-gray-50 dark:bg-gray-700 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">Cancellation Difficulty</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2">
                                <div class="flex items-center">
                                    @for($i = 1; $i <= 5; $i++)
                                        <svg class="h-4 w-4 {{ $i <= $subscription->cancellation_difficulty ? 'text-yellow-400' : 'text-gray-300 dark:text-gray-600' }}" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                        </svg>
                                    @endfor
                                    <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">
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
                        <div class="bg-white dark:bg-gray-800 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">Tags</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2">
                                <div class="flex flex-wrap gap-2">
                                    @foreach($subscription->tags as $tag)
                                        <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                                            {{ $tag }}
                                        </span>
                                    @endforeach
                                </div>
                            </dd>
                        </div>
                    @endif

                    @if($subscription->notes)
                        <div class="bg-gray-50 dark:bg-gray-700 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">Notes</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2">{{ $subscription->notes }}</dd>
                        </div>
                    @endif
                </dl>
            </div>
        </div>
    @endif

    <!-- Price History -->
    @if($subscription->price_history && count($subscription->price_history) > 0)
        <div class="mt-8 bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                    Price History
                </h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
                    Track how the subscription price has changed over time.
                </p>
            </div>
            <div class="border-t border-gray-200 dark:border-gray-700">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Price</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Change</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($subscription->price_history as $index => $history)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                        {{ \Carbon\Carbon::parse($history['date'])->format('M j, Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
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
                                                <span class="text-red-600 dark:text-red-400">
                                                    +{{ $subscription->currency }}{{ number_format($change, 2) }} (+{{ number_format($changePercent, 1) }}%)
                                                </span>
                                            @elseif($change < 0)
                                                <span class="text-green-600 dark:text-green-400">
                                                    {{ $subscription->currency }}{{ number_format($change, 2) }} ({{ number_format($changePercent, 1) }}%)
                                                </span>
                                            @else
                                                <span class="text-gray-500 dark:text-gray-400">No change</span>
                                            @endif
                                        @else
                                            <span class="text-gray-500 dark:text-gray-400">Initial price</span>
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
    <div class="mt-8 bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                Actions
            </h3>
            <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
                Manage your subscription status.
            </p>
        </div>
        <div class="border-t border-gray-200 dark:border-gray-700 px-4 py-5 sm:px-6">
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('subscriptions.edit', $subscription) }}" class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                    Edit Subscription
                </a>

                @if($subscription->status === 'active')
                    <form method="POST" action="{{ route('subscriptions.pause', $subscription) }}" class="inline">
                        @csrf
                        <button type="submit" class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-md text-sm font-medium" onclick="return confirm('Are you sure you want to pause this subscription?')">
                            Pause Subscription
                        </button>
                    </form>
                    <form method="POST" action="{{ route('subscriptions.cancel', $subscription) }}" class="inline">
                        @csrf
                        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md text-sm font-medium" onclick="return confirm('Are you sure you want to cancel this subscription?')">
                            Cancel Subscription
                        </button>
                    </form>
                @elseif($subscription->status === 'paused')
                    <form method="POST" action="{{ route('subscriptions.resume', $subscription) }}" class="inline">
                        @csrf
                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                            Resume Subscription
                        </button>
                    </form>
                    <form method="POST" action="{{ route('subscriptions.cancel', $subscription) }}" class="inline">
                        @csrf
                        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md text-sm font-medium" onclick="return confirm('Are you sure you want to cancel this subscription?')">
                            Cancel Subscription
                        </button>
                    </form>
                @elseif($subscription->status === 'cancelled')
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        This subscription has been cancelled and cannot be modified.
                    </div>
                @endif

                <form method="POST" action="{{ route('subscriptions.destroy', $subscription) }}" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm font-medium" onclick="return confirm('Are you sure you want to delete this subscription? This action cannot be undone.')">
                        Delete Subscription
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection
