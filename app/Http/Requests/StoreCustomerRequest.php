<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'billing_address' => ['nullable', 'array'],
            'billing_address.street' => ['nullable', 'string', 'max:255'],
            'billing_address.city' => ['nullable', 'string', 'max:100'],
            'billing_address.state' => ['nullable', 'string', 'max:100'],
            'billing_address.postal_code' => ['nullable', 'string', 'max:20'],
            'billing_address.country' => ['nullable', 'string', 'max:100'],
            'tax_id' => ['nullable', 'string', 'max:50'],
            'tax_country' => ['nullable', 'string', 'size:2'],
            'currency' => ['required', 'string', 'size:3', 'in:MKD,USD,EUR,GBP,CAD,AUD,JPY,CHF,RSD,BGN'],
            'notes' => ['nullable', 'string', 'max:5000'],
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
            'name.required' => 'The customer name is required.',
            'name.max' => 'The customer name cannot exceed 255 characters.',
            'email.email' => 'Please enter a valid email address.',
            'email.max' => 'The email cannot exceed 255 characters.',
            'currency.required' => 'Please specify the default currency for this customer.',
            'currency.size' => 'The currency must be a valid 3-letter code.',
            'currency.in' => 'The selected currency is not supported.',
            'tax_country.size' => 'The tax country must be a valid 2-letter country code.',
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
            'company_name' => 'company name',
            'billing_address.street' => 'street address',
            'billing_address.city' => 'city',
            'billing_address.state' => 'state/province',
            'billing_address.postal_code' => 'postal code',
            'billing_address.country' => 'country',
            'tax_id' => 'tax ID',
            'tax_country' => 'tax country',
        ];
    }
}
