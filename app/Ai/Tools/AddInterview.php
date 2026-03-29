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
        $companyName = $request->get('company_name');

        $application = $this->scopedQuery(JobApplication::class)
            ->where('company_name', 'LIKE', '%'.$companyName.'%')
            ->first();

        if (! $application) {
            $available = $this->scopedQuery(JobApplication::class)
                ->pluck('company_name')
                ->implode(', ');

            return "No job application found for '{$companyName}'. Available: {$available}";
        }

        $type = $request->get('type', 'video');
        $scheduledAt = $request->get('scheduled_at');
        $durationMinutes = $request->get('duration_minutes', 60);

        JobApplicationInterview::create([
            'tenant_id' => $this->tenantId,
            'user_id' => $this->userId,
            'job_application_id' => $application->id,
            'type' => $type,
            'scheduled_at' => $scheduledAt,
            'duration_minutes' => $durationMinutes,
            'notes' => $request->get('notes'),
        ]);

        return "Added {$type} interview for {$application->company_name} on {$scheduledAt}";
    }
}
