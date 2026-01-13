<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInvoiceRequest extends FormRequest
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
            'customer_id' => ['required', 'exists:customers,id'],
            'currency' => ['required', 'string', 'size:3', 'in:MKD,USD,EUR,GBP,CAD,AUD,JPY,CHF,RSD,BGN'],
            'tax_behavior' => ['required', 'in:inclusive,exclusive'],
            'net_terms_days' => ['nullable', 'integer', 'min:0', 'max:365'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'internal_notes' => ['nullable', 'string', 'max:5000'],
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
            'customer_id.required' => 'Please select a customer.',
            'customer_id.exists' => 'The selected customer does not exist.',
            'currency.required' => 'Currency is required.',
            'currency.size' => 'Currency must be a 3-letter ISO code.',
            'currency.in' => 'The selected currency is not supported.',
            'tax_behavior.required' => 'Please specify tax behavior.',
            'tax_behavior.in' => 'Tax behavior must be either inclusive or exclusive.',
            'net_terms_days.min' => 'Net terms cannot be negative.',
            'net_terms_days.max' => 'Net terms cannot exceed 365 days.',
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
            'customer_id' => 'customer',
            'tax_behavior' => 'tax behavior',
            'net_terms_days' => 'payment terms',
            'internal_notes' => 'internal notes',
        ];
    }
}
