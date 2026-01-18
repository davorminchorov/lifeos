<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreWarrantyRequest extends FormRequest
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
            'product_name' => 'required|string|max:255',
            'brand' => 'required|string|max:255',
            'model' => 'nullable|string|max:255',
            'serial_number' => 'nullable|string|max:255',
            'purchase_date' => 'required|date|before_or_equal:today',
            'purchase_price' => 'required|numeric|min:1|max:99999999',
            'retailer' => 'nullable|string|max:255',
            'warranty_duration_months' => 'required|integer|min:1|max:120',
            'warranty_type' => 'required|in:manufacturer,extended,both',
            'warranty_terms' => 'nullable|string|max:2000',
            'warranty_expiration_date' => 'required|date|after:purchase_date',
            'current_status' => 'nullable|in:active,claimed,transferred,expired',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Get the validation messages that apply to the request.
     */
    public function messages(): array
    {
        return [
            'product_name.required' => 'Please enter the product name.',
            'brand.required' => 'Please enter the product brand.',
            'purchase_date.required' => 'Please select the purchase date.',
            'purchase_date.before_or_equal' => 'Purchase date cannot be in the future.',
            'purchase_price.required' => 'Please enter the purchase price.',
            'purchase_price.min' => 'Purchase price must be at least 1.',
            'warranty_duration_months.required' => 'Please enter the warranty duration.',
            'warranty_duration_months.min' => 'Warranty duration must be at least 1 month.',
            'warranty_duration_months.max' => 'Warranty duration cannot exceed 120 months.',
            'warranty_type.required' => 'Please select the warranty type.',
            'warranty_type.in' => 'Invalid warranty type selected.',
            'warranty_expiration_date.required' => 'Please select the warranty expiration date.',
            'warranty_expiration_date.after' => 'Warranty expiration date must be after purchase date.',
        ];
    }
}
