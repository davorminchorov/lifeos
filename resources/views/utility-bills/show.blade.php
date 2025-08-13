@extends('layouts.app')

@section('title', 'Utility Bill Details - LifeOS')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ ucfirst($utilityBill->utility_type) }} Bill</h1>
                <p class="text-gray-600 dark:text-gray-400 mt-2">{{ $utilityBill->service_provider }}</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('utility-bills.edit', $utilityBill) }}"
                   class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Edit
                </a>
                <a href="{{ route('utility-bills.index') }}"
                   class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    Back to Bills
                </a>
            </div>
        </div>

        <!-- Bill Status Banner -->
        <div class="mb-8">
            @if($utilityBill->payment_status === 'paid')
                <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 rounded-lg p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-green-800 dark:text-green-200">
                                Bill Paid
                            </h3>
                            <div class="mt-1 text-sm text-green-700 dark:text-green-300">
                                @if($utilityBill->payment_date)
                                    Paid on {{ $utilityBill->payment_date->format('M j, Y') }}
                                @else
                                    Payment recorded
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @elseif($utilityBill->is_overdue)
                <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 rounded-lg p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800 dark:text-red-200">
                                Bill Overdue
                            </h3>
                            <div class="mt-1 text-sm text-red-700 dark:text-red-300">
                                Due {{ $utilityBill->due_date->format('M j, Y') }} ({{ abs($utilityBill->days_until_due) }} days ago)
                            </div>
                        </div>
                    </div>
                </div>
            @elseif($utilityBill->days_until_due <= 7 && $utilityBill->days_until_due >= 0)
                <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 rounded-lg p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                                Bill Due Soon
                            </h3>
                            <div class="mt-1 text-sm text-yellow-700 dark:text-yellow-300">
                                Due {{ $utilityBill->due_date->format('M j, Y') }}
                                @if($utilityBill->days_until_due == 0)
                                    (today)
                                @elseif($utilityBill->days_until_due == 1)
                                    (tomorrow)
                                @else
                                    (in {{ $utilityBill->days_until_due }} days)
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Bill Details -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Information -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Basic Information -->
                <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
                    <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-6">Basic Information</h2>
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Utility Type</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white capitalize">{{ str_replace('_', ' ', $utilityBill->utility_type) }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Service Provider</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $utilityBill->service_provider }}</dd>
                        </div>
                        @if($utilityBill->account_number)
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Account Number</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $utilityBill->account_number }}</dd>
                        </div>
                        @endif
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Payment Status</dt>
                            <dd class="mt-1">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ $utilityBill->payment_status === 'paid' ? 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400' :
                                       ($utilityBill->payment_status === 'overdue' ? 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400' :
                                       'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400') }}">
                                    {{ ucfirst($utilityBill->payment_status) }}
                                </span>
                            </dd>
                        </div>
                        @if($utilityBill->service_address)
                        <div class="md:col-span-2">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Service Address</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $utilityBill->service_address }}</dd>
                        </div>
                        @endif
                    </dl>
                </div>

                <!-- Bill Details -->
                <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
                    <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-6">Bill Details</h2>
                    <dl class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Bill Amount</dt>
                            <dd class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">${{ number_format($utilityBill->bill_amount, 2) }}</dd>
                        </div>
                        @if($utilityBill->usage_amount)
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Usage</dt>
                            <dd class="mt-1 text-lg text-gray-900 dark:text-white">
                                {{ number_format($utilityBill->usage_amount, 4) }}
                                @if($utilityBill->usage_unit)
                                    {{ $utilityBill->usage_unit }}
                                @endif
                            </dd>
                        </div>
                        @endif
                        @if($utilityBill->rate_per_unit)
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Rate per Unit</dt>
                            <dd class="mt-1 text-lg text-gray-900 dark:text-white">${{ number_format($utilityBill->rate_per_unit, 6) }}</dd>
                        </div>
                        @endif
                        @if($utilityBill->usage_efficiency)
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Cost Efficiency</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">${{ number_format($utilityBill->usage_efficiency, 4) }} per unit</dd>
                        </div>
                        @endif
                        @if($utilityBill->budget_alert_threshold)
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Budget Threshold</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                ${{ number_format($utilityBill->budget_alert_threshold, 2) }}
                                @if($utilityBill->is_over_budget)
                                    <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400">
                                        Over Budget
                                    </span>
                                @endif
                            </dd>
                        </div>
                        @endif
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Cost per Day</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">${{ number_format($utilityBill->cost_per_day, 2) }}</dd>
                        </div>
                    </dl>
                </div>

                <!-- Billing Period -->
                <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
                    <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-6">Billing Period</h2>
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Period Start</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $utilityBill->bill_period_start->format('M j, Y') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Period End</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $utilityBill->bill_period_end->format('M j, Y') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Due Date</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $utilityBill->due_date->format('M j, Y') }}</dd>
                        </div>
                        @if($utilityBill->payment_date)
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Payment Date</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $utilityBill->payment_date->format('M j, Y') }}</dd>
                        </div>
                        @endif
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Billing Period</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $utilityBill->billing_period_days }} days</dd>
                        </div>
                    </dl>
                </div>

                <!-- Additional Information -->
                @if($utilityBill->service_plan || $utilityBill->contract_terms || $utilityBill->notes)
                <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
                    <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-6">Additional Information</h2>
                    <dl class="space-y-4">
                        @if($utilityBill->service_plan)
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Service Plan</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $utilityBill->service_plan }}</dd>
                        </div>
                        @endif
                        @if($utilityBill->contract_terms)
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Contract Terms</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white whitespace-pre-wrap">{{ $utilityBill->contract_terms }}</dd>
                        </div>
                        @endif
                        @if($utilityBill->notes)
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Notes</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white whitespace-pre-wrap">{{ $utilityBill->notes }}</dd>
                        </div>
                        @endif
                    </dl>
                </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Quick Actions -->
                <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Quick Actions</h3>
                    <div class="space-y-3">
                        @if($utilityBill->payment_status !== 'paid')
                        <form action="{{ route('utility-bills.mark-paid', $utilityBill) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Mark as Paid
                            </button>
                        </form>
                        @endif
                        <a href="{{ route('utility-bills.edit', $utilityBill) }}"
                           class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            Edit Bill
                        </a>
                        <form action="{{ route('utility-bills.destroy', $utilityBill) }}" method="POST"
                              onsubmit="return confirm('Are you sure you want to delete this utility bill?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 border border-red-300 dark:border-red-600 rounded-md shadow-sm text-sm font-medium text-red-700 dark:text-red-400 bg-white dark:bg-gray-700 hover:bg-red-50 dark:hover:bg-red-900/20">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                                Delete Bill
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Settings -->
                <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Settings</h3>
                    <dl class="space-y-3">
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Auto-pay</dt>
                            <dd class="mt-1">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ $utilityBill->auto_pay_enabled ? 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400' : 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400' }}">
                                    {{ $utilityBill->auto_pay_enabled ? 'Enabled' : 'Disabled' }}
                                </span>
                            </dd>
                        </div>
                    </dl>
                </div>

                <!-- Usage Comparison -->
                @if($utilityBill->usage_comparison)
                <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Usage Comparison</h3>
                    <div class="text-center">
                        <div class="text-2xl font-semibold {{ $utilityBill->usage_comparison > 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">
                            {{ $utilityBill->usage_comparison > 0 ? '+' : '' }}{{ number_format($utilityBill->usage_comparison, 1) }}%
                        </div>
                        <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            vs previous period
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
