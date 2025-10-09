<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportInvestmentCsvRequest extends FormRequest
{
    /**
     * Allow all requests to proceed with validation.
     *
     * @return bool `true` to authorize the request, `false` otherwise.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules for the import CSV request.
     *
     * Specifies that 'file' is required, must be an uploaded file with MIME type `csv` or `txt`,
     * and must not exceed 10240 kilobytes (10 MB).
     *
     * @return array The validation rules keyed by input name.
     */
    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:10240'],
        ];
    }

    /**
     * Custom validation messages for the CSV import request.
     *
     * Provides user-facing error messages keyed by validation rule names.
     *
     * @return array<string, string> Mapping of validation rule keys to their messages.
     */
    public function messages(): array
    {
        return [
            'file.required' => 'Please upload a CSV file to import.',
            'file.mimes' => 'The import file must be a CSV (or TXT) file.',
            'file.max' => 'The CSV file may not be greater than 10MB.',
        ];
    }
}