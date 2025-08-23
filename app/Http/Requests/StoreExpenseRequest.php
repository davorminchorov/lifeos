<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreExpenseRequest extends FormRequest
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
            'amount' => 'required|numeric|min:1|max:99999999',
            'currency' => 'nullable|string|size:3',
            'category' => 'required|string|max:255',
            'subcategory' => 'nullable|string|max:255',
            'expense_date' => 'required|date',
            'description' => 'required|string|max:65535',
            'merchant' => 'nullable|string|max:255',
            'payment_method' => 'nullable|string|max:255',
            'receipt_attachments' => 'nullable|array',
            'tags' => 'nullable|array',
            'location' => 'nullable|string|max:255',
            'is_tax_deductible' => 'nullable|boolean',
            'expense_type' => 'nullable|in:business,personal',
            'is_recurring' => 'nullable|boolean',
            'recurring_schedule' => 'nullable|string|max:255',
            'budget_allocated' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:65535',
            'status' => 'nullable|in:pending,confirmed,reimbursed',
        ];
    }
}
