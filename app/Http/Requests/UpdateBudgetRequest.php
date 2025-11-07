<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBudgetRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && $this->budget->user_id === auth()->id();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'category' => [
                'required',
                'string',
                'max:255',
                // Ensure unique category per user per active period, excluding current budget
                Rule::unique('budgets')->where(function ($query) {
                    return $query->where('user_id', auth()->id())
                        ->where('is_active', true)
                        ->where('id', '!=', $this->budget->id)
                        ->where('start_date', '<=', $this->end_date ?? now())
                        ->where('end_date', '>=', $this->start_date ?? now());
                }),
            ],
            'budget_period' => [
                'required',
                'string',
                Rule::in(['monthly', 'quarterly', 'yearly', 'custom']),
            ],
            'amount' => [
                'required',
                'numeric',
                'min:0.01',
                'max:999999.99',
            ],
            'currency' => [
                'required',
                'string',
                'size:3',
                'alpha',
                'uppercase',
            ],
            'start_date' => [
                'required_if:budget_period,custom',
                'date',
                // Allow past dates for existing budgets but not too far in the past
                'after_or_equal:'.now()->subYear()->toDateString(),
            ],
            'end_date' => [
                'required_if:budget_period,custom',
                'date',
                'after:start_date',
            ],
            'is_active' => [
                'boolean',
            ],
            'rollover_unused' => [
                'boolean',
            ],
            'alert_threshold' => [
                'integer',
                'min:1',
                'max:100',
            ],
            'notes' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'category.required' => 'Please select or enter a budget category.',
            'category.unique' => 'You already have another active budget for this category in the selected period.',
            'budget_period.required' => 'Please select a budget period.',
            'budget_period.in' => 'Please select a valid budget period.',
            'amount.required' => 'Please enter the budget amount.',
            'amount.numeric' => 'Budget amount must be a valid number.',
            'amount.min' => 'Budget amount must be at least :min.',
            'amount.max' => 'Budget amount cannot exceed :max.',
            'currency.required' => 'Please select a currency.',
            'currency.size' => 'Currency code must be exactly 3 characters.',
            'currency.alpha' => 'Currency code must contain only letters.',
            'currency.uppercase' => 'Currency code must be uppercase.',
            'start_date.required_if' => 'Start date is required for custom budget periods.',
            'start_date.after_or_equal' => 'Start date cannot be more than 1 year in the past.',
            'end_date.required_if' => 'End date is required for custom budget periods.',
            'end_date.after' => 'End date must be after the start date.',
            'alert_threshold.integer' => 'Alert threshold must be a whole number.',
            'alert_threshold.min' => 'Alert threshold must be at least :min%.',
            'alert_threshold.max' => 'Alert threshold cannot exceed :max%.',
            'notes.max' => 'Notes cannot exceed :max characters.',
        ];
    }

    /**
     * Get custom attribute names for validation errors.
     */
    public function attributes(): array
    {
        return [
            'budget_period' => 'budget period',
            'alert_threshold' => 'alert threshold',
            'start_date' => 'start date',
            'end_date' => 'end date',
            'rollover_unused' => 'rollover unused amount',
            'is_active' => 'active status',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Additional validation for custom periods
            if ($this->budget_period === 'custom') {
                $startDate = $this->start_date;
                $endDate = $this->end_date;

                if ($startDate && $endDate) {
                    $diffInDays = \Carbon\Carbon::parse($endDate)->diffInDays(\Carbon\Carbon::parse($startDate));

                    // Ensure period is reasonable (not too short or too long)
                    if ($diffInDays < 1) {
                        $validator->errors()->add('end_date', 'Budget period must be at least 1 day.');
                    } elseif ($diffInDays > 365) {
                        $validator->errors()->add('end_date', 'Budget period cannot exceed 365 days.');
                    }
                }
            }

            // Validate currency exists in supported currencies
            if ($this->currency) {
                $currencyService = app(\App\Services\CurrencyService::class);
                $supportedCurrencies = array_keys($currencyService->getSupportedCurrencies());

                if (! in_array($this->currency, $supportedCurrencies)) {
                    $validator->errors()->add('currency', 'The selected currency is not supported.');
                }
            }

            // Warn if budget period is being changed and it affects existing expenses
            if ($this->budget && $this->budget_period !== $this->budget->budget_period) {
                $currentSpending = $this->budget->getCurrentSpending();
                if ($currentSpending > 0) {
                    // Add informational message rather than error
                    $validator->after(function ($validator) {
                        session()->flash('info', 'Changing the budget period may affect how existing expenses are calculated against this budget.');
                    });
                }
            }
        });
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        // Set default values if not provided
        $this->merge([
            'is_active' => $this->boolean('is_active'),
            'rollover_unused' => $this->boolean('rollover_unused'),
            'alert_threshold' => $this->alert_threshold ?? 80,
        ]);
    }
}
