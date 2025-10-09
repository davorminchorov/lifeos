<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInvestmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'investment_type' => 'sometimes|required|string|in:stocks,bonds,etf,mutual_fund,crypto,real_estate,commodities,cash,project',
            'symbol_identifier' => 'nullable|string|max:20',
            'name' => 'sometimes|required|string|max:255',
            'quantity' => 'sometimes|required|numeric|min:0',
            'purchase_date' => 'sometimes|required|date|before_or_equal:today',
            'purchase_price' => 'sometimes|required|numeric|min:1|max:999999999',
            'current_value' => 'nullable|numeric|min:1|max:999999999',
            'total_dividends_received' => 'nullable|numeric|min:0|max:999999999',
            'total_fees_paid' => 'nullable|numeric|min:0|max:999999999',
            'investment_goals' => 'nullable|array',
            'risk_tolerance' => 'sometimes|required|string|in:conservative,moderate,aggressive',
            'account_broker' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:50',
            'transaction_history' => 'nullable|array',
            'tax_lots' => 'nullable|array',
            'target_allocation_percentage' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|string|max:1000',
            'status' => 'nullable|string|in:active,sold,pending',

            // Project-specific fields for updates
            'project_type' => 'nullable|string|max:100',
            'project_website' => 'nullable|url|max:255',
            'project_repository' => 'nullable|url|max:255',
            'project_stage' => 'nullable|string|max:50',
            'project_business_model' => 'nullable|string|max:100',
            'equity_percentage' => 'nullable|numeric|min:0|max:100',
            'project_start_date' => 'nullable|date',
            'project_end_date' => 'nullable|date|after_or_equal:project_start_date',
            'project_notes' => 'nullable|string',
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'investment_type.required' => 'Please select an investment type.',
            'investment_type.in' => 'Please select a valid investment type.',
            'name.required' => 'Investment name is required.',
            'quantity.required' => 'Quantity is required.',
            'quantity.min' => 'Quantity must be greater than or equal to 0.',
            'purchase_date.required' => 'Purchase date is required.',
            'purchase_date.before_or_equal' => 'Purchase date cannot be in the future.',
            'purchase_price.required' => 'Purchase price is required.',
            'purchase_price.min' => 'Purchase price must be greater than or equal to 0.',
            'risk_tolerance.required' => 'Please select a risk tolerance level.',
            'risk_tolerance.in' => 'Please select a valid risk tolerance level.',
        ];
    }
}
