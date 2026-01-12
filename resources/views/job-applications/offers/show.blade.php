@extends('layouts.app')

@section('title', 'Offer Details - ' . $application->job_title . ' - LifeOS')

@section('header')
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                Job Offer Details
            </h1>
            <p class="mt-2 text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">
                {{ $application->job_title }} at {{ $application->company_name }}
            </p>
        </div>
        <div class="flex space-x-3">
            <x-button href="{{ route('job-applications.offers.edit', [$application, $offer]) }}" variant="secondary">
                Edit Offer
            </x-button>
            <x-button href="{{ route('job-applications.show', $application) }}" variant="primary">
                Back to Application
            </x-button>
        </div>
    </div>
@endsection

@section('content')
    <div class="space-y-6">
        <!-- Offer Status Banner -->
        <div class="rounded-lg p-4
            @if($offer->status->value === 'accepted') bg-[color:var(--color-success-50)] dark:bg-[color:var(--color-success-900)]/20 border-2 border-[color:var(--color-success-500)]
            @elseif($offer->status->value === 'declined') bg-[color:var(--color-danger-50)] dark:bg-[color:var(--color-danger-900)]/20 border-2 border-[color:var(--color-danger-500)]
            @elseif($offer->status->value === 'expired') bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-900)]/20 border-2 border-[color:var(--color-primary-500)]
            @elseif($offer->status->value === 'negotiating') bg-[color:var(--color-warning-50)] dark:bg-[color:var(--color-warning-900)]/20 border-2 border-[color:var(--color-warning-500)]
            @else bg-[color:var(--color-info-50)] dark:bg-[color:var(--color-info-900)]/20 border-2 border-[color:var(--color-info-500)]
            @endif">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <span class="text-2xl mr-3">
                        @if($offer->status->value === 'accepted') 🎉
                        @elseif($offer->status->value === 'declined') 😔
                        @elseif($offer->status->value === 'expired') ⏰
                        @elseif($offer->status->value === 'negotiating') 💬
                        @else 📩
                        @endif
                    </span>
                    <div>
                        <h3 class="text-lg font-medium
                            @if($offer->status->value === 'accepted') text-[color:var(--color-success-800)] dark:text-[color:var(--color-success-200)]
                            @elseif($offer->status->value === 'declined') text-[color:var(--color-danger-800)] dark:text-[color:var(--color-danger-200)]
                            @elseif($offer->status->value === 'expired') text-[color:var(--color-primary-800)] dark:text-[color:var(--color-primary-200)]
                            @elseif($offer->status->value === 'negotiating') text-[color:var(--color-warning-800)] dark:text-[color:var(--color-warning-200)]
                            @else text-[color:var(--color-info-800)] dark:text-[color:var(--color-info-200)]
                            @endif">
                            Offer Status: {{ ucfirst($offer->status->value) }}
                        </h3>
                        @if($offer->decision_deadline && $offer->status->value === 'pending')
                            <p class="text-sm
                                @if($offer->decision_deadline->isPast()) text-[color:var(--color-danger-600)] dark:text-[color:var(--color-danger-400)]
                                @elseif($offer->decision_deadline->diffInDays() <= 3) text-[color:var(--color-warning-600)] dark:text-[color:var(--color-warning-400)]
                                @else text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]
                                @endif">
                                Decision deadline: {{ $offer->decision_deadline->format('F j, Y') }}
                                @if($offer->decision_deadline->isPast())
                                    (Expired!)
                                @else
                                    ({{ $offer->decision_deadline->diffForHumans() }})
                                @endif
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Compensation Details -->
        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow overflow-hidden sm:rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                    Compensation Package
                </h3>
                <p class="mt-1 max-w-2xl text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">
                    Salary and benefits information
                </p>
            </div>
            <div class="border-t border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)]">
                <dl>
                    <div class="bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Base Salary</dt>
                        <dd class="mt-1 text-sm sm:mt-0 sm:col-span-2">
                            <span class="text-lg font-semibold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                {{ number_format($offer->base_salary, 2) }} {{ $offer->currency }}
                            </span>
                            <span class="text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]"> / year</span>
                        </dd>
                    </div>
                    @if($offer->bonus)
                        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Bonus</dt>
                            <dd class="mt-1 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] sm:mt-0 sm:col-span-2">
                                {{ number_format($offer->bonus, 2) }} {{ $offer->currency }}
                            </dd>
                        </div>
                    @endif
                    @if($offer->equity)
                        <div class="bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Equity / Stock Options</dt>
                            <dd class="mt-1 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] sm:mt-0 sm:col-span-2">
                                {{ $offer->equity }}
                            </dd>
                        </div>
                    @endif
                    <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Total Compensation</dt>
                        <dd class="mt-1 text-sm sm:mt-0 sm:col-span-2">
                            <span class="text-xl font-bold text-[color:var(--color-success-600)] dark:text-[color:var(--color-success-400)]">
                                {{ number_format($offer->base_salary + ($offer->bonus ?? 0), 2) }} {{ $offer->currency }}
                            </span>
                            @if($offer->equity)
                                <span class="text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]"> + equity</span>
                            @endif
                        </dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- Benefits -->
        @if($offer->benefits)
            <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow overflow-hidden sm:rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                        Benefits Package
                    </h3>
                </div>
                <div class="border-t border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)] px-4 py-5 sm:px-6">
                    <p class="text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] whitespace-pre-wrap">{{ $offer->benefits }}</p>
                </div>
            </div>
        @endif

        <!-- Offer Details -->
        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow overflow-hidden sm:rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                    Offer Details
                </h3>
            </div>
            <div class="border-t border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)]">
                <dl>
                    @if($offer->start_date)
                        <div class="bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Proposed Start Date</dt>
                            <dd class="mt-1 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] sm:mt-0 sm:col-span-2">
                                {{ $offer->start_date->format('F j, Y') }}
                            </dd>
                        </div>
                    @endif
                    @if($offer->decision_deadline)
                        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Decision Deadline</dt>
                            <dd class="mt-1 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] sm:mt-0 sm:col-span-2">
                                {{ $offer->decision_deadline->format('F j, Y') }}
                            </dd>
                        </div>
                    @endif
                    <div class="bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Offer Received</dt>
                        <dd class="mt-1 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] sm:mt-0 sm:col-span-2">
                            {{ $offer->created_at->format('F j, Y g:i A') }}
                        </dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- Notes -->
        @if($offer->notes)
            <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow overflow-hidden sm:rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                        Notes & Negotiation Details
                    </h3>
                </div>
                <div class="border-t border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)] px-4 py-5 sm:px-6">
                    <p class="text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] whitespace-pre-wrap">{{ $offer->notes }}</p>
                </div>
            </div>
        @endif

        <!-- Actions -->
        @if($offer->status->value === 'pending' || $offer->status->value === 'negotiating')
            <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow overflow-hidden sm:rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                        Actions
                    </h3>
                    <p class="mt-1 max-w-2xl text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">
                        Accept or decline this offer
                    </p>
                </div>
                <div class="border-t border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)] px-4 py-5 sm:px-6">
                    <div class="flex flex-wrap gap-3">
                        <form method="POST" action="{{ route('job-applications.offers.accept', [$application, $offer]) }}" onsubmit="return confirm('Are you sure you want to accept this offer? This will update the application status to Accepted.');">
                            @csrf
                            @method('PATCH')
                            <x-button type="submit" variant="primary" class="bg-[color:var(--color-success-600)] hover:bg-[color:var(--color-success-700)] focus:ring-[color:var(--color-success-500)]">
                                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                Accept Offer
                            </x-button>
                        </form>
                        <form method="POST" action="{{ route('job-applications.offers.decline', [$application, $offer]) }}" onsubmit="return confirm('Are you sure you want to decline this offer?');">
                            @csrf
                            @method('PATCH')
                            <x-button type="submit" variant="danger">
                                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                </svg>
                                Decline Offer
                            </x-button>
                        </form>
                    </div>
                </div>
            </div>
        @endif

        <!-- Delete Action -->
        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow overflow-hidden sm:rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-[color:var(--color-danger-600)] dark:text-[color:var(--color-danger-400)]">
                    Danger Zone
                </h3>
            </div>
            <div class="border-t border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)] px-4 py-5 sm:px-6">
                <form method="POST" action="{{ route('job-applications.offers.destroy', [$application, $offer]) }}" onsubmit="return confirm('Are you sure you want to delete this offer? This action cannot be undone.');">
                    @csrf
                    @method('DELETE')
                    <x-button type="submit" variant="danger">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                        </svg>
                        Delete Offer
                    </x-button>
                </form>
            </div>
        </div>
    </div>
@endsection
