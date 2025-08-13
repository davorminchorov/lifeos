<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSubscriptionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && $this->route('subscription')->user_id === auth()->id();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'service_name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'category' => ['sometimes', 'string', 'max:100'],
            'cost' => ['sometimes', 'numeric', 'min:0', 'max:9999.99'],
            'billing_cycle' => ['sometimes', 'string', 'in:monthly,yearly,weekly,custom'],
            'billing_cycle_days' => ['nullable', 'integer', 'min:1', 'max:365', 'required_if:billing_cycle,custom'],
            'currency' => ['sometimes', 'string', 'size:3'],
            'start_date' => ['sometimes', 'date', 'before_or_equal:today'],
            'next_billing_date' => ['sometimes', 'date', 'after:start_date'],
            'cancellation_date' => ['nullable', 'date', 'after:start_date'],
            'payment_method' => ['nullable', 'string', 'max:100'],
            'merchant_info' => ['nullable', 'string', 'max:255'],
            'auto_renewal' => ['boolean'],
            'cancellation_difficulty' => ['nullable', 'integer', 'min:1', 'max:5'],
            'price_history' => ['nullable', 'array'],
            'price_history.*.date' => ['required_with:price_history', 'date'],
            'price_history.*.price' => ['required_with:price_history', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:50'],
            'status' => ['sometimes', 'string', 'in:active,cancelled,paused'],
        ];
    }

    /**
     * Get custom validation error messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'service_name.max' => 'The service name cannot exceed 255 characters.',
            'category.max' => 'The category cannot exceed 100 characters.',
            'cost.numeric' => 'The cost must be a valid number.',
            'cost.min' => 'The cost cannot be negative.',
            'cost.max' => 'The cost cannot exceed $9,999.99.',
            'billing_cycle.in' => 'The billing cycle must be monthly, yearly, weekly, or custom.',
            'billing_cycle_days.required_if' => 'The billing cycle days are required when using a custom billing cycle.',
            'billing_cycle_days.min' => 'The billing cycle must be at least 1 day.',
            'billing_cycle_days.max' => 'The billing cycle cannot exceed 365 days.',
            'currency.size' => 'The currency must be a valid 3-letter code (e.g., USD, EUR).',
            'start_date.before_or_equal' => 'The start date cannot be in the future.',
            'next_billing_date.after' => 'The next billing date must be after the start date.',
            'cancellation_date.after' => 'The cancellation date must be after the start date.',
            'cancellation_difficulty.min' => 'The cancellation difficulty rating must be between 1 and 5.',
            'cancellation_difficulty.max' => 'The cancellation difficulty rating must be between 1 and 5.',
            'price_history.*.date.required_with' => 'Each price history entry must include a date.',
            'price_history.*.price.required_with' => 'Each price history entry must include a price.',
            'price_history.*.price.min' => 'Price history entries cannot have negative prices.',
            'tags.*.max' => 'Each tag cannot exceed 50 characters.',
            'status.in' => 'The status must be active, cancelled, or paused.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'service_name' => 'service name',
            'billing_cycle_days' => 'custom billing cycle days',
            'next_billing_date' => 'next billing date',
            'cancellation_date' => 'cancellation date',
            'payment_method' => 'payment method',
            'merchant_info' => 'merchant information',
            'auto_renewal' => 'auto-renewal setting',
            'cancellation_difficulty' => 'cancellation difficulty',
            'price_history' => 'price history',
        ];
    }
}
