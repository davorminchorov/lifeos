<?php

namespace App\Http\Requests\CycleMenus;

use App\Enums\MealType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreCycleMenuItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'cycle_menu_day_id' => ['required', 'exists:cycle_menu_days,id'],
            'title' => ['required', 'string', 'max:255'],
            'meal_type' => ['required', new Enum(MealType::class)],
            'time_of_day' => ['nullable', 'date_format:H:i,H:i:s'],
            'quantity' => ['nullable', 'string', 'max:255'],
            'recipe_id' => ['nullable', 'exists:recipes,id'],
            'position' => ['nullable', 'integer', 'min:0'],
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
