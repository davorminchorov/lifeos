<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectInvestmentTransactionRequest extends FormRequest
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
            'amount' => 'required|numeric|min:0.01|max:999999999',
            'currency' => 'required|string|in:MKD,USD,EUR,GBP,CAD,AUD,JPY,CHF,RSD,BGN',
            'transaction_date' => 'required|date|before_or_equal:today',
            'notes' => 'nullable|string|max:5000',
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'amount.required' => 'Investment amount is required.',
            'amount.min' => 'Investment amount must be at least 0.01.',
            'amount.max' => 'Investment amount cannot exceed 999,999,999.',
            'currency.required' => 'Currency is required.',
            'currency.in' => 'Please select a valid currency.',
            'transaction_date.required' => 'Transaction date is required.',
            'transaction_date.before_or_equal' => 'Transaction date cannot be in the future.',
            'notes.max' => 'Notes cannot exceed 5000 characters.',
        ];
    }
}
