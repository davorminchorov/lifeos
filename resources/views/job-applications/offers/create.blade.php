@extends('layouts.app')

@section('title', 'Record Job Offer - LifeOS')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Record Job Offer</h1>
                <p class="text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] mt-2">
                    {{ $application->job_title }} at {{ $application->company_name }}
                </p>
            </div>
            <a href="{{ route('job-applications.show', $application) }}"
               class="inline-flex items-center px-4 py-2 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded-md shadow-sm text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] hover:bg-[color:var(--color-primary-200)] dark:hover:bg-[color:var(--color-dark-300)]">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back to Application
            </a>
        </div>

        <!-- Form -->
        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow-md rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
            <form method="POST" action="{{ route('job-applications.offers.store', $application) }}" class="space-y-6 p-6">
                @csrf

                <!-- Compensation -->
                <div>
                    <h3 class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-4">Compensation Details</h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="md:col-span-2">
                            <x-form.input
                                name="base_salary"
                                type="number"
                                label="Base Salary"
                                :required="true"
                                step="0.01"
                                min="0"
                                placeholder="80000"
                            />
                        </div>

                        <div>
                            <x-form.select name="currency" label="Currency" :required="true">
                                @foreach($currencies as $curr)
                                    <option value="{{ $curr }}" {{ old('currency', 'USD') === $curr ? 'selected' : '' }}>{{ $curr }}</option>
                                @endforeach
                            </x-form.select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                        <div>
                            <x-form.input
                                name="bonus"
                                type="number"
                                label="Bonus (Signing/Annual)"
                                step="0.01"
                                min="0"
                                placeholder="10000"
                            />
                        </div>

                        <div>
                            <x-form.input
                                name="equity"
                                type="text"
                                label="Equity/Stock Options"
                                placeholder="e.g., 10,000 RSUs over 4 years"
                            />
                        </div>
                    </div>

                    <div class="mt-6">
                        <x-form.input
                            name="benefits"
                            type="textarea"
                            label="Benefits Package"
                            :rows="4"
                            placeholder="Health insurance, 401k match, PTO, remote work policy, etc..."
                        />
                    </div>
                </div>

                <!-- Timeline & Status -->
                <div class="border-t border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)] pt-6">
                    <h3 class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-4">Timeline & Status</h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <x-form.input
                                name="start_date"
                                type="date"
                                label="Proposed Start Date"
                            />
                        </div>

                        <div>
                            <x-form.input
                                name="decision_deadline"
                                type="date"
                                label="Decision Deadline"
                            />
                        </div>

                        <div>
                            <x-form.select name="status" label="Offer Status" :required="true">
                                @foreach($statuses as $statusOption)
                                    <option value="{{ $statusOption->value }}" {{ old('status', 'pending') === $statusOption->value ? 'selected' : '' }}>
                                        {{ ucfirst($statusOption->value) }}
                                    </option>
                                @endforeach
                            </x-form.select>
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div class="border-t border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)] pt-6">
                    <h3 class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-4">Additional Notes</h3>

                    <div>
                        <x-form.input
                            name="notes"
                            type="textarea"
                            label="Notes"
                            :rows="4"
                            placeholder="Negotiation notes, important terms, concerns, questions..."
                        />
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="border-t border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)] pt-6 flex justify-end gap-3">
                    <x-button href="{{ route('job-applications.show', $application) }}" variant="secondary">
                        Cancel
                    </x-button>
                    <x-button type="submit" variant="primary">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Record Offer
                    </x-button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
