@extends('layouts.app')

@section('title', 'Utility Bill Details - LifeOS')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">{{ ucfirst($utilityBill->utility_type) }} Bill</h1>
                <p class="text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] mt-2">{{ $utilityBill->service_provider }}</p>
            </div>
            <div class="flex space-x-3">
                <form method="POST" action="{{ route('utility-bills.duplicate', $utilityBill) }}">
                    @csrf
                    <x-button type="submit" variant="secondary">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h8m-8 4h8m-8 4h5"></path>
                        </svg>
                        Duplicate
                    </x-button>
                </form>
                <x-button href="{{ route('utility-bills.edit', $utilityBill) }}" variant="secondary">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Edit
                </x-button>
                <x-button href="{{ route('utility-bills.index') }}" variant="secondary">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    Back to Bills
                </x-button>
            </div>
        </div>

        <!-- Bill Status Banner -->
        <div class="mb-8">
            @if($utilityBill->payment_status === 'paid')
                <div class="bg-[color:var(--color-success-50)] dark:bg-[color:var(--color-dark-300)] border border-[color:var(--color-success-200)] dark:border-[color:var(--color-dark-300)] rounded-lg p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-[color:var(--color-success-500)]" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-[color:var(--color-success-600)] dark:text-[color:var(--color-success-500)]">
                                Bill Paid
                            </h3>
                            <div class="mt-1 text-sm text-[color:var(--color-success-600)] dark:text-[color:var(--color-success-500)]">
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
                <div class="bg-[color:var(--color-danger-50)] dark:bg-[color:var(--color-dark-300)] border border-[color:var(--color-danger-200)] dark:border-[color:var(--color-dark-300)] rounded-lg p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-[color:var(--color-danger-500)]" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-[color:var(--color-danger-600)] dark:text-[color:var(--color-danger-500)]">
                                Bill Overdue
                            </h3>
                            <div class="mt-1 text-sm text-[color:var(--color-danger-600)] dark:text-[color:var(--color-danger-500)]">
                                Due {{ $utilityBill->due_date->format('M j, Y') }}
                                @if($utilityBill->is_overdue)
                                    ({{ abs($utilityBill->days_until_due) }} days ago)
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @elseif($utilityBill->days_until_due <= 7 && $utilityBill->days_until_due >= 0)
                <div class="bg-[color:var(--color-warning-50)] dark:bg-[color:var(--color-dark-300)] border border-[color:var(--color-warning-200)] dark:border-[color:var(--color-dark-300)] rounded-lg p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-[color:var(--color-warning-500)]" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-[color:var(--color-warning-600)] dark:text-[color:var(--color-warning-500)]">
                                Bill Due Soon
                            </h3>
                            <div class="mt-1 text-sm text-[color:var(--color-warning-600)] dark:text-[color:var(--color-warning-500)]">
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
                <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow-md rounded-lg p-6 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
                    <h2 class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-6">Basic Information</h2>
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Utility Type</dt>
                            <dd class="mt-1 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] capitalize">{{ str_replace('_', ' ', $utilityBill->utility_type) }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Service Provider</dt>
                            <dd class="mt-1 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">{{ $utilityBill->service_provider }}</dd>
                        </div>
                        @if($utilityBill->account_number)
                        <div>
                            <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Account Number</dt>
                            <dd class="mt-1 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">{{ $utilityBill->account_number }}</dd>
                        </div>
                        @endif
                        <div>
                            <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Payment Status</dt>
                            <dd class="mt-1">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ $utilityBill->payment_status === 'paid' ? 'bg-[color:var(--color-success-50)] text-[color:var(--color-success-600)] dark:bg-[color:var(--color-dark-300)] dark:text-[color:var(--color-success-500)]' :
                                       ($utilityBill->payment_status === 'overdue' ? 'bg-[color:var(--color-danger-50)] text-[color:var(--color-danger-600)] dark:bg-[color:var(--color-dark-300)] dark:text-[color:var(--color-danger-500)]' :
                                       'bg-[color:var(--color-warning-50)] text-[color:var(--color-warning-600)] dark:bg-[color:var(--color-dark-300)] dark:text-[color:var(--color-warning-500)]') }}">
                                    {{ ucfirst($utilityBill->payment_status) }}
                                </span>
                            </dd>
                        </div>
                        @if($utilityBill->service_address)
                        <div class="md:col-span-2">
                            <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Service Address</dt>
                            <dd class="mt-1 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">{{ $utilityBill->service_address }}</dd>
                        </div>
                        @endif
                    </dl>
                </div>

                <!-- Bill Details -->
                <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow-md rounded-lg p-6 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
                    <h2 class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-6">Bill Details</h2>
                    <dl class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Bill Amount</dt>
                            <dd class="mt-1 text-2xl font-semibold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">{{ number_format($utilityBill->bill_amount, 2) }} MKD</dd>
                        </div>
                        @if($utilityBill->usage_amount)
                        <div>
                            <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Usage</dt>
                            <dd class="mt-1 text-lg text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                {{ number_format($utilityBill->usage_amount, 4) }}
                                @if($utilityBill->usage_unit)
                                    {{ $utilityBill->usage_unit }}
                                @endif
                            </dd>
                        </div>
                        @endif
                        @if($utilityBill->rate_per_unit)
                        <div>
                            <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Rate per Unit</dt>
                            <dd class="mt-1 text-lg text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">{{ number_format($utilityBill->rate_per_unit, 6) }} MKD</dd>
                        </div>
                        @endif
                        @if($utilityBill->usage_efficiency)
                        <div>
                            <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Cost Efficiency</dt>
                            <dd class="mt-1 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">{{ number_format($utilityBill->usage_efficiency, 4) }} MKD per unit</dd>
                        </div>
                        @endif
                        @if($utilityBill->budget_alert_threshold)
                        <div>
                            <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Budget Threshold</dt>
                            <dd class="mt-1 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                {{ number_format($utilityBill->budget_alert_threshold, 2) }} MKD
                                @if($utilityBill->is_over_budget)
                                    <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-[color:var(--color-danger-50)] text-[color:var(--color-danger-600)] dark:bg-[color:var(--color-dark-300)] dark:text-[color:var(--color-danger-500)]">
                                        Over Budget
                                    </span>
                                @endif
                            </dd>
                        </div>
                        @endif
                        <div>
                            <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Cost per Day</dt>
                            <dd class="mt-1 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">{{ number_format($utilityBill->cost_per_day, 2) }} MKD</dd>
                        </div>
                    </dl>
                </div>

                <!-- Billing Period -->
                <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow-md rounded-lg p-6 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
                    <h2 class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-6">Billing Period</h2>
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Period Start</dt>
                            <dd class="mt-1 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">{{ $utilityBill->bill_period_start->format('M j, Y') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Period End</dt>
                            <dd class="mt-1 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">{{ $utilityBill->bill_period_end->format('M j, Y') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Due Date</dt>
                            <dd class="mt-1 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">{{ $utilityBill->due_date->format('M j, Y') }}</dd>
                        </div>
                        @if($utilityBill->payment_date)
                        <div>
                            <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Payment Date</dt>
                            <dd class="mt-1 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">{{ $utilityBill->payment_date->format('M j, Y') }}</dd>
                        </div>
                        @endif
                        <div>
                            <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Billing Period</dt>
                            <dd class="mt-1 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">{{ $utilityBill->billing_period_days }} days</dd>
                        </div>
                    </dl>
                </div>

                <!-- Additional Information -->
                @if($utilityBill->service_plan || $utilityBill->contract_terms || $utilityBill->notes)
                <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow-md rounded-lg p-6 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
                    <h2 class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-6">Additional Information</h2>
                    <dl class="space-y-4">
                        @if($utilityBill->service_plan)
                        <div>
                            <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Service Plan</dt>
                            <dd class="mt-1 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">{{ $utilityBill->service_plan }}</dd>
                        </div>
                        @endif
                        @if($utilityBill->contract_terms)
                        <div>
                            <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Contract Terms</dt>
                            <dd class="mt-1 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] whitespace-pre-wrap">{{ $utilityBill->contract_terms }}</dd>
                        </div>
                        @endif
                        @if($utilityBill->notes)
                        <div>
                            <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Notes</dt>
                            <dd class="mt-1 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] whitespace-pre-wrap">{{ $utilityBill->notes }}</dd>
                        </div>
                        @endif
                    </dl>
                </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Quick Actions -->
                <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow-md rounded-lg p-6 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
                    <h3 class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-4">Quick Actions</h3>
                    <div class="space-y-3">
                        @if($utilityBill->payment_status !== 'paid')
                        <form action="{{ route('utility-bills.mark-paid', $utilityBill) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <x-button type="submit" variant="primary" class="w-full">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Mark as Paid
                            </x-button>
                        </form>
                        @endif
                        <x-button href="{{ route('utility-bills.edit', $utilityBill) }}" variant="secondary" class="w-full">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            Edit Bill
                        </x-button>
                        <form action="{{ route('utility-bills.destroy', $utilityBill) }}" method="POST"
                              onsubmit="return confirm('Are you sure you want to delete this utility bill?')">
                            @csrf
                            @method('DELETE')
                            <x-button type="submit" variant="danger" class="w-full">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1 1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                                Delete Bill
                            </x-button>
                        </form>
                    </div>
                </div>

                <!-- Settings -->
                <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow-md rounded-lg p-6 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
                    <h3 class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-4">Settings</h3>
                    <dl class="space-y-3">
                        <div>
                            <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Auto-pay</dt>
                            <dd class="mt-1">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ $utilityBill->auto_pay_enabled ? 'bg-[color:var(--color-success-50)] text-[color:var(--color-success-600)] dark:bg-[color:var(--color-dark-300)] dark:text-[color:var(--color-success-500)]' : 'bg-[color:var(--color-primary-200)] text-[color:var(--color-primary-700)] dark:bg-[color:var(--color-dark-300)] dark:text-[color:var(--color-dark-500)]' }}">
                                    {{ $utilityBill->auto_pay_enabled ? 'Enabled' : 'Disabled' }}
                                </span>
                            </dd>
                        </div>
                    </dl>
                </div>

                <!-- Usage Comparison -->
                @if($utilityBill->usage_comparison)
                <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow-md rounded-lg p-6 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
                    <h3 class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-4">Usage Comparison</h3>
                    <div class="text-center">
                        <div class="text-2xl font-semibold {{ $utilityBill->usage_comparison > 0 ? 'text-[color:var(--color-danger-600)] dark:text-[color:var(--color-danger-400)]' : 'text-[color:var(--color-success-600)] dark:text-[color:var(--color-success-400)]' }}">
                            {{ $utilityBill->usage_comparison > 0 ? '+' : '' }}{{ number_format($utilityBill->usage_comparison, 1) }}%
                        </div>
                        <div class="text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] mt-1">
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
