<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInvestmentTransactionRequest extends FormRequest
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
            'transaction_type' => ['required', 'in:buy,sell,dividend_reinvestment,stock_split,stock_dividend,merger,spinoff,transfer_in,transfer_out'],
            'quantity' => ['required', 'numeric', 'min:0', 'decimal:0,8'],
            'price_per_share' => ['required', 'numeric', 'min:0', 'decimal:0,8'],
            'total_amount' => ['required', 'numeric', 'min:0', 'decimal:0,8'],
            'fees' => ['nullable', 'numeric', 'min:0', 'decimal:0,2'],
            'taxes' => ['nullable', 'numeric', 'min:0', 'decimal:0,2'],
            'transaction_date' => ['required', 'date', 'before_or_equal:today'],
            'settlement_date' => ['nullable', 'date', 'after_or_equal:transaction_date'],
            'order_id' => ['nullable', 'string', 'max:255'],
            'confirmation_number' => ['nullable', 'string', 'max:255'],
            'account_number' => ['nullable', 'string', 'max:255'],
            'broker' => ['nullable', 'string', 'max:255'],
            'currency' => ['required', 'string', 'size:3'],
            'exchange_rate' => ['nullable', 'numeric', 'min:0', 'decimal:0,6'],
            'order_type' => ['nullable', 'in:market,limit,stop,stop_limit'],
            'limit_price' => ['nullable', 'numeric', 'min:0', 'decimal:0,8'],
            'stop_price' => ['nullable', 'numeric', 'min:0', 'decimal:0,8'],
            'notes' => ['nullable', 'string', 'max:65535'],
            'tax_lot_info' => ['nullable', 'array'],
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
            'transaction_type.required' => 'Transaction type is required.',
            'transaction_type.in' => 'Invalid transaction type selected.',
            'quantity.required' => 'Transaction quantity is required.',
            'quantity.numeric' => 'Transaction quantity must be a valid number.',
            'quantity.min' => 'Transaction quantity must be greater than or equal to 0.',
            'price_per_share.required' => 'Price per share is required.',
            'price_per_share.numeric' => 'Price per share must be a valid number.',
            'price_per_share.min' => 'Price per share must be greater than or equal to 0.',
            'total_amount.required' => 'Total transaction amount is required.',
            'total_amount.numeric' => 'Total transaction amount must be a valid number.',
            'total_amount.min' => 'Total transaction amount must be greater than or equal to 0.',
            'fees.numeric' => 'Transaction fees must be a valid number.',
            'fees.min' => 'Transaction fees must be greater than or equal to 0.',
            'taxes.numeric' => 'Transaction taxes must be a valid number.',
            'taxes.min' => 'Transaction taxes must be greater than or equal to 0.',
            'transaction_date.required' => 'Transaction date is required.',
            'transaction_date.date' => 'Transaction date must be a valid date.',
            'transaction_date.before_or_equal' => 'Transaction date cannot be in the future.',
            'settlement_date.date' => 'Settlement date must be a valid date.',
            'settlement_date.after_or_equal' => 'Settlement date must be on or after the transaction date.',
            'order_id.max' => 'Order ID cannot exceed 255 characters.',
            'confirmation_number.max' => 'Confirmation number cannot exceed 255 characters.',
            'account_number.max' => 'Account number cannot exceed 255 characters.',
            'broker.max' => 'Broker name cannot exceed 255 characters.',
            'currency.required' => 'Currency is required.',
            'currency.size' => 'Currency must be a 3-letter code (e.g., USD).',
            'exchange_rate.numeric' => 'Exchange rate must be a valid number.',
            'exchange_rate.min' => 'Exchange rate must be greater than or equal to 0.',
            'order_type.in' => 'Invalid order type selected.',
            'limit_price.numeric' => 'Limit price must be a valid number.',
            'limit_price.min' => 'Limit price must be greater than or equal to 0.',
            'stop_price.numeric' => 'Stop price must be a valid number.',
            'stop_price.min' => 'Stop price must be greater than or equal to 0.',
            'notes.max' => 'Notes cannot exceed 65,535 characters.',
            'tax_lot_info.array' => 'Tax lot information must be a valid array.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Custom validation: ensure total amount matches calculation
            if ($this->filled(['quantity', 'price_per_share'])) {
                $calculatedAmount = round($this->quantity * $this->price_per_share, 8);
                $tolerance = 0.01; // Allow small rounding differences

                if (abs($calculatedAmount - $this->total_amount) > $tolerance) {
                    $validator->errors()->add('total_amount', 'Total amount should equal quantity times price per share.');
                }
            }

            // Validate limit price is provided for limit orders
            if ($this->order_type === 'limit' && !$this->filled('limit_price')) {
                $validator->errors()->add('limit_price', 'Limit price is required for limit orders.');
            }

            // Validate stop price is provided for stop orders
            if (in_array($this->order_type, ['stop', 'stop_limit']) && !$this->filled('stop_price')) {
                $validator->errors()->add('stop_price', 'Stop price is required for stop orders.');
            }
        });
    }
}
