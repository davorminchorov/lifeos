<?php

declare(strict_types=1);

namespace App\Ai\Tools;

use App\Models\JobApplication;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Tools\Request;

class CreateJobApplication extends TenantScopedTool
{
    public function description(): string
    {
        return 'Create a new job application to track a position you are applying for.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'company_name' => $schema->string()->required()->description('Name of the company'),
            'job_title' => $schema->string()->required()->description('Title of the position'),
            'job_url' => $schema->string()->description('URL of the job posting'),
            'location' => $schema->string()->description('Job location'),
            'remote' => $schema->boolean()->description('Whether the position is remote'),
            'status' => $schema->string()->description('One of: wishlist, applied, screening, interview. Defaults to applied'),
            'source' => $schema->string()->description('One of: linkedin, company_website, job_board, referral, recruiter, networking, other. Defaults to other'),
            'notes' => $schema->string()->description('Additional notes about the application'),
        ];
    }

    public function handle(Request $request): string
    {
        $companyName = $request['company_name'] ?? null;
        $jobTitle = $request['job_title'] ?? null;

        $validated = $this->validate(
            ['company_name' => $companyName, 'job_title' => $jobTitle],
            [
                'company_name' => 'required|string|max:255',
                'job_title' => 'required|string|max:255',
            ],
        );

        if (is_string($validated)) {
            return $validated;
        }

        $status = $request['status'] ?? 'applied';
        $source = $request['source'] ?? 'other';

        $data = [
            'user_id' => $this->userId,
            'tenant_id' => $this->tenantId,
            'company_name' => $companyName,
            'job_title' => $jobTitle,
            'job_url' => $request['job_url'] ?? null,
            'location' => $request['location'] ?? null,
            'remote' => $request['remote'] ?? false,
            'status' => $status,
            'source' => $source,
            'applied_at' => date('Y-m-d'),
            'currency' => 'MKD',
            'notes' => $request['notes'] ?? null,
        ];

        JobApplication::create($data);

        return "Created job application: {$jobTitle} at {$companyName} (status: {$status})";
    }
}
