<?php

declare(strict_types=1);

namespace Tests\Feature\Mcp;

use App\Mcp\LifeOsServer;
use App\Mcp\Tools\Jobs\CreateApplication;
use App\Models\AgentToken;
use App\Models\JobApplication;
use App\Models\PendingAction;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Agents\PendingActionApplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Illuminate\Testing\Fluent\AssertableJson;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class Phase8JobSearchTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        ['user' => $this->user, 'tenant' => $this->tenant] = $this->setupTenantContext();
        [$token] = AgentToken::issue($this->user, $this->tenant, 'phpunit', ['*']);
        App::instance('agent.token', $token);
    }

    private function jobArgs(array $overrides = []): array
    {
        return array_merge([
            'company_name' => 'Acme',
            'job_title' => 'Senior Backend Engineer',
            'job_description' => 'Build backend services.',
            'job_url' => 'https://acme.example/careers/123',
            'location' => 'Remote',
            'remote' => true,
            'salary_min' => 60000,
            'salary_max' => 90000,
            'currency' => 'EUR',
            'status' => 'discovered',
            'source' => 'recruiter',
            'notes' => 'Matches: Laravel, AWS, EU-remote.',
            'source_email_id' => 'gmail-msg-1',
        ], $overrides);
    }

    public function test_create_application_queues_pending_action(): void
    {
        LifeOsServer::tool(CreateApplication::class, $this->jobArgs())
            ->assertOk()
            ->assertStructuredContent(function (AssertableJson $json) {
                $json->where('status', PendingAction::STATUS_PENDING)->etc();
            });

        $this->assertSame(1, PendingAction::query()->where('tool', 'jobs.createApplication')->count());
        $this->assertSame(0, JobApplication::query()->count());
    }

    public function test_idempotent_on_company_title_and_email(): void
    {
        $args = $this->jobArgs();

        LifeOsServer::tool(CreateApplication::class, $args);
        LifeOsServer::tool(CreateApplication::class, $args);

        $this->assertSame(1, PendingAction::query()->count());
    }

    public function test_apply_creates_job_application_with_discovered_status(): void
    {
        LifeOsServer::tool(CreateApplication::class, $this->jobArgs());

        $action = PendingAction::query()->firstOrFail();
        app(PendingActionApplier::class)->apply($action, $this->user);

        $this->assertSame(1, JobApplication::query()->count());
        $job = JobApplication::query()->first();
        $this->assertSame('Acme', $job->company_name);
        $this->assertSame('Senior Backend Engineer', $job->job_title);
        // Status is stored via cast (ApplicationStatus enum); compare on the value.
        $value = is_object($job->status) ? $job->status->value : $job->status;
        $this->assertSame('discovered', $value);
    }

    public function test_revert_deletes_created_application(): void
    {
        LifeOsServer::tool(CreateApplication::class, $this->jobArgs());

        $action = PendingAction::query()->firstOrFail();
        $applied = app(PendingActionApplier::class)->apply($action, $this->user);
        $this->assertSame(1, JobApplication::query()->count());

        app(PendingActionApplier::class)->revert($applied, $this->user);

        $this->assertSame(0, JobApplication::query()->count());
    }

    public function test_unauthorized_token_blocks_tool(): void
    {
        [$restricted] = AgentToken::issue($this->user, $this->tenant, 'restricted', ['jobs.pipeline']);
        App::instance('agent.token', $restricted);

        LifeOsServer::tool(CreateApplication::class, $this->jobArgs())
            ->assertHasErrors(['Agent token is not authorized to call [jobs.createApplication].']);
    }

    public function test_invalid_url_is_rejected_at_apply_time(): void
    {
        // Server-side queueing is permissive; the FormRequest-style validator
        // in the applier rejects bad data when the human approves.
        LifeOsServer::tool(CreateApplication::class, $this->jobArgs([
            'job_url' => 'not-a-url',
        ]));

        $action = PendingAction::query()->firstOrFail();

        $this->expectException(ValidationException::class);
        app(PendingActionApplier::class)->apply($action, $this->user);
    }
}
