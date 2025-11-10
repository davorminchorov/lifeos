<?php

namespace App\Http\Requests;

use App\Enums\InterviewOutcome;
use App\Enums\InterviewType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInterviewRequest extends FormRequest
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
            'job_application_id' => ['required', 'exists:job_applications,id'],
            'type' => ['required', Rule::enum(InterviewType::class)],
            'scheduled_at' => ['required', 'date', 'after:now'],
            'duration_minutes' => ['nullable', 'integer', 'min:1', 'max:480'],
            'location' => ['nullable', 'string', 'max:500'],
            'video_link' => ['nullable', 'string', 'url', 'max:500'],
            'interviewer_name' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:10000'],
            'feedback' => ['nullable', 'string', 'max:10000'],
            'outcome' => ['nullable', Rule::enum(InterviewOutcome::class)],
            'completed' => ['boolean'],
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
            'job_application_id.required' => 'The job application is required.',
            'job_application_id.exists' => 'The selected job application does not exist.',
            'type.required' => 'The interview type is required.',
            'scheduled_at.required' => 'The interview date and time is required.',
            'scheduled_at.date' => 'Please enter a valid date and time.',
            'scheduled_at.after' => 'The interview must be scheduled for a future date and time.',
            'duration_minutes.integer' => 'The duration must be a number.',
            'duration_minutes.min' => 'The duration must be at least 1 minute.',
            'duration_minutes.max' => 'The duration cannot exceed 480 minutes (8 hours).',
            'location.max' => 'The location cannot exceed 500 characters.',
            'video_link.url' => 'Please enter a valid video call URL.',
            'video_link.max' => 'The video link cannot exceed 500 characters.',
            'interviewer_name.max' => 'The interviewer name cannot exceed 255 characters.',
            'notes.max' => 'The notes cannot exceed 10,000 characters.',
            'feedback.max' => 'The feedback cannot exceed 10,000 characters.',
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
            'job_application_id' => 'job application',
            'scheduled_at' => 'interview date/time',
            'duration_minutes' => 'duration',
            'video_link' => 'video call link',
            'interviewer_name' => 'interviewer name',
        ];
    }
}
