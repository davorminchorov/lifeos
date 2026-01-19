<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectInvestmentRequest extends FormRequest
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
            'name' => 'sometimes|required|string|max:255',
            'project_type' => 'nullable|string|max:100',
            'stage' => 'nullable|string|in:idea,prototype,mvp,growth,mature',
            'business_model' => 'nullable|string|in:subscription,ads,one-time,freemium',
            'website_url' => ['nullable', 'url:http,https', 'max:255'],
            'repository_url' => ['nullable', 'url:http,https', 'max:255'],
            'equity_percentage' => 'nullable|numeric|min:0|max:100',
            'current_value' => 'nullable|numeric|min:0|max:999999999',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'nullable|string|in:active,completed,sold,abandoned',
            'notes' => 'nullable|string|max:5000',
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Project name is required.',
            'name.max' => 'Project name cannot exceed 255 characters.',
            'equity_percentage.max' => 'Equity percentage cannot exceed 100%.',
            'website_url.url' => 'Please enter a valid URL for the website.',
            'repository_url.url' => 'Please enter a valid URL for the repository.',
            'end_date.after_or_equal' => 'End date must be after or equal to the start date.',
        ];
    }
}
