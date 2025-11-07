<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateIouRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && $this->route('iou')->user_id === auth()->id();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type' => 'required|in:owe,owed',
            'person_name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01|max:99999999',
            'currency' => 'nullable|string|size:3',
            'transaction_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:transaction_date',
            'description' => 'required|string|max:65535',
            'notes' => 'nullable|string|max:65535',
            'status' => 'nullable|in:pending,partially_paid,paid,cancelled',
            'amount_paid' => 'nullable|numeric|min:0|lte:amount',
            'payment_method' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:255',
            'attachments' => 'nullable|array',
            'is_recurring' => 'nullable|boolean',
            'recurring_schedule' => 'nullable|string|max:255',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'type.required' => 'Please select whether you owe money or someone owes you money.',
            'type.in' => 'Invalid type selected. Must be either "I Owe" or "Owed to Me".',
            'person_name.required' => 'Please enter the name of the person involved.',
            'amount.required' => 'Please enter the amount.',
            'amount.min' => 'The amount must be greater than zero.',
            'amount.max' => 'The amount is too large.',
            'transaction_date.required' => 'Please select the transaction date.',
            'due_date.after_or_equal' => 'The due date must be on or after the transaction date.',
            'description.required' => 'Please provide a description.',
            'amount_paid.lte' => 'The amount paid cannot exceed the total amount.',
        ];
    }
}
