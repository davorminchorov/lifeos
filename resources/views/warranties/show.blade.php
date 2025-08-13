@extends('layouts.app')

@section('title', $warranty->product_name . ' - Warranties - LifeOS')

@section('header')
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                {{ $warranty->product_name }}
            </h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">
                {{ $warranty->brand }} {{ $warranty->model ? '- ' . $warranty->model : '' }}
            </p>
        </div>
        <div class="flex space-x-3">
            @if($warranty->current_status === 'active' && !$warranty->is_expired)
                <button onclick="openClaimModal({{ $warranty->id }})" class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                    File Claim
                </button>
            @endif
            <a href="{{ route('warranties.edit', $warranty) }}" class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                Edit
            </a>
            <a href="{{ route('warranties.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                Back to List
            </a>
        </div>
    </div>
@endsection

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Product Details -->
        <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                    Product Information
                </h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
                    Details about the product under warranty.
                </p>
            </div>
            <div class="border-t border-gray-200 dark:border-gray-700">
                <dl>
                    <div class="bg-gray-50 dark:bg-gray-700 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">Product Name</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2">{{ $warranty->product_name }}</dd>
                    </div>
                    <div class="bg-white dark:bg-gray-800 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">Brand</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                {{ $warranty->brand }}
                            </span>
                        </dd>
                    </div>
                    @if($warranty->model)
                        <div class="bg-gray-50 dark:bg-gray-700 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">Model</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2">{{ $warranty->model }}</dd>
                        </div>
                    @endif
                    @if($warranty->serial_number)
                        <div class="bg-white dark:bg-gray-800 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">Serial Number</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2">
                                <code class="bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded text-sm">{{ $warranty->serial_number }}</code>
                            </dd>
                        </div>
                    @endif
                    <div class="bg-gray-50 dark:bg-gray-700 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">Current Status</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2">
                            @if($warranty->current_status === 'active')
                                @if($warranty->is_expired)
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                        Expired
                                    </span>
                                @else
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                        Active
                                    </span>
                                @endif
                            @elseif($warranty->current_status === 'claimed')
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                    Claimed
                                </span>
                            @elseif($warranty->current_status === 'transferred')
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">
                                    Transferred
                                </span>
                            @else
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200">
                                    {{ ucfirst($warranty->current_status) }}
                                </span>
                            @endif
                        </dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- Warranty Information -->
        <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                    Warranty Coverage
                </h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
                    Warranty terms and coverage details.
                </p>
            </div>
            <div class="border-t border-gray-200 dark:border-gray-700">
                <dl>
                    <div class="bg-gray-50 dark:bg-gray-700 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">Warranty Type</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200">
                                {{ ucfirst($warranty->warranty_type) }}
                            </span>
                        </dd>
                    </div>
                    <div class="bg-white dark:bg-gray-800 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">Duration</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2">
                            <div class="text-lg font-semibold">{{ $warranty->warranty_duration_months }} months</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                @php
                                    $remaining = $warranty->warranty_remaining_percentage;
                                @endphp
                                @if($remaining > 0)
                                    {{ number_format($remaining, 1) }}% remaining
                                @else
                                    Coverage expired
                                @endif
                            </div>
                        </dd>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">Expiration Date</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2">
                            <div class="text-lg font-semibold">{{ $warranty->warranty_expiration_date->format('M j, Y') }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                @php
                                    $daysUntil = $warranty->days_until_expiration;
                                @endphp
                                @if($daysUntil > 0)
                                    <span class="text-green-600 dark:text-green-400">{{ $daysUntil }} days remaining</span>
                                @elseif($daysUntil === 0)
                                    <span class="text-orange-600 dark:text-orange-400">Expires today</span>
                                @else
                                    <span class="text-red-600 dark:text-red-400">Expired {{ abs($daysUntil) }} days ago</span>
                                @endif
                            </div>
                        </dd>
                    </div>
                    @if($warranty->warranty_terms)
                        <div class="bg-white dark:bg-gray-800 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">Terms & Conditions</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2">
                                <div class="whitespace-pre-wrap">{{ $warranty->warranty_terms }}</div>
                            </dd>
                        </div>
                    @endif
                </dl>
            </div>
        </div>

        <!-- Purchase Information -->
        <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                    Purchase Information
                </h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
                    Details about the original purchase.
                </p>
            </div>
            <div class="border-t border-gray-200 dark:border-gray-700">
                <dl>
                    <div class="bg-gray-50 dark:bg-gray-700 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">Purchase Date</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2">
                            <div class="text-lg font-semibold">{{ $warranty->purchase_date->format('M j, Y') }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                {{ $warranty->purchase_date->diffForHumans() }}
                            </div>
                        </dd>
                    </div>
                    <div class="bg-white dark:bg-gray-800 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">Purchase Price</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2">
                            <div class="text-lg font-semibold">${{ number_format($warranty->purchase_price, 2) }}</div>
                        </dd>
                    </div>
                    @if($warranty->retailer)
                        <div class="bg-gray-50 dark:bg-gray-700 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">Retailer</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2">{{ $warranty->retailer }}</dd>
                        </div>
                    @endif
                </dl>
            </div>
        </div>

        <!-- Claims History -->
        <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                    Claims History
                </h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
                    Previous warranty claims and their status.
                </p>
            </div>
            <div class="border-t border-gray-200 dark:border-gray-700">
                @if($warranty->has_claims)
                    <div class="px-4 py-5 sm:px-6">
                        <div class="space-y-4">
                            @foreach($warranty->claim_history as $index => $claim)
                                <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <h4 class="font-medium text-gray-900 dark:text-white">Claim #{{ $index + 1 }}</h4>
                                            @if(isset($claim['description']))
                                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ $claim['description'] }}</p>
                                            @endif
                                            @if(isset($claim['date']))
                                                <p class="text-xs text-gray-500 dark:text-gray-500 mt-2">Filed on {{ \Carbon\Carbon::parse($claim['date'])->format('M j, Y') }}</p>
                                            @endif
                                        </div>
                                        @if(isset($claim['status']))
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                                @if($claim['status'] === 'approved') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                                @elseif($claim['status'] === 'denied') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                                @else bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200 @endif">
                                                {{ ucfirst($claim['status']) }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="px-4 py-5 sm:px-6 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No claims filed</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">This warranty hasn't been used yet.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Attachments -->
        @if($warranty->receipt_attachments || $warranty->proof_of_purchase_attachments)
        <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg lg:col-span-2">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                    Attachments
                </h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
                    Receipts and proof of purchase documents.
                </p>
            </div>
            <div class="border-t border-gray-200 dark:border-gray-700">
                <div class="px-4 py-5 sm:px-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @if($warranty->receipt_attachments)
                        <div>
                            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Receipt/Invoice Files</h4>
                            <div class="space-y-2">
                                @foreach($warranty->receipt_attachments as $file)
                                    <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded">
                                        <div class="flex items-center">
                                            <svg class="h-5 w-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                            <span class="text-sm text-gray-600 dark:text-gray-300">{{ basename($file) }}</span>
                                        </div>
                                        <a href="{{ Storage::url($file) }}" target="_blank" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 text-sm">View</a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        @if($warranty->proof_of_purchase_attachments)
                        <div>
                            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Proof of Purchase Files</h4>
                            <div class="space-y-2">
                                @foreach($warranty->proof_of_purchase_attachments as $file)
                                    <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded">
                                        <div class="flex items-center">
                                            <svg class="h-5 w-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                            <span class="text-sm text-gray-600 dark:text-gray-300">{{ basename($file) }}</span>
                                        </div>
                                        <a href="{{ Storage::url($file) }}" target="_blank" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 text-sm">View</a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Notes -->
        @if($warranty->notes)
        <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg lg:col-span-2">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                    Notes
                </h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
                    Additional information and reminders.
                </p>
            </div>
            <div class="border-t border-gray-200 dark:border-gray-700">
                <div class="px-4 py-5 sm:px-6">
                    <div class="prose dark:prose-invert max-w-none">
                        <p class="text-sm text-gray-900 dark:text-white whitespace-pre-wrap">{{ $warranty->notes }}</p>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
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
