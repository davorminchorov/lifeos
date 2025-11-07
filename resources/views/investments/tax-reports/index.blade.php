@extends('layouts.app')

@section('title', 'Tax Reports - LifeOS')

@section('header')
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                Investment Tax Reports
            </h1>
            <p class="mt-2 text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">
                Review your investment tax information for {{ $taxSummary['tax_year'] }}
            </p>
        </div>
        <div class="flex-shrink-0">
            <form method="GET" action="{{ route('investments.tax-reports.index') }}" class="flex items-center gap-2">
                <label for="tax_year" class="text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Tax Year:</label>
                <select name="tax_year" id="tax_year" onchange="this.form.submit()" class="rounded-md border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-300)] text-[color:var(--color-primary-700)] dark:text-white shadow-sm focus:border-[color:var(--color-accent-500)] focus:ring-[color:var(--color-accent-500)]">
                    @for($year = date('Y'); $year >= date('Y') - 5; $year--)
                        <option value="{{ $year }}" {{ $taxSummary['tax_year'] == $year ? 'selected' : '' }}>
                            {{ $year }}
                        </option>
                    @endfor
                </select>
            </form>
        </div>
    </div>
@endsection

@section('content')

                <!-- Tax Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div class="bg-[color:var(--color-success-50)] dark:bg-[color:var(--color-success-900)]/20 rounded-lg p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8 text-[color:var(--color-success-600)] dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] truncate">Realized Gains</dt>
                                    <dd class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] dark:text-[color:var(--color-primary-100)] dark:text-[color:var(--color-dark-200)]">${{ number_format($taxSummary['total_realized_gains'], 2) }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>

                    <div class="bg-red-50 dark:bg-red-900/20 rounded-lg p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8 text-[color:var(--color-danger-600)] dark:text-[color:var(--color-danger-400)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] truncate">Realized Losses</dt>
                                    <dd class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] dark:text-[color:var(--color-primary-100)] dark:text-[color:var(--color-dark-200)]">${{ number_format($taxSummary['total_realized_losses'], 2) }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>

                    <div class="bg-[color:var(--color-info-50)] dark:bg-[color:var(--color-info-900)]/20 rounded-lg p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8 text-[color:var(--color-info-600)] dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] truncate">Dividend Income</dt>
                                    <dd class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] dark:text-[color:var(--color-primary-100)] dark:text-[color:var(--color-dark-200)]">${{ number_format($taxSummary['total_dividend_income'], 2) }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>

                    <div class="bg-purple-50 dark:bg-purple-900/20 rounded-lg p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] truncate">Net Gains/Losses</dt>
                                    <dd class="text-lg font-medium {{ ($taxSummary['total_realized_gains'] - $taxSummary['total_realized_losses']) >= 0 ? 'text-[color:var(--color-success-600)] dark:text-green-400' : 'text-[color:var(--color-danger-600)] dark:text-[color:var(--color-danger-400)]' }}">
                                        ${{ number_format($taxSummary['total_realized_gains'] - $taxSummary['total_realized_losses'], 2) }}
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Report Actions -->
                <div class="bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] rounded-lg p-6 mb-8">
                    <h3 class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] dark:text-[color:var(--color-primary-100)] dark:text-[color:var(--color-dark-200)] mb-4">Generate Tax Reports</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <a href="{{ route('investments.tax-reports.capital-gains', ['tax_year' => $taxSummary['tax_year']]) }}" class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-[color:var(--color-accent-500)] hover:bg-[color:var(--color-accent-600)] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[color:var(--color-accent-500)]">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Capital Gains Report
                        </a>
                        <a href="{{ route('investments.tax-reports.dividend-income', ['tax_year' => $taxSummary['tax_year']]) }}" class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-[color:var(--color-success-600)] hover:bg-[color:var(--color-success-600)] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                            </svg>
                            Dividend Income Report
                        </a>
                    </div>
                </div>

                <!-- Important Tax Information -->
                <div class="bg-[color:var(--color-warning-50)] dark:bg-[color:var(--color-warning-900)]/20 border border-yellow-200 dark:border-yellow-700 rounded-lg p-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-[color:var(--color-warning-400)]" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-[color:var(--color-warning-600)] dark:text-[color:var(--color-warning-200)]">
                                Important Tax Information
                            </h3>
                            <div class="mt-2 text-sm text-[color:var(--color-warning-700)] dark:text-[color:var(--color-warning-300)]">
                                <ul class="list-disc list-inside space-y-1">
                                    <li>These reports are for informational purposes only and should not be considered professional tax advice.</li>
                                    <li>Consult with a qualified tax professional for specific tax planning and filing guidance.</li>
                                    <li>Capital gains holding periods and tax rates may vary based on your specific situation.</li>
                                    <li>Some dividend classifications may require additional verification with your broker statements.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
@endsection
