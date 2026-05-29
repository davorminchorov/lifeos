<?php

namespace App\Http\Requests;

use App\Exports\ExportRegistry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDataExportRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $keys = app(ExportRegistry::class)->keys();

        return [
            'modules' => ['required', 'array', 'min:1'],
            'modules.*' => ['required', 'string', Rule::in($keys)],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ];
    }

    /**
     * The export filters extracted from the validated input.
     *
     * @return array<string, mixed>
     */
    public function filters(): array
    {
        return array_filter([
            'date_from' => $this->validated('date_from'),
            'date_to' => $this->validated('date_to'),
        ], fn ($value) => $value !== null && $value !== '');
    }
}
