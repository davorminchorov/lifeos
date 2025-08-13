<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateContractRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && $this->route('contract')->user_id === auth()->id();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'contract_type' => ['sometimes', 'string', 'max:100'],
            'title' => ['sometimes', 'string', 'max:255'],
            'counterparty' => ['sometimes', 'string', 'max:255'],
            'start_date' => ['sometimes', 'date'],
            'end_date' => ['nullable', 'date', 'after:start_date'],
            'notice_period_days' => ['nullable', 'integer', 'min:1', 'max:365'],
            'auto_renewal' => ['boolean'],
            'contract_value' => ['nullable', 'numeric', 'min:0', 'max:999999999.99'],
            'payment_terms' => ['nullable', 'string', 'max:255'],
            'key_obligations' => ['nullable', 'string', 'max:5000'],
            'penalties' => ['nullable', 'string', 'max:2000'],
            'termination_clauses' => ['nullable', 'string', 'max:2000'],
            'document_attachments' => ['nullable', 'array'],
            'document_attachments.*' => ['string', 'max:255'],
            'performance_rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'renewal_history' => ['nullable', 'array'],
            'renewal_history.*.date' => ['required_with:renewal_history', 'date'],
            'renewal_history.*.action' => ['required_with:renewal_history', 'string', 'max:100'],
            'amendments' => ['nullable', 'array'],
            'amendments.*.date' => ['required_with:amendments', 'date'],
            'amendments.*.change' => ['required_with:amendments', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'status' => ['sometimes', 'string', 'in:active,expired,terminated,pending'],
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
            'contract_type.max' => 'The contract type cannot exceed 100 characters.',
            'title.max' => 'The contract title cannot exceed 255 characters.',
            'counterparty.max' => 'The counterparty name cannot exceed 255 characters.',
            'end_date.after' => 'The end date must be after the start date.',
            'notice_period_days.min' => 'The notice period must be at least 1 day.',
            'notice_period_days.max' => 'The notice period cannot exceed 365 days.',
            'contract_value.numeric' => 'The contract value must be a valid number.',
            'contract_value.min' => 'The contract value cannot be negative.',
            'contract_value.max' => 'The contract value cannot exceed $999,999,999.99.',
            'payment_terms.max' => 'The payment terms cannot exceed 255 characters.',
            'key_obligations.max' => 'The key obligations cannot exceed 5,000 characters.',
            'penalties.max' => 'The penalties section cannot exceed 2,000 characters.',
            'termination_clauses.max' => 'The termination clauses cannot exceed 2,000 characters.',
            'document_attachments.*.max' => 'Each document attachment path cannot exceed 255 characters.',
            'performance_rating.min' => 'The performance rating must be between 1 and 5.',
            'performance_rating.max' => 'The performance rating must be between 1 and 5.',
            'renewal_history.*.date.required_with' => 'Each renewal history entry must include a date.',
            'renewal_history.*.action.required_with' => 'Each renewal history entry must include an action.',
            'amendments.*.date.required_with' => 'Each amendment entry must include a date.',
            'amendments.*.change.required_with' => 'Each amendment entry must include a description of the change.',
            'notes.max' => 'The notes cannot exceed 2,000 characters.',
            'status.in' => 'The status must be active, expired, terminated, or pending.',
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
            'contract_type' => 'contract type',
            'counterparty' => 'counterparty',
            'start_date' => 'start date',
            'end_date' => 'end date',
            'notice_period_days' => 'notice period',
            'auto_renewal' => 'auto-renewal setting',
            'contract_value' => 'contract value',
            'payment_terms' => 'payment terms',
            'key_obligations' => 'key obligations',
            'termination_clauses' => 'termination clauses',
            'document_attachments' => 'document attachments',
            'performance_rating' => 'performance rating',
            'renewal_history' => 'renewal history',
            'amendments' => 'amendments',
        ];
    }
}
