<?php

namespace App\Http\Requests\CycleMenus;

use Illuminate\Foundation\Http\FormRequest;

class StoreCycleMenuRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'starts_on' => ['nullable', 'date'],
            'cycle_length_days' => ['required', 'integer', 'min:1', 'max:365'],
            'is_active' => ['sometimes', 'boolean'],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Please enter a name for the cycle menu.',
            'cycle_length_days.min' => 'Cycle length must be at least 1 day.',
        ];
    }

    protected function prepareForValidation(): void
    {
        // Ensure is_active is converted to boolean (false if unchecked)
        if (! $this->has('is_active')) {
            $this->merge(['is_active' => false]);
        }
    }
}
