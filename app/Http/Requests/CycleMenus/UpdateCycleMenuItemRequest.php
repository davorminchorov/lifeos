<?php

namespace App\Http\Requests\CycleMenus;

use App\Enums\MealType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateCycleMenuItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'meal_type' => ['sometimes', 'required', new Enum(MealType::class)],
            'time_of_day' => ['sometimes', 'nullable', 'date_format:H:i'],
            'quantity' => ['sometimes', 'nullable', 'string', 'max:255'],
            'recipe_id' => ['sometimes', 'nullable', 'exists:recipes,id'],
            'position' => ['sometimes', 'integer', 'min:0'],
        ];
    }

    protected function prepareForValidation(): void
    {
        // Convert time from H:i to H:i:s for database storage
        if ($this->has('time_of_day') && $this->time_of_day && strlen($this->time_of_day) === 5) {
            $this->merge([
                'time_of_day' => $this->time_of_day . ':00',
            ]);
        }
    }
}
