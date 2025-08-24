@extends('layouts.app')

@section('title', 'Utility Bills - LifeOS')

@section('header')
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                Utility Bills
            </h1>
            <p class="mt-2 text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">
                Track your utility bills and monitor usage patterns
            </p>
        </div>
        <a href="{{ route('utility-bills.create') }}" class="bg-[color:var(--color-accent-600)] hover:bg-[color:var(--color-accent-700)] text-white px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200 shadow-sm">
            Add Utility Bill
        </a>
    </div>
@endsection

@section('content')
    <!-- Filters and Search -->
    <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow rounded-lg mb-6 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
        <div class="px-4 py-5 sm:px-6">
            <form method="GET" action="{{ route('utility-bills.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Search -->
                <div>
                    <x-form.input
                        name="search"
                        label="Search"
                        type="text"
                        placeholder="Search utility bills..."
                    />
                </div>

                <!-- Utility Type Filter -->
                <div>
                    <x-form.select
                        name="utility_type"
                        label="Utility Type"
                        placeholder="All Types"
                    >
                        <option value="electricity" {{ request('utility_type') === 'electricity' ? 'selected' : '' }}>Electricity</option>
                        <option value="gas" {{ request('utility_type') === 'gas' ? 'selected' : '' }}>Gas</option>
                        <option value="water" {{ request('utility_type') === 'water' ? 'selected' : '' }}>Water</option>
                        <option value="internet" {{ request('utility_type') === 'internet' ? 'selected' : '' }}>Internet</option>
                        <option value="phone" {{ request('utility_type') === 'phone' ? 'selected' : '' }}>Phone</option>
                        <option value="cable_tv" {{ request('utility_type') === 'cable_tv' ? 'selected' : '' }}>Cable TV</option>
                        <option value="trash" {{ request('utility_type') === 'trash' ? 'selected' : '' }}>Trash</option>
                        <option value="sewer" {{ request('utility_type') === 'sewer' ? 'selected' : '' }}>Sewer</option>
                    </x-form.select>
                </div>

                <!-- Payment Status Filter -->
                <div>
                    <x-form.select
                        name="payment_status"
                        label="Payment Status"
                        placeholder="All Status"
                    >
                        <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="pending" {{ request('payment_status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="overdue" {{ request('payment_status') === 'overdue' ? 'selected' : '' }}>Overdue</option>
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
                    <button type="submit" class="bg-[color:var(--color-accent-500)] hover:bg-[color:var(--color-accent-600)] text-white px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200 shadow-sm">
                        Apply Filters
                    </button>
                    <a href="{{ route('utility-bills.index') }}" class="ml-2 bg-[color:var(--color-primary-200)] hover:bg-[color:var(--color-primary-300)] text-[color:var(--color-primary-700)] dark:bg-[color:var(--color-dark-300)] dark:hover:bg-[color:var(--color-dark-400)] dark:text-[color:var(--color-dark-600)] px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200">
                        Clear
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Utility Bills Table -->
    <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow overflow-hidden sm:rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]" x-data="{}">
        <div class="px-4 py-5 sm:p-6">
            @if($utilityBills->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-[color:var(--color-primary-300)] dark:divide-[color:var(--color-dark-300)]">
                        <thead class="bg-[color:var(--color-primary-200)] dark:bg-[color:var(--color-dark-300)]">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">Utility</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">Provider</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">Bill Period</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">Amount</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">Usage</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">Due Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] divide-y divide-[color:var(--color-primary-300)] dark:divide-[color:var(--color-dark-300)]">
                            @foreach($utilityBills as $bill)
                                @php
                                    $daysUntilDue = $bill->due_date ? (int) round(now()->diffInDays($bill->due_date, false)) : null;
                                    $isOverdue = $bill->is_overdue;
                                    $isDueSoon = $daysUntilDue !== null && $daysUntilDue <= 7 && $daysUntilDue >= 0;
                                @endphp
                                <tr class="hover:bg-[color:var(--color-primary-200)] dark:hover:bg-[color:var(--color-dark-300)] {{ $isOverdue ? 'bg-[color:var(--color-danger-50)] dark:bg-[color:var(--color-danger-800)]/20' : ($isDueSoon ? 'bg-[color:var(--color-warning-50)] dark:bg-[color:var(--color-warning-800)]/20' : '') }}">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div>
                                                <div class="text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                                    {{ ucfirst(str_replace('_', ' ', $bill->utility_type)) }}
                                                </div>
                                                @if($bill->account_number)
                                                    <div class="text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">
                                                        Account: {{ $bill->account_number }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                        {{ $bill->service_provider }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                        {{ $bill->bill_period_start->format('M d') }} - {{ $bill->bill_period_end->format('M d, Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                        {{ $bill->formatted_bill_amount_mkd }}
                                        @if($bill->currency && $bill->currency !== config('currency.default', 'MKD'))
                                            <span class="text-xs text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] ml-1">
                                                ({{ $bill->formatted_bill_amount }})
                                            </span>
                                        @endif
                                        @if($bill->rate_per_unit)
                                            <div class="text-xs text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">
                                                MKD {{ number_format($bill->rate_per_unit, 4) }}/{{ $bill->usage_unit }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                        @if($bill->usage_amount)
                                            {{ number_format($bill->usage_amount, 2) }} {{ $bill->usage_unit }}
                                            @if($bill->previous_reading && $bill->current_reading)
                                                <div class="text-xs text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">
                                                    {{ number_format($bill->previous_reading) }} → {{ number_format($bill->current_reading) }}
                                                </div>
                                            @endif
                                        @else
                                            <span class="text-[color:var(--color-primary-400)] dark:text-[color:var(--color-dark-400)]">N/A</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                        {{ $bill->due_date->format('M d, Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($bill->payment_status === 'paid')
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-[color:var(--color-success-50)] text-[color:var(--color-success-600)] dark:bg-[color:var(--color-dark-300)] dark:text-[color:var(--color-success-500)]">
                                                Paid
                                            </span>
                                        @elseif($bill->payment_status === 'overdue' || $isOverdue)
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-[color:var(--color-danger-50)] text-[color:var(--color-danger-600)] dark:bg-[color:var(--color-dark-300)] dark:text-[color:var(--color-danger-500)]">
                                                Overdue
                                            </span>
                                        @else
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-[color:var(--color-warning-50)] text-[color:var(--color-warning-600)] dark:bg-[color:var(--color-dark-300)] dark:text-[color:var(--color-warning-500)]">
                                                Pending
                                            </span>
                                        @endif
                                        @if($bill->auto_pay_enabled)
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-[color:var(--color-info-50)] text-[color:var(--color-info-600)] dark:bg-[color:var(--color-dark-300)] dark:text-[color:var(--color-info-500)] ml-1">
                                                Auto Pay
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <a href="{{ route('utility-bills.show', $bill) }}" class="text-[color:var(--color-accent-600)] hover:text-[color:var(--color-accent-700)]">View</a>
                                            <a href="{{ route('utility-bills.edit', $bill) }}" class="text-[color:var(--color-warning-600)] hover:text-[color:var(--color-warning-700)]">Edit</a>
                                            @if($bill->payment_status !== 'paid')
                                                <form method="POST" action="{{ route('utility-bills.mark-paid', $bill) }}" class="inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="text-[color:var(--color-success-600)] hover:text-[color:var(--color-success-700)]">
                                                        Mark Paid
                                                    </button>
                                                </form>
                                            @endif
                                            <button type="button"
                                                    class="text-[color:var(--color-danger-600)] hover:text-[color:var(--color-danger-700)]"
                                                    x-on:click="$dispatch('open-modal', { id: 'deleteUtilityBillModal-{{ $bill->id }}' })">
                                                Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-6">
                    {{ $utilityBills->appends(request()->query())->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <div class="text-[color:var(--color-primary-400)] dark:text-[color:var(--color-dark-400)] mb-4">
                        <svg class="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-2">No utility bills found</h3>
                    <p class="text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] mb-4">Get started by adding your first utility bill.</p>
                    <a href="{{ route('utility-bills.create') }}" class="bg-[color:var(--color-accent-600)] hover:bg-[color:var(--color-accent-700)] text-white px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200">
                        Add Utility Bill
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- Delete Modals for each utility bill -->
    @foreach($utilityBills as $bill)
        <x-confirmation-modal
            id="deleteUtilityBillModal-{{ $bill->id }}"
            title="Delete Utility Bill"
            message="Are you sure you want to delete the {{ ucfirst($bill->utility_type) }} bill from {{ $bill->service_provider }}? This action cannot be undone."
            confirm-text="Delete"
            confirm-button-class="bg-[color:var(--color-danger-500)] hover:bg-[color:var(--color-danger-600)] text-white"
            :action="route('utility-bills.destroy', $bill)"
            method="DELETE"
        />
    @endforeach
@endsection
