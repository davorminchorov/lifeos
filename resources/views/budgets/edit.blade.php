@extends('layouts.app')

@section('title', 'Edit Budget - LifeOS')

@section('header')
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                Edit Budget
            </h1>
            <p class="mt-2 text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">
                Update spending limits for {{ $budget->category }}
            </p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('budgets.show', $budget) }}" class="bg-[color:var(--color-accent-500)] hover:bg-[color:var(--color-accent-600)] text-white px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200">
                View Details
            </a>
            <a href="{{ route('budgets.index') }}" class="px-4 py-2 rounded-md text-sm font-medium border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] bg-[color:var(--color-primary-200)] hover:bg-[color:var(--color-primary-300)] text-[color:var(--color-primary-700)] dark:bg-[color:var(--color-dark-200)] dark:hover:bg-[color:var(--color-dark-300)] dark:text-[color:var(--color-dark-600)] transition-colors duration-200">
                Back to Budgets
            </a>
        </div>
    </div>
@endsection

@section('content')
    <div class="max-w-3xl mx-auto">
        <form action="{{ route('budgets.update', $budget) }}" method="POST" class="space-y-6">
            @csrf
            @method('PATCH')

            <div class="bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] shadow-sm rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-6">Budget Information</h3>

                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <!-- Category -->
                        <div class="sm:col-span-2">
                            <label for="category" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                Category <span class="text-[color:var(--color-danger-500)]">*</span>
                            </label>
                            <div class="mt-1 flex">
                                <select name="category" id="category"
                                        class="flex-1 rounded-md border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] dark:bg-[color:var(--color-dark-100)] dark:text-[color:var(--color-dark-600)] shadow-sm focus:border-[color:var(--color-accent-500)] focus:ring-[color:var(--color-accent-500)] @error('category') border-red-300 @enderror"
                                        onchange="toggleCustomCategory(this)">
                                    <option value="">Select a category</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat }}" {{ (old('category', $budget->category) === $cat) ? 'selected' : '' }}>{{ $cat }}</option>
                                    @endforeach
                                    @if(!$categories->contains($budget->category))
                                        <option value="{{ $budget->category }}" selected>{{ $budget->category }}</option>
                                    @endif
                                    <option value="custom" {{ old('category') === 'custom' ? 'selected' : '' }}>+ Add Custom Category</option>
                                </select>
                            </div>
                            <input type="text" name="custom_category" id="custom_category"
                                   class="mt-2 hidden flex-1 rounded-md border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] dark:bg-[color:var(--color-dark-100)] dark:text-[color:var(--color-dark-600)] shadow-sm focus:border-[color:var(--color-accent-500)] focus:ring-[color:var(--color-accent-500)]"
                                   placeholder="Enter custom category name" value="{{ old('custom_category') }}">
                            @error('category')
                                <p class="mt-2 text-sm text-[color:var(--color-danger-500)]">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Budget Period -->
                        <div>
                            <label for="budget_period" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                Budget Period <span class="text-[color:var(--color-danger-500)]">*</span>
                            </label>
                            <select name="budget_period" id="budget_period"
                                    class="mt-1 block w-full rounded-md border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] dark:bg-[color:var(--color-dark-100)] dark:text-[color:var(--color-dark-600)] shadow-sm focus:border-[color:var(--color-accent-500)] focus:ring-[color:var(--color-accent-500)] @error('budget_period') border-red-300 @enderror"
                                    onchange="toggleCustomDates(this)">
                                <option value="">Select period</option>
                                <option value="monthly" {{ old('budget_period', $budget->budget_period) === 'monthly' ? 'selected' : '' }}>Monthly</option>
                                <option value="quarterly" {{ old('budget_period', $budget->budget_period) === 'quarterly' ? 'selected' : '' }}>Quarterly</option>
                                <option value="yearly" {{ old('budget_period', $budget->budget_period) === 'yearly' ? 'selected' : '' }}>Yearly</option>
                                <option value="custom" {{ old('budget_period', $budget->budget_period) === 'custom' ? 'selected' : '' }}>Custom Period</option>
                            </select>
                            @error('budget_period')
                                <p class="mt-2 text-sm text-[color:var(--color-danger-500)]">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Amount -->
                        <div>
                            <label for="amount" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                Budget Amount <span class="text-[color:var(--color-danger-500)]">*</span>
                            </label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <input type="number" name="amount" id="amount" step="0.01" min="0.01" max="999999.99"
                                       class="block w-full rounded-md border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] dark:bg-[color:var(--color-dark-100)] dark:text-[color:var(--color-dark-600)] shadow-sm focus:border-[color:var(--color-accent-500)] focus:ring-[color:var(--color-accent-500)] pr-12 @error('amount') border-red-300 @enderror"
                                       placeholder="0.00" value="{{ old('amount', $budget->amount) }}">
                                <div class="absolute inset-y-0 right-0 flex items-center">
                                    <select name="currency" id="currency"
                                            class="h-full rounded-md border-0 bg-transparent py-0 pl-2 pr-7 text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] focus:ring-2 focus:ring-inset focus:ring-[color:var(--color-accent-600)] sm:text-sm">
                                        @foreach($currencies as $code => $name)
                                            @php $currencyCode = explode(' ', $code)[0]; @endphp
                                            <option value="{{ $currencyCode }}" {{ (old('currency', $budget->currency) === $currencyCode) ? 'selected' : '' }}>
                                                {{ $currencyCode }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            @error('amount')
                                <p class="mt-2 text-sm text-[color:var(--color-danger-500)]">{{ $message }}</p>
                            @enderror
                            @error('currency')
                                <p class="mt-2 text-sm text-[color:var(--color-danger-500)]">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Custom Date Range -->
                        <div id="custom_dates" class="sm:col-span-2 grid grid-cols-2 gap-4 {{ old('budget_period', $budget->budget_period) === 'custom' ? '' : 'hidden' }}">
                            <div>
                                <label for="start_date" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                    Start Date <span class="text-[color:var(--color-danger-500)]">*</span>
                                </label>
                                <input type="date" name="start_date" id="start_date"
                                       class="mt-1 block w-full rounded-md border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] dark:bg-[color:var(--color-dark-100)] dark:text-[color:var(--color-dark-600)] shadow-sm focus:border-[color:var(--color-accent-500)] focus:ring-[color:var(--color-accent-500)] @error('start_date') border-red-300 @enderror"
                                       value="{{ old('start_date', $budget->start_date->format('Y-m-d')) }}">
                                @error('start_date')
                                    <p class="mt-2 text-sm text-[color:var(--color-danger-500)]">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="end_date" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                    End Date <span class="text-[color:var(--color-danger-500)]">*</span>
                                </label>
                                <input type="date" name="end_date" id="end_date"
                                       class="mt-1 block w-full rounded-md border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] dark:bg-[color:var(--color-dark-100)] dark:text-[color:var(--color-dark-600)] shadow-sm focus:border-[color:var(--color-accent-500)] focus:ring-[color:var(--color-accent-500)] @error('end_date') border-red-300 @enderror"
                                       value="{{ old('end_date', $budget->end_date->format('Y-m-d')) }}">
                                @error('end_date')
                                    <p class="mt-2 text-sm text-[color:var(--color-danger-500)]">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Alert Threshold -->
                        <div>
                            <label for="alert_threshold" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                Alert Threshold (%)
                            </label>
                            <input type="number" name="alert_threshold" id="alert_threshold" min="1" max="100"
                                   class="mt-1 block w-full rounded-md border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] dark:bg-[color:var(--color-dark-100)] dark:text-[color:var(--color-dark-600)] shadow-sm focus:border-[color:var(--color-accent-500)] focus:ring-[color:var(--color-accent-500)] @error('alert_threshold') border-red-300 @enderror"
                                   placeholder="80" value="{{ old('alert_threshold', $budget->alert_threshold) }}">
                            <p class="mt-1 text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Get notified when spending reaches this percentage</p>
                            @error('alert_threshold')
                                <p class="mt-2 text-sm text-[color:var(--color-danger-500)]">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Active Status -->
                        <div>
                            <div class="flex items-start">
                                <div class="flex items-center h-5">
                                    <input id="is_active" name="is_active" type="checkbox" value="1"
                                           class="focus:ring-[color:var(--color-accent-500)] h-4 w-4 text-[color:var(--color-accent-600)] border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded"
                                           {{ old('is_active', $budget->is_active) ? 'checked' : '' }}>
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="is_active" class="font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Active</label>
                                    <p class="text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Budget is currently active and being tracked</p>
                                </div>
                            </div>
                        </div>

                        <!-- Rollover Unused -->
                        <div>
                            <div class="flex items-start">
                                <div class="flex items-center h-5">
                                    <input id="rollover_unused" name="rollover_unused" type="checkbox" value="1"
                                           class="focus:ring-[color:var(--color-accent-500)] h-4 w-4 text-[color:var(--color-accent-600)] border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded"
                                           {{ old('rollover_unused', $budget->rollover_unused) ? 'checked' : '' }}>
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="rollover_unused" class="font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Rollover Unused Amount</label>
                                    <p class="text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Add unspent budget to next period</p>
                                </div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="sm:col-span-2">
                            <label for="notes" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                Notes
                            </label>
                            <textarea name="notes" id="notes" rows="3"
                                      class="mt-1 block w-full rounded-md border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] dark:bg-[color:var(--color-dark-100)] dark:text-[color:var(--color-dark-600)] shadow-sm focus:border-[color:var(--color-accent-500)] focus:ring-[color:var(--color-accent-500)] @error('notes') border-red-300 @enderror"
                                      placeholder="Optional notes about this budget...">{{ old('notes', $budget->notes) }}</textarea>
                            @error('notes')
                                <p class="mt-2 text-sm text-[color:var(--color-danger-500)]">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Budget Status Summary -->
            <div class="bg-[color:var(--color-info-50)] dark:bg-[color:var(--color-dark-100)] border border-[color:var(--color-info-500)] dark:border-[color:var(--color-dark-300)] rounded-lg p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-[color:var(--color-info-500)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-3 flex-1 md:flex md:justify-between">
                        <p class="text-sm text-[color:var(--color-info-600)] dark:text-[color:var(--color-info-500)]">
                            <strong>Current Status:</strong>
                            {{ number_format($budget->getCurrentSpending(), 2) }} {{ $budget->currency }} spent of {{ number_format($budget->amount, 2) }} {{ $budget->currency }}
                            ({{ $budget->getUtilizationPercentage() }}% used)
                        </p>
                        <p class="mt-3 text-sm md:mt-0 md:ml-6">
                            <span class="font-medium text-[color:var(--color-info-600)] dark:text-[color:var(--color-info-500)]">
                                {{ number_format($budget->getRemainingAmount(), 2) }} {{ $budget->currency }} remaining
                            </span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex justify-end gap-3">
                <a href="{{ route('budgets.show', $budget) }}"
                   class="py-2 px-4 rounded-md text-sm font-medium border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] bg-[color:var(--color-primary-200)] hover:bg-[color:var(--color-primary-300)] text-[color:var(--color-primary-700)] dark:bg-[color:var(--color-dark-200)] dark:hover:bg-[color:var(--color-dark-300)] dark:text-[color:var(--color-dark-600)] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[color:var(--color-accent-500)] transition-colors duration-200">
                    Cancel
                </a>
                <button type="submit"
                        class="bg-[color:var(--color-accent-500)] hover:bg-[color:var(--color-accent-600)] text-white py-2 px-4 rounded-md text-sm font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[color:var(--color-accent-500)] transition-colors duration-200">
                    Update Budget
                </button>
            </div>
        </form>
    </div>

    <!-- JavaScript for form interactions -->
    <script>
        function toggleCustomCategory(select) {
            const customInput = document.getElementById('custom_category');
            if (select.value === 'custom') {
                customInput.classList.remove('hidden');
                customInput.required = true;
                customInput.focus();
            } else {
                customInput.classList.add('hidden');
                customInput.required = false;
                customInput.value = '';
            }
        }

        function toggleCustomDates(select) {
            const customDates = document.getElementById('custom_dates');
            const startDate = document.getElementById('start_date');
            const endDate = document.getElementById('end_date');

            if (select.value === 'custom') {
                customDates.classList.remove('hidden');
                startDate.required = true;
                endDate.required = true;
            } else {
                customDates.classList.add('hidden');
                startDate.required = false;
                endDate.required = false;
            }
        }

        // Initialize custom category if previously selected
        document.addEventListener('DOMContentLoaded', function() {
            const categorySelect = document.getElementById('category');
            if (categorySelect.value === 'custom') {
                toggleCustomCategory(categorySelect);
            }

            const periodSelect = document.getElementById('budget_period');
            if (periodSelect.value === 'custom') {
                toggleCustomDates(periodSelect);
            }
        });

        // Update end date when start date changes (for custom periods)
        document.getElementById('start_date').addEventListener('change', function() {
            const endDate = document.getElementById('end_date');
            if (this.value && (!endDate.value || endDate.value <= this.value)) {
                const nextDay = new Date(this.value);
                nextDay.setDate(nextDay.getDate() + 30); // Default to 30 days later
                endDate.value = nextDay.toISOString().split('T')[0];
            }
        });
    </script>
@endsection
