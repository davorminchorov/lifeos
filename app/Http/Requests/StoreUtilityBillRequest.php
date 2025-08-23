<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUtilityBillRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'utility_type' => ['required', 'string', 'max:255'],
            'service_provider' => ['required', 'string', 'max:255'],
            'account_number' => ['nullable', 'string', 'max:255'],
            'service_address' => ['nullable', 'string'],
            'bill_amount' => ['required', 'numeric', 'min:1', 'max:9999999'],
            'usage_amount' => ['nullable', 'numeric', 'min:1', 'max:999999'],
            'usage_unit' => ['nullable', 'string', 'max:50'],
            'rate_per_unit' => ['nullable', 'numeric', 'min:0.01', 'max:9999'],
            'bill_period_start' => ['required', 'date'],
            'bill_period_end' => ['required', 'date', 'after:bill_period_start'],
            'due_date' => ['required', 'date', 'after:bill_period_start'],
            'payment_status' => ['required', 'in:pending,paid,overdue'],
            'payment_date' => ['nullable', 'date'],
            'meter_readings' => ['nullable', 'array'],
            'bill_attachments' => ['nullable', 'array'],
            'service_plan' => ['nullable', 'string'],
            'contract_terms' => ['nullable', 'string'],
            'auto_pay_enabled' => ['boolean'],
            'usage_history' => ['nullable', 'array'],
            'budget_alert_threshold' => ['nullable', 'numeric', 'min:1', 'max:9999999'],
            'notes' => ['nullable', 'string'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'utility_type.required' => 'Please select a utility type.',
            'service_provider.required' => 'Please enter the service provider name.',
            'bill_amount.required' => 'Please enter the bill amount.',
            'bill_amount.min' => 'Bill amount must be a positive number.',
            'bill_period_start.required' => 'Please enter the billing period start date.',
            'bill_period_end.required' => 'Please enter the billing period end date.',
            'bill_period_end.after' => 'Billing period end date must be after the start date.',
            'due_date.required' => 'Please enter the due date.',
            'due_date.after' => 'Due date must be after the billing period start date.',
            'payment_status.required' => 'Please select a payment status.',
            'payment_status.in' => 'Payment status must be pending, paid, or overdue.',
        ];
    }
}
