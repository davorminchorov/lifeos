@extends('layouts.app')

@section('title', 'Create Budget - LifeOS')

@section('header')
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                Create Budget
            </h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">
                Set spending limits for a specific category
            </p>
        </div>
        <a href="{{ route('budgets.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm font-medium">
            Back to Budgets
        </a>
    </div>
@endsection

@section('content')
    <div class="max-w-3xl mx-auto">
        <form action="{{ route('budgets.store') }}" method="POST" class="space-y-6">
            @csrf

            <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white mb-6">Budget Information</h3>

                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <!-- Category -->
                        <div class="sm:col-span-2">
                            <label for="category" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Category <span class="text-red-500">*</span>
                            </label>
                            <div class="mt-1 flex">
                                <select name="category" id="category"
                                        class="flex-1 rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('category') border-red-300 @enderror"
                                        onchange="toggleCustomCategory(this)">
                                    <option value="">Select a category</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat }}" {{ old('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                                    @endforeach
                                    <option value="custom" {{ old('category') === 'custom' ? 'selected' : '' }}>+ Add Custom Category</option>
                                </select>
                            </div>
                            <input type="text" name="custom_category" id="custom_category"
                                   class="mt-2 hidden flex-1 rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                   placeholder="Enter custom category name" value="{{ old('custom_category') }}">
                            @error('category')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Budget Period -->
                        <div>
                            <label for="budget_period" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Budget Period <span class="text-red-500">*</span>
                            </label>
                            <select name="budget_period" id="budget_period"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('budget_period') border-red-300 @enderror"
                                    onchange="toggleCustomDates(this)">
                                <option value="">Select period</option>
                                <option value="monthly" {{ old('budget_period') === 'monthly' ? 'selected' : '' }}>Monthly</option>
                                <option value="quarterly" {{ old('budget_period') === 'quarterly' ? 'selected' : '' }}>Quarterly</option>
                                <option value="yearly" {{ old('budget_period') === 'yearly' ? 'selected' : '' }}>Yearly</option>
                                <option value="custom" {{ old('budget_period') === 'custom' ? 'selected' : '' }}>Custom Period</option>
                            </select>
                            @error('budget_period')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Amount -->
                        <div>
                            <label for="amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Budget Amount <span class="text-red-500">*</span>
                            </label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <input type="number" name="amount" id="amount" step="0.01" min="0.01" max="999999.99"
                                       class="block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 pr-12 @error('amount') border-red-300 @enderror"
                                       placeholder="0.00" value="{{ old('amount') }}">
                                <div class="absolute inset-y-0 right-0 flex items-center">
                                    <select name="currency" id="currency"
                                            class="h-full rounded-md border-0 bg-transparent py-0 pl-2 pr-7 text-gray-500 dark:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm">
                                        @foreach($currencies as $code => $name)
                                            <option value="{{ explode(' ', $code)[0] }}" {{ (old('currency', 'MKD') === explode(' ', $code)[0]) ? 'selected' : '' }}>
                                                {{ explode(' ', $code)[0] }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            @error('amount')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                            @error('currency')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Custom Date Range (hidden by default) -->
                        <div id="custom_dates" class="sm:col-span-2 grid grid-cols-2 gap-4 {{ old('budget_period') === 'custom' ? '' : 'hidden' }}">
                            <div>
                                <label for="start_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Start Date <span class="text-red-500">*</span>
                                </label>
                                <input type="date" name="start_date" id="start_date"
                                       class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('start_date') border-red-300 @enderror"
                                       value="{{ old('start_date') }}">
                                @error('start_date')
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="end_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    End Date <span class="text-red-500">*</span>
                                </label>
                                <input type="date" name="end_date" id="end_date"
                                       class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('end_date') border-red-300 @enderror"
                                       value="{{ old('end_date') }}">
                                @error('end_date')
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Alert Threshold -->
                        <div>
                            <label for="alert_threshold" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Alert Threshold (%)
                            </label>
                            <input type="number" name="alert_threshold" id="alert_threshold" min="1" max="100"
                                   class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('alert_threshold') border-red-300 @enderror"
                                   placeholder="80" value="{{ old('alert_threshold', 80) }}">
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Get notified when spending reaches this percentage</p>
                            @error('alert_threshold')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Active Status -->
                        <div>
                            <div class="flex items-start">
                                <div class="flex items-center h-5">
                                    <input id="is_active" name="is_active" type="checkbox" value="1"
                                           class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 dark:border-gray-700 rounded"
                                           {{ old('is_active', true) ? 'checked' : '' }}>
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="is_active" class="font-medium text-gray-700 dark:text-gray-300">Active</label>
                                    <p class="text-gray-500 dark:text-gray-400">Budget is currently active and being tracked</p>
                                </div>
                            </div>
                        </div>

                        <!-- Rollover Unused -->
                        <div>
                            <div class="flex items-start">
                                <div class="flex items-center h-5">
                                    <input id="rollover_unused" name="rollover_unused" type="checkbox" value="1"
                                           class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 dark:border-gray-700 rounded"
                                           {{ old('rollover_unused') ? 'checked' : '' }}>
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="rollover_unused" class="font-medium text-gray-700 dark:text-gray-300">Rollover Unused Amount</label>
                                    <p class="text-gray-500 dark:text-gray-400">Add unspent budget to next period</p>
                                </div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="sm:col-span-2">
                            <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Notes
                            </label>
                            <textarea name="notes" id="notes" rows="3"
                                      class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('notes') border-red-300 @enderror"
                                      placeholder="Optional notes about this budget...">{{ old('notes') }}</textarea>
                            @error('notes')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex justify-end gap-3">
                <a href="{{ route('budgets.index') }}"
                   class="bg-white dark:bg-gray-800 py-2 px-4 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Cancel
                </a>
                <button type="submit"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white py-2 px-4 rounded-md text-sm font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Create Budget
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

                // Set default dates if not already set
                if (!startDate.value) {
                    startDate.value = new Date().toISOString().split('T')[0];
                }
                if (!endDate.value) {
                    const nextMonth = new Date();
                    nextMonth.setMonth(nextMonth.getMonth() + 1);
                    endDate.value = nextMonth.toISOString().split('T')[0];
                }
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
