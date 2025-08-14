@extends('layouts.app')

@section('title', 'Utility Bills - LifeOS')

@section('header')
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                Utility Bills
            </h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">
                Track your utility bills and monitor usage patterns
            </p>
        </div>
        <a href="{{ route('utility-bills.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium">
            Add Utility Bill
        </a>
    </div>
@endsection

@section('content')
    <!-- Filters and Search -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg mb-6">
        <div class="px-4 py-5 sm:px-6">
            <form method="GET" action="{{ route('utility-bills.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Search -->
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Search</label>
                    <input type="text" name="search" id="search" value="{{ request('search') }}"
                           placeholder="Search utility bills..."
                           class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <!-- Utility Type Filter -->
                <div>
                    <label for="utility_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Utility Type</label>
                    <select name="utility_type" id="utility_type" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">All Types</option>
                        <option value="electricity" {{ request('utility_type') === 'electricity' ? 'selected' : '' }}>Electricity</option>
                        <option value="gas" {{ request('utility_type') === 'gas' ? 'selected' : '' }}>Gas</option>
                        <option value="water" {{ request('utility_type') === 'water' ? 'selected' : '' }}>Water</option>
                        <option value="internet" {{ request('utility_type') === 'internet' ? 'selected' : '' }}>Internet</option>
                        <option value="phone" {{ request('utility_type') === 'phone' ? 'selected' : '' }}>Phone</option>
                        <option value="cable_tv" {{ request('utility_type') === 'cable_tv' ? 'selected' : '' }}>Cable TV</option>
                        <option value="trash" {{ request('utility_type') === 'trash' ? 'selected' : '' }}>Trash</option>
                        <option value="sewer" {{ request('utility_type') === 'sewer' ? 'selected' : '' }}>Sewer</option>
                    </select>
                </div>

                <!-- Payment Status Filter -->
                <div>
                    <label for="payment_status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Payment Status</label>
                    <select name="payment_status" id="payment_status" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">All Status</option>
                        <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="pending" {{ request('payment_status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="overdue" {{ request('payment_status') === 'overdue' ? 'selected' : '' }}>Overdue</option>
                    </select>
                </div>

                <!-- Due Soon -->
                <div>
                    <label for="due_soon" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Due Soon</label>
                    <select name="due_soon" id="due_soon" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">All</option>
                        <option value="7" {{ request('due_soon') === '7' ? 'selected' : '' }}>Due in 7 days</option>
                        <option value="14" {{ request('due_soon') === '14' ? 'selected' : '' }}>Due in 14 days</option>
                        <option value="30" {{ request('due_soon') === '30' ? 'selected' : '' }}>Due in 30 days</option>
                    </select>
                </div>

                <div class="col-span-full">
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                        Apply Filters
                    </button>
                    <a href="{{ route('utility-bills.index') }}" class="ml-2 bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-md text-sm font-medium">
                        Clear
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Utility Bills Table -->
    <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            @if($utilityBills->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Utility</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Provider</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Bill Period</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Amount</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Usage</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Due Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($utilityBills as $bill)
                                @php
                                    $daysUntilDue = $bill->due_date ? now()->diffInDays($bill->due_date, false) : null;
                                    $isOverdue = $daysUntilDue !== null && $daysUntilDue < 0;
                                    $isDueSoon = $daysUntilDue !== null && $daysUntilDue <= 7 && $daysUntilDue >= 0;
                                @endphp
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 {{ $isOverdue ? 'bg-red-50 dark:bg-red-900/20' : ($isDueSoon ? 'bg-yellow-50 dark:bg-yellow-900/20' : '') }}">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div>
                                                <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                    {{ ucfirst(str_replace('_', ' ', $bill->utility_type)) }}
                                                </div>
                                                @if($bill->account_number)
                                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                                        Account: {{ $bill->account_number }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                        {{ $bill->service_provider }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                        {{ $bill->bill_period_start->format('M d') }} - {{ $bill->bill_period_end->format('M d, Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                        ${{ number_format($bill->bill_amount, 2) }}
                                        @if($bill->rate_per_unit)
                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                ${{ number_format($bill->rate_per_unit, 4) }}/{{ $bill->usage_unit }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                        @if($bill->usage_amount)
                                            {{ number_format($bill->usage_amount, 2) }} {{ $bill->usage_unit }}
                                            @if($bill->previous_reading && $bill->current_reading)
                                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                                    {{ number_format($bill->previous_reading) }} → {{ number_format($bill->current_reading) }}
                                                </div>
                                            @endif
                                        @else
                                            <span class="text-gray-400 dark:text-gray-600">N/A</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                        {{ $bill->due_date->format('M d, Y') }}
                                        @if($daysUntilDue !== null)
                                            <div class="text-xs {{ $isOverdue ? 'text-red-600 dark:text-red-400' : ($isDueSoon ? 'text-yellow-600 dark:text-yellow-400' : 'text-gray-500 dark:text-gray-400') }}">
                                                @if($isOverdue)
                                                    {{ abs($daysUntilDue) }} days overdue
                                                @elseif($daysUntilDue === 0)
                                                    Due today
                                                @else
                                                    {{ $daysUntilDue }} days left
                                                @endif
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($bill->payment_status === 'paid')
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                                Paid
                                            </span>
                                        @elseif($bill->payment_status === 'overdue' || $isOverdue)
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                                Overdue
                                            </span>
                                        @else
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                                Pending
                                            </span>
                                        @endif
                                        @if($bill->auto_pay_enabled)
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 ml-1">
                                                Auto Pay
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <a href="{{ route('utility-bills.show', $bill) }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">View</a>
                                            <a href="{{ route('utility-bills.edit', $bill) }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">Edit</a>
                                            @if($bill->payment_status !== 'paid')
                                                <form method="POST" action="{{ route('utility-bills.mark-paid', $bill) }}" class="inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300">
                                                        Mark Paid
                                                    </button>
                                                </form>
                                            @endif
                                            <form method="POST" action="{{ route('utility-bills.destroy', $bill) }}" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                                                        onclick="return confirm('Are you sure you want to delete this utility bill?')">
                                                    Delete
                                                </button>
                                            </form>
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
                    <div class="text-gray-400 dark:text-gray-600 mb-4">
                        <svg class="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No utility bills found</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-4">Get started by adding your first utility bill.</p>
                    <a href="{{ route('utility-bills.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                        Add Utility Bill
                    </a>
                </div>
            @endif
        </div>
    </div>
@endsection
