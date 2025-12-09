<?php

namespace App\Http\Requests\CycleMenus;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCycleMenuRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'starts_on' => ['sometimes', 'nullable', 'date'],
            'cycle_length_days' => ['sometimes', 'required', 'integer', 'min:1', 'max:365'],
            'is_active' => ['sometimes', 'boolean'],
            'notes' => ['sometimes', 'nullable', 'string'],
        ];
    }
}
