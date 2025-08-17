<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInvestmentDividendRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization will be handled in controller
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'investment_id' => ['required', 'exists:investments,id'],
            'amount' => ['required', 'numeric', 'min:0', 'decimal:0,2'],
            'record_date' => ['required', 'date', 'before_or_equal:today'],
            'payment_date' => ['required', 'date', 'after_or_equal:record_date'],
            'ex_dividend_date' => ['nullable', 'date', 'before_or_equal:record_date'],
            'dividend_type' => ['required', 'in:ordinary,qualified,special,return_of_capital'],
            'frequency' => ['required', 'in:monthly,quarterly,semi_annual,annual,special'],
            'dividend_per_share' => ['required', 'numeric', 'min:0', 'decimal:0,8'],
            'shares_held' => ['required', 'numeric', 'min:0', 'decimal:0,8'],
            'tax_withheld' => ['nullable', 'numeric', 'min:0', 'decimal:0,2'],
            'currency' => ['required', 'string', 'size:3'],
            'reinvested' => ['boolean'],
            'notes' => ['nullable', 'string', 'max:65535'],
        ];
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'investment_id.required' => 'An investment must be selected.',
            'investment_id.exists' => 'The selected investment does not exist.',
            'amount.required' => 'Dividend amount is required.',
            'amount.numeric' => 'Dividend amount must be a valid number.',
            'amount.min' => 'Dividend amount must be greater than or equal to 0.',
            'record_date.required' => 'Record date is required.',
            'record_date.date' => 'Record date must be a valid date.',
            'record_date.before_or_equal' => 'Record date cannot be in the future.',
            'payment_date.required' => 'Payment date is required.',
            'payment_date.date' => 'Payment date must be a valid date.',
            'payment_date.after_or_equal' => 'Payment date must be on or after the record date.',
            'ex_dividend_date.date' => 'Ex-dividend date must be a valid date.',
            'ex_dividend_date.before_or_equal' => 'Ex-dividend date must be on or before the record date.',
            'dividend_type.required' => 'Dividend type is required.',
            'dividend_type.in' => 'Invalid dividend type selected.',
            'frequency.required' => 'Dividend frequency is required.',
            'frequency.in' => 'Invalid dividend frequency selected.',
            'dividend_per_share.required' => 'Dividend per share is required.',
            'dividend_per_share.numeric' => 'Dividend per share must be a valid number.',
            'dividend_per_share.min' => 'Dividend per share must be greater than or equal to 0.',
            'shares_held.required' => 'Number of shares held is required.',
            'shares_held.numeric' => 'Number of shares held must be a valid number.',
            'shares_held.min' => 'Number of shares held must be greater than or equal to 0.',
            'tax_withheld.numeric' => 'Tax withheld must be a valid number.',
            'tax_withheld.min' => 'Tax withheld must be greater than or equal to 0.',
            'currency.required' => 'Currency is required.',
            'currency.size' => 'Currency must be a 3-letter code (e.g., USD).',
            'reinvested.boolean' => 'Reinvested field must be true or false.',
            'notes.max' => 'Notes cannot exceed 65,535 characters.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Custom validation: ensure dividend amount matches calculation
            if ($this->filled(['dividend_per_share', 'shares_held'])) {
                $calculatedAmount = round($this->dividend_per_share * $this->shares_held, 2);
                if (abs($calculatedAmount - $this->amount) > 0.01) {
                    $validator->errors()->add('amount', 'Dividend amount should equal dividend per share times shares held.');
                }
            }
        });
    }
}
