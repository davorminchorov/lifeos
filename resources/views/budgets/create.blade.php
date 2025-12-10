@extends('layouts.app')

@section('title', 'Create Budget - LifeOS')

@section('header')
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                Create Budget
            </h1>
            <p class="mt-2 text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">
                Set spending limits for a specific category
            </p>
        </div>
        <x-button href="{{ route('budgets.index') }}" variant="secondary">
            Back to Budgets
        </x-button>
    </div>
@endsection

@section('content')
    <div class="max-w-3xl mx-auto">
        <form action="{{ route('budgets.store') }}" method="POST" class="space-y-6">
            @csrf

            <div class="bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] shadow-sm rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-6">Budget Information</h3>

                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <!-- Category -->
                        <div class="sm:col-span-2">
                            <x-form.select
                                name="category"
                                label="Category"
                                :required="true"
                                placeholder="Select a category"
                                onchange="toggleCustomCategory(this)"
                                selectClass="@error('category') border-danger-400 @enderror"
                            >
                                @foreach($categories as $cat)
                                    <option value="{{ $cat }}" {{ old('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                                @endforeach
                                <option value="custom" {{ old('category') === 'custom' ? 'selected' : '' }}>+ Add Custom Category</option>
                            </x-form.select>

                            <input type="text" name="custom_category" id="custom_category"
                                   class="mt-2 hidden flex-1 rounded-md border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] dark:bg-[color:var(--color-dark-100)] dark:text-[color:var(--color-dark-600)] shadow-sm focus:border-[color:var(--color-accent-500)] focus:ring-[color:var(--color-accent-500)]"
                                   placeholder="Enter custom category name" value="{{ old('custom_category') }}">
                            @error('category')
                                <p class="mt-2 text-sm text-[color:var(--color-danger-500)]">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Budget Period -->
                        <div>
                            <x-form.select
                                name="budget_period"
                                label="Budget Period"
                                :required="true"
                                placeholder="Select period"
                                onchange="toggleCustomDates(this)"
                                selectClass="@error('budget_period') border-danger-400 @enderror"
                            >
                                <option value="monthly" {{ old('budget_period') === 'monthly' ? 'selected' : '' }}>Monthly</option>
                                <option value="quarterly" {{ old('budget_period') === 'quarterly' ? 'selected' : '' }}>Quarterly</option>
                                <option value="yearly" {{ old('budget_period') === 'yearly' ? 'selected' : '' }}>Yearly</option>
                                <option value="custom" {{ old('budget_period') === 'custom' ? 'selected' : '' }}>Custom Period</option>
                            </x-form.select>
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
                                       class="block w-full rounded-md border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] dark:bg-[color:var(--color-dark-100)] dark:text-[color:var(--color-dark-600)] shadow-sm focus:border-[color:var(--color-accent-500)] focus:ring-[color:var(--color-accent-500)] pr-12 @error('amount') border-danger-400 @enderror"
                                       placeholder="0.00" value="{{ old('amount') }}">
                                <div class="absolute inset-y-0 right-0 flex items-center">
                                    <select name="currency" id="currency"
                                            class="h-full rounded-md border-0 bg-transparent py-0 pl-2 pr-7 text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] focus:ring-2 focus:ring-inset focus:ring-[color:var(--color-accent-600)] sm:text-sm">
                                        @foreach($currencies as $code => $name)
                                            <option value="{{ explode(' ', $code)[0] }}" {{ (old('currency', 'MKD') === explode(' ', $code)[0]) ? 'selected' : '' }}>
                                                {{ explode(' ', $code)[0] }}
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

                        <!-- Custom Date Range (hidden by default) -->
                        <div id="custom_dates" class="sm:col-span-2 grid grid-cols-2 gap-4 {{ old('budget_period') === 'custom' ? '' : 'hidden' }}">
                            <div>
                                <x-form.input
                                    type="date"
                                    name="start_date"
                                    label="Start Date"
                                    inputClass="@error('start_date') border-danger-400 @enderror"
                                />
                                @error('start_date')
                                    <p class="mt-2 text-sm text-[color:var(--color-danger-500)]">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <x-form.input
                                    type="date"
                                    name="end_date"
                                    label="End Date"
                                    inputClass="@error('end_date') border-danger-400 @enderror"
                                />
                                @error('end_date')
                                    <p class="mt-2 text-sm text-[color:var(--color-danger-500)]">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Alert Threshold -->
                        <div>
                            <x-form.input
                                type="number"
                                name="alert_threshold"
                                label="Alert Threshold (%)"
                                min="1"
                                max="100"
                                placeholder="80"
                                :value="old('alert_threshold', 80)"
                                helpText="Get notified when spending reaches this percentage"
                                inputClass="@error('alert_threshold') border-danger-400 @enderror"
                            />
                            @error('alert_threshold')
                                <p class="mt-2 text-sm text-[color:var(--color-danger-500)]">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Active Status -->
                        <div>
                            <x-form.checkbox
                                name="is_active"
                                label="Active"
                                :checked="old('is_active', true)"
                                helpText="Budget is currently active and being tracked"
                            />
                        </div>

                        <!-- Rollover Unused -->
                        <div>
                            <x-form.checkbox
                                name="rollover_unused"
                                label="Rollover Unused Amount"
                                :checked="old('rollover_unused')"
                                helpText="Add unspent budget to next period"
                            />
                        </div>

                        <!-- Notes -->
                        <div class="sm:col-span-2">
                            <x-form.input
                                type="textarea"
                                name="notes"
                                label="Notes"
                                rows="3"
                                placeholder="Optional notes about this budget..."
                                inputClass="@error('notes') border-danger-400 @enderror"
                            />
                            @error('notes')
                                <p class="mt-2 text-sm text-[color:var(--color-danger-500)]">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex justify-end gap-3">
                <x-button href="{{ route('budgets.index') }}" variant="secondary">
                    Cancel
                </x-button>
                <x-button type="submit" variant="primary">
                    Create Budget
                </x-button>
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
