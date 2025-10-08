<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportInvestmentCsvRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:10240'],
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'Please upload a CSV file to import.',
            'file.mimes' => 'The import file must be a CSV (or TXT) file.',
            'file.max' => 'The CSV file may not be greater than 10MB.',
        ];
    }
}
