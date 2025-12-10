@extends('layouts.app')

@section('title', 'Warranties - LifeOS')

@section('header')
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                Warranties
            </h1>
            <p class="mt-2 text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">
                Track your product warranties and never miss important dates
            </p>
        </div>
        <div class="flex-shrink-0">
            <x-button href="{{ route('warranties.create') }}" variant="primary" class="w-full sm:w-auto">
                Add Warranty
            </x-button>
        </div>
    </div>
@endsection

@section('content')
    <!-- Filters and Search -->
    <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow rounded-lg mb-6 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
        <div class="p-6">
            <form method="GET" action="{{ route('warranties.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Search -->
                <div>
                    <x-form.input
                        name="search"
                        label="Search"
                        type="text"
                        :value="request('search')"
                        placeholder="Search warranties..."
                    />
                </div>

                <!-- Status Filter -->
                <div>
                    <x-form.select name="status" label="Status" placeholder="All Status">
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expired</option>
                        <option value="claimed" {{ request('status') === 'claimed' ? 'selected' : '' }}>Claimed</option>
                        <option value="transferred" {{ request('status') === 'transferred' ? 'selected' : '' }}>Transferred</option>
                    </x-form.select>
                </div>

                <!-- Warranty Type Filter -->
                <div>
                    <x-form.select name="warranty_type" label="Type" placeholder="All Types">
                        <option value="manufacturer" {{ request('warranty_type') === 'manufacturer' ? 'selected' : '' }}>Manufacturer</option>
                        <option value="extended" {{ request('warranty_type') === 'extended' ? 'selected' : '' }}>Extended</option>
                        <option value="store" {{ request('warranty_type') === 'store' ? 'selected' : '' }}>Store</option>
                    </x-form.select>
                </div>

                <!-- Expiring Soon -->
                <div>
                    <x-form.select name="expiring_soon" label="Expiring Soon" placeholder="All">
                        <option value="7" {{ request('expiring_soon') === '7' ? 'selected' : '' }}>Expiring in 7 days</option>
                        <option value="30" {{ request('expiring_soon') === '30' ? 'selected' : '' }}>Expiring in 30 days</option>
                        <option value="90" {{ request('expiring_soon') === '90' ? 'selected' : '' }}>Expiring in 90 days</option>
                    </x-form.select>
                </div>

                <div class="col-span-full">
                    <x-button type="submit" variant="primary">Apply Filters</x-button>
                    <x-button href="{{ route('warranties.index') }}" variant="secondary" class="ml-2">Clear</x-button>
                </div>
            </form>
        </div>
    </div>

    <!-- Warranties Table -->
    <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow overflow-hidden sm:rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
        <div class="px-4 py-5 sm:p-6">
            @if($warranties->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-[color:var(--color-primary-300)] dark:divide-[color:var(--color-dark-300)]">
                        <thead class="bg-[color:var(--color-primary-200)] dark:bg-[color:var(--color-dark-300)]">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">Product</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">Brand</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">Purchase Price</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">Warranty Expiration</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] divide-y divide-[color:var(--color-primary-300)] dark:divide-[color:var(--color-dark-300)]">
                            @foreach($warranties as $warranty)
                                <tr class="hover:bg-[color:var(--color-primary-200)] dark:hover:bg-[color:var(--color-dark-300)]">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div>
                                                <div class="text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                                    {{ $warranty->product_name }}
                                                </div>
                                                @if($warranty->model)
                                                    <div class="text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">
                                                        Model: {{ $warranty->model }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-[color:var(--color-info-50)] text-[color:var(--color-info-600)] dark:bg-[color:var(--color-dark-300)] dark:text-[color:var(--color-info-500)]">
                                            {{ $warranty->brand }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                        <div>{{ $warranty->formatted_purchase_price }}</div>
                                        <div class="text-xs text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">{{ ucfirst($warranty->warranty_type) }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                        {{ $warranty->warranty_expiration_date->format('M j, Y') }}
                                        @php
                                            $daysUntil = $warranty->days_until_expiration;
                                        @endphp
                                        @if($daysUntil <= 30 && $daysUntil >= 0)
                                            <div class="text-xs text-[color:var(--color-warning-600)]">
                                                @if($daysUntil === 0)
                                                    Expires today
                                                @elseif($daysUntil === 1)
                                                    Expires tomorrow
                                                @else
                                                    Expires in {{ $daysUntil }} days
                                                @endif
                                            </div>
                                        @elseif($daysUntil < 0)
                                            <div class="text-xs text-[color:var(--color-danger-600)] dark:text-[color:var(--color-danger-400)]">Expired {{ abs($daysUntil) }} days ago</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($warranty->current_status === 'active')
                                            @if($warranty->is_expired)
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-[color:var(--color-danger-50)] text-[color:var(--color-danger-600)] dark:bg-[color:var(--color-dark-300)] dark:text-[color:var(--color-danger-500)]">
                                                    Expired
                                                </span>
                                            @else
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-[color:var(--color-success-50)] text-[color:var(--color-success-600)] dark:bg-[color:var(--color-dark-300)] dark:text-[color:var(--color-success-500)]">
                                                    Active
                                                </span>
                                            @endif
                                        @elseif($warranty->current_status === 'claimed')
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-[color:var(--color-warning-50)] text-[color:var(--color-warning-600)] dark:bg-[color:var(--color-dark-300)] dark:text-[color:var(--color-warning-500)]">
                                                Claimed
                                            </span>
                                        @elseif($warranty->current_status === 'transferred')
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-[color:var(--color-accent-50)] text-[color:var(--color-accent-600)] dark:bg-[color:var(--color-dark-300)] dark:text-[color:var(--color-accent-500)]">
                                                Transferred
                                            </span>
                                        @else
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-[color:var(--color-primary-200)] text-[color:var(--color-primary-700)] dark:bg-[color:var(--color-dark-300)] dark:text-[color:var(--color-dark-600)]">
                                                {{ ucfirst($warranty->current_status) }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <a href="{{ route('warranties.show', $warranty) }}" class="text-[color:var(--color-accent-600)] hover:text-[color:var(--color-accent-700)]">View</a>
                                            <a href="{{ route('warranties.edit', $warranty) }}" class="text-[color:var(--color-warning-600)] hover:text-[color:var(--color-warning-700)]">Edit</a>
                                            @if($warranty->current_status === 'active' && !$warranty->is_expired)
                                                <button type="button" onclick="openClaimModal({{ $warranty->id }})" class="text-[color:var(--color-warning-600)] hover:text-[color:var(--color-warning-700)]">Claim</button>
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
                    {{ $warranties->withQueryString()->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-[color:var(--color-primary-400)] dark:text-[color:var(--color-dark-400)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">No warranties</h3>
                    <p class="mt-1 text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Get started by adding your first warranty.</p>
                    <div class="mt-6">
                        <x-button href="{{ route('warranties.create') }}" variant="primary">Add Warranty</x-button>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Summary Stats -->
    @if($warranties->count() > 0)
        <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-6">
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
                                <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] truncate">Active Warranties</dt>
                                <dd class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                    {{ $warranties->where('current_status', 'active')->where('is_expired', false)->count() }}
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
                            <svg class="h-6 w-6 text-[color:var(--color-warning-600)] dark:text-[color:var(--color-warning-500)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] truncate">Expiring Soon</dt>
                                <dd class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                    {{ $warranties->where('current_status', 'active')->filter(function($warranty) { return $warranty->days_until_expiration >= 0 && $warranty->days_until_expiration <= 30; })->count() }}
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
                            <svg class="h-6 w-6 text-[color:var(--color-info-600)] dark:text-[color:var(--color-info-500)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] truncate">Total Coverage Value</dt>
                                <dd class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                    @php
                                        $currencyService = app(\App\Services\CurrencyService::class);
                                        $totalCoverageValue = $warranties->where('current_status', 'active')->where('is_expired', false)->sum('purchase_price');
                                    @endphp
                                    {{ $currencyService->format($totalCoverageValue) }}
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

@push('scripts')
<script>
function openClaimModal(warrantyId) {
    // This would open a modal for creating a warranty claim
    // For now, we'll just redirect to a claim creation form
    window.location.href = `/warranties/${warrantyId}/claim`;
}
</script>
@endpush
