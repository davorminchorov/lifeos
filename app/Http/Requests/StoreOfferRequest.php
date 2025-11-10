<?php

namespace App\Http\Requests;

use App\Enums\OfferStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOfferRequest extends FormRequest
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
            'job_application_id' => ['required', 'exists:job_applications,id'],
            'base_salary' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'bonus' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'equity' => ['nullable', 'string', 'max:1000'],
            'currency' => ['required', 'string', 'size:3', 'in:MKD,USD,EUR,GBP,CAD,AUD,JPY,CHF,RSD,BGN'],
            'benefits' => ['nullable', 'string', 'max:10000'],
            'start_date' => ['nullable', 'date', 'after:today'],
            'decision_deadline' => ['nullable', 'date', 'after:today'],
            'status' => ['required', Rule::enum(OfferStatus::class)],
            'notes' => ['nullable', 'string', 'max:10000'],
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
            'job_application_id.required' => 'The job application is required.',
            'job_application_id.exists' => 'The selected job application does not exist.',
            'base_salary.required' => 'The base salary is required.',
            'base_salary.numeric' => 'The base salary must be a valid number.',
            'base_salary.min' => 'The base salary cannot be negative.',
            'base_salary.max' => 'The base salary cannot exceed 99,999,999.99.',
            'bonus.numeric' => 'The bonus must be a valid number.',
            'bonus.min' => 'The bonus cannot be negative.',
            'bonus.max' => 'The bonus cannot exceed 99,999,999.99.',
            'equity.max' => 'The equity details cannot exceed 1,000 characters.',
            'currency.required' => 'Please specify the currency.',
            'currency.size' => 'The currency must be a valid 3-letter code.',
            'currency.in' => 'The selected currency is not supported. Please choose from: MKD, USD, EUR, GBP, CAD, AUD, JPY, CHF, RSD, BGN.',
            'benefits.max' => 'The benefits description cannot exceed 10,000 characters.',
            'start_date.date' => 'Please enter a valid start date.',
            'start_date.after' => 'The start date must be in the future.',
            'decision_deadline.date' => 'Please enter a valid decision deadline.',
            'decision_deadline.after' => 'The decision deadline must be in the future.',
            'status.required' => 'The offer status is required.',
            'notes.max' => 'The notes cannot exceed 10,000 characters.',
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
            'job_application_id' => 'job application',
            'base_salary' => 'base salary',
            'equity' => 'equity details',
            'start_date' => 'proposed start date',
            'decision_deadline' => 'decision deadline',
        ];
    }
}
