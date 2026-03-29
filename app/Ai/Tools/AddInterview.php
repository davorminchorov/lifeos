<?php

declare(strict_types=1);

namespace App\Ai\Tools;

use App\Models\JobApplication;
use App\Models\JobApplicationInterview;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Tools\Request;

class AddInterview extends TenantScopedTool
{
    public function description(): string
    {
        return 'Add an interview to an existing job application.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'company_name' => $schema->string()->required()->description('Company name to find the job application'),
            'scheduled_at' => $schema->string()->required()->description('Date and time in YYYY-MM-DD HH:MM format'),
            'type' => $schema->string()->description('One of: phone, video, onsite, panel, technical, behavioral, final. Defaults to video'),
            'duration_minutes' => $schema->integer()->description('Duration in minutes, defaults to 60'),
            'notes' => $schema->string()->description('Additional notes about the interview'),
        ];
    }

    public function handle(Request $request): string
    {
        $companyName = $request['company_name'] ?? null;

        $matches = $this->scopedQuery(JobApplication::class)
            ->where('company_name', 'LIKE', '%'.$companyName.'%')
            ->limit(5)
            ->get();

        if ($matches->isEmpty()) {
            $available = $this->scopedQuery(JobApplication::class)
                ->pluck('company_name')
                ->implode(', ');

            return "No job application found for '{$companyName}'. Available: {$available}";
        }

        if ($matches->count() > 1) {
            $names = $matches->pluck('company_name')->implode(', ');

            return "Multiple applications match '{$companyName}'. Please be more specific: {$names}";
        }

        $application = $matches->first();

        $type = $request['type'] ?? 'video';
        $scheduledAt = $request['scheduled_at'] ?? null;
        $durationMinutes = $request['duration_minutes'] ?? 60;

        $validated = $this->validate([
            'scheduled_at' => $scheduledAt,
            'type' => $type,
            'duration_minutes' => $durationMinutes,
        ], [
            'scheduled_at' => 'required|date',
            'type' => 'required|string|in:phone,video,onsite,panel,technical,behavioral,final',
            'duration_minutes' => 'required|integer|min:1|max:480',
        ]);

        if (is_string($validated)) {
            return $validated;
        }

        JobApplicationInterview::create([
            'tenant_id' => $this->tenantId,
            'user_id' => $this->userId,
            'job_application_id' => $application->id,
            'type' => $validated['type'],
            'scheduled_at' => $validated['scheduled_at'],
            'duration_minutes' => $validated['duration_minutes'],
            'notes' => $request['notes'] ?? null,
        ]);

        return "Added {$validated['type']} interview for {$application->company_name} on {$validated['scheduled_at']}";
    }
}
