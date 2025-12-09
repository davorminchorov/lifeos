<?php

namespace App\Http\Requests\CycleMenus;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCycleMenuDayRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'notes' => ['sometimes', 'nullable', 'string'],
        ];
    }
}
