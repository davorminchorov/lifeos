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
        $data = [
            'company_name' => $request['company_name'] ?? null,
            'job_title' => $request['job_title'] ?? null,
            'job_url' => $request['job_url'] ?? null,
            'location' => $request['location'] ?? null,
            'remote' => $request['remote'] ?? false,
            'notes' => $request['notes'] ?? null,
            'status' => $request['status'] ?? 'applied',
            'source' => $request['source'] ?? 'other',
        ];

        $validated = $this->validate($data, [
            'company_name' => 'required|string|max:255',
            'job_title' => 'required|string|max:255',
            'job_url' => 'nullable|string|url|max:500',
            'location' => 'nullable|string|max:255',
            'remote' => 'nullable|boolean',
            'notes' => 'nullable|string|max:10000',
            'status' => 'required|string|in:wishlist,applied,screening,interview,assessment,offer,accepted,rejected,withdrawn,archived',
            'source' => 'required|string|in:linkedin,company_website,job_board,referral,recruiter,networking,other',
        ]);

        if (is_string($validated)) {
            return $validated;
        }

        JobApplication::create([
            'user_id' => $this->userId,
            'tenant_id' => $this->tenantId,
            ...$validated,
            'applied_at' => date('Y-m-d'),
            'currency' => 'MKD',
        ]);

        return "Created job application: {$validated['job_title']} at {$validated['company_name']} (status: {$validated['status']})";
    }
}
