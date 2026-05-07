<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Agents;

use App\Services\Agents\IdempotencyKey;
use Tests\TestCase;

class IdempotencyKeyPhase8Test extends TestCase
{
    private IdempotencyKey $keys;

    protected function setUp(): void
    {
        parent::setUp();
        $this->keys = new IdempotencyKey;
    }

    public function test_same_company_title_and_email_collapses(): void
    {
        $payload = [
            'company_name' => 'Acme',
            'job_title' => 'Senior Backend Engineer',
            'source_email_id' => 'gmail-msg-123',
        ];

        $this->assertSame(
            $this->keys->for('jobs.createApplication', 1, $payload),
            $this->keys->for('jobs.createApplication', 1, $payload),
        );
    }

    public function test_company_name_normalization_collapses_minor_drift(): void
    {
        $a = $this->keys->for('jobs.createApplication', 1, [
            'company_name' => 'Acme Corp',
            'job_title' => 'Senior Backend Engineer',
            'source_email_id' => 'gmail-1',
        ]);
        $b = $this->keys->for('jobs.createApplication', 1, [
            'company_name' => '  acme   corp  ',
            'job_title' => 'SENIOR BACKEND ENGINEER',
            'source_email_id' => 'gmail-1',
        ]);

        $this->assertSame($a, $b);
    }

    public function test_email_id_anchor_wins_over_url_when_both_present(): void
    {
        // Same email id with two different URLs should still collide because
        // the email id is the dominant anchor.
        $a = $this->keys->for('jobs.createApplication', 1, [
            'company_name' => 'Acme',
            'job_title' => 'Senior Backend Engineer',
            'source_email_id' => 'gmail-1',
            'job_url' => 'https://acme.example/careers/123',
        ]);
        $b = $this->keys->for('jobs.createApplication', 1, [
            'company_name' => 'Acme',
            'job_title' => 'Senior Backend Engineer',
            'source_email_id' => 'gmail-1',
            'job_url' => 'https://acme.example/careers/123?utm=x',
        ]);

        $this->assertSame($a, $b);
    }

    public function test_url_anchor_used_when_no_email_or_file_id(): void
    {
        $a = $this->keys->for('jobs.createApplication', 1, [
            'company_name' => 'Acme',
            'job_title' => 'Senior Backend Engineer',
            'job_url' => 'https://acme.example/careers/123',
        ]);
        $b = $this->keys->for('jobs.createApplication', 1, [
            'company_name' => 'Acme',
            'job_title' => 'Senior Backend Engineer',
            'job_url' => 'https://acme.example/careers/456',
        ]);

        $this->assertNotSame($a, $b);
    }

    public function test_distinct_companies_distinguish(): void
    {
        $a = $this->keys->for('jobs.createApplication', 1, [
            'company_name' => 'Acme',
            'job_title' => 'Senior Backend Engineer',
        ]);
        $b = $this->keys->for('jobs.createApplication', 1, [
            'company_name' => 'Globex',
            'job_title' => 'Senior Backend Engineer',
        ]);

        $this->assertNotSame($a, $b);
    }

    public function test_cross_tenant_keys_differ(): void
    {
        $payload = [
            'company_name' => 'Acme',
            'job_title' => 'Senior Backend Engineer',
            'source_email_id' => 'gmail-1',
        ];

        $this->assertNotSame(
            $this->keys->for('jobs.createApplication', 1, $payload),
            $this->keys->for('jobs.createApplication', 2, $payload),
        );
    }
}
