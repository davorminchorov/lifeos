<?php

namespace App\Ai\Tools\JobApplications;

use App\Ai\Tools\Concerns\ResolvesContext;
use App\Enums\ApplicationSource;
use App\Enums\ApplicationStatus;
use App\Models\JobApplication;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;

class AddJobApplicationTool implements Tool
{
    use ResolvesContext;

    public function description(): string
    {
        return 'Add a new job application. Use when the user says they applied somewhere, are interested in a job, or want to track a position.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'company_name' => $schema->string()->description('Company name')->required(),
            'job_title' => $schema->string()->description('Job title/position')->required(),
            'status' => $schema->string()->description('Status: wishlist, applied, screening, interview, assessment, offer, accepted, rejected, withdrawn. Default: applied'),
            'source' => $schema->string()->description('Source: linkedin, company_website, referral, job_board, recruiter, other'),
            'location' => $schema->string()->description('Job location'),
            'remote' => $schema->boolean()->description('Is remote position'),
            'salary_min' => $schema->number()->description('Minimum salary'),
            'salary_max' => $schema->number()->description('Maximum salary'),
            'currency' => $schema->string()->description('Salary currency (default: EUR)'),
            'notes' => $schema->string()->description('Additional notes'),
        ];
    }

    public function handle(Request $request): string
    {
        $status = $request['status'] ?? 'applied';
        $source = $request['source'] ?? null;

        $application = JobApplication::create([
            'tenant_id' => $this->tenantId(),
            'user_id' => $this->userId(),
            'company_name' => $request['company_name'],
            'job_title' => $request['job_title'],
            'status' => ApplicationStatus::from($status),
            'source' => $source ? ApplicationSource::from($source) : null,
            'location' => $request['location'] ?? null,
            'remote' => $request['remote'] ?? false,
            'salary_min' => $request['salary_min'] ?? null,
            'salary_max' => $request['salary_max'] ?? null,
            'currency' => $request['currency'] ?? 'EUR',
            'applied_at' => $status === 'applied' ? now() : null,
            'notes' => $request['notes'] ?? null,
            'priority' => 3,
        ]);

        $result = "Job application created: {$application->company_name} — {$application->job_title}";
        $result .= "\nStatus: ".$application->status->label();

        if ($application->salary_min || $application->salary_max) {
            $result .= "\nSalary: ".$application->formatted_salary_range;
        }

        return $result;
    }
}
