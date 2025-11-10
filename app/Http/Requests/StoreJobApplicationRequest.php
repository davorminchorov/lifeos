<?php

namespace App\Http\Requests;

use App\Enums\ApplicationSource;
use App\Enums\ApplicationStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreJobApplicationRequest extends FormRequest
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
            // Company information
            'company_name' => ['required', 'string', 'max:255'],
            'company_website' => ['nullable', 'string', 'url', 'max:500'],

            // Job details
            'job_title' => ['required', 'string', 'max:255'],
            'job_description' => ['nullable', 'string', 'max:10000'],
            'job_url' => ['nullable', 'string', 'url', 'max:500'],
            'location' => ['nullable', 'string', 'max:255'],
            'remote' => ['nullable', 'boolean'],

            // Salary information
            'salary_min' => ['nullable', 'numeric', 'min:0', 'max:99999999.99', 'lte:salary_max'],
            'salary_max' => ['nullable', 'numeric', 'min:0', 'max:99999999.99', 'gte:salary_min'],
            'currency' => ['required', 'string', 'size:3', 'in:MKD,USD,EUR,GBP,CAD,AUD,JPY,CHF,RSD,BGN'],

            // Application details
            'status' => ['required', Rule::enum(ApplicationStatus::class)],
            'source' => ['required', Rule::enum(ApplicationSource::class)],
            'applied_at' => ['nullable', 'date', 'before_or_equal:today'],
            'next_action_at' => ['nullable', 'date'],
            'priority' => ['nullable', 'integer', 'min:0', 'max:3'],

            // Contact information
            'contact_name' => ['nullable', 'string', 'max:255'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:50'],

            // Additional information
            'notes' => ['nullable', 'string', 'max:10000'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:50'],
            'file_attachments' => ['nullable', 'array'],
            'file_attachments.*' => ['string', 'max:500'],
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
            'company_name.required' => 'The company name is required.',
            'company_name.max' => 'The company name cannot exceed 255 characters.',
            'company_website.url' => 'Please enter a valid website URL.',
            'job_title.required' => 'The job title is required.',
            'job_title.max' => 'The job title cannot exceed 255 characters.',
            'job_description.max' => 'The job description cannot exceed 10,000 characters.',
            'job_url.url' => 'Please enter a valid job posting URL.',
            'location.max' => 'The location cannot exceed 255 characters.',
            'salary_min.numeric' => 'The minimum salary must be a valid number.',
            'salary_min.min' => 'The minimum salary cannot be negative.',
            'salary_min.lte' => 'The minimum salary cannot be greater than the maximum salary.',
            'salary_max.numeric' => 'The maximum salary must be a valid number.',
            'salary_max.min' => 'The maximum salary cannot be negative.',
            'salary_max.gte' => 'The maximum salary cannot be less than the minimum salary.',
            'currency.required' => 'Please specify the currency.',
            'currency.size' => 'The currency must be a valid 3-letter code.',
            'currency.in' => 'The selected currency is not supported. Please choose from: MKD, USD, EUR, GBP, CAD, AUD, JPY, CHF, RSD, BGN.',
            'status.required' => 'The application status is required.',
            'source.required' => 'Please specify how you found this job.',
            'applied_at.date' => 'Please enter a valid application date.',
            'applied_at.before_or_equal' => 'The application date cannot be in the future.',
            'next_action_at.date' => 'Please enter a valid date for the next action.',
            'priority.integer' => 'The priority must be a number.',
            'priority.min' => 'The priority must be between 0 and 3.',
            'priority.max' => 'The priority must be between 0 and 3.',
            'contact_email.email' => 'Please enter a valid email address.',
            'contact_phone.max' => 'The contact phone cannot exceed 50 characters.',
            'notes.max' => 'The notes cannot exceed 10,000 characters.',
            'tags.*.max' => 'Each tag cannot exceed 50 characters.',
            'file_attachments.*.max' => 'Each file path cannot exceed 500 characters.',
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
            'company_website' => 'company website',
            'job_title' => 'job title',
            'job_description' => 'job description',
            'job_url' => 'job URL',
            'salary_min' => 'minimum salary',
            'salary_max' => 'maximum salary',
            'applied_at' => 'application date',
            'next_action_at' => 'next action date',
            'contact_name' => 'contact name',
            'contact_email' => 'contact email',
            'contact_phone' => 'contact phone',
        ];
    }
}
