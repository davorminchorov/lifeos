<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\AgentToken;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AgentTokenTest extends TestCase
{
    use RefreshDatabase;

    public function test_issue_returns_plaintext_once_and_stores_only_hash(): void
    {
        $user = User::factory()->create();
        $tenant = Tenant::factory()->create(['owner_id' => $user->id]);

        [$token, $plain] = AgentToken::issue($user, $tenant, 'test', ['*']);

        $this->assertStringStartsWith('lifeos_agent_', $plain);
        $this->assertNotEquals($plain, $token->token_hash);
        $this->assertSame(hash('sha256', $plain), $token->token_hash);
        $this->assertSame(['*'], $token->abilities);
        $this->assertSame($tenant->id, $token->tenant_id);
        $this->assertSame($user->id, $token->user_id);
    }

    public function test_resolve_finds_active_token_by_plaintext(): void
    {
        $user = User::factory()->create();
        $tenant = Tenant::factory()->create(['owner_id' => $user->id]);
        [$created, $plain] = AgentToken::issue($user, $tenant, 'test', ['expenses.list']);

        $resolved = AgentToken::resolve($plain);

        $this->assertNotNull($resolved);
        $this->assertSame($created->id, $resolved->id);
    }

    public function test_resolve_returns_null_for_unknown_token(): void
    {
        $this->assertNull(AgentToken::resolve('lifeos_agent_garbage'));
    }

    public function test_resolve_skips_revoked_tokens(): void
    {
        $user = User::factory()->create();
        $tenant = Tenant::factory()->create(['owner_id' => $user->id]);
        [$token, $plain] = AgentToken::issue($user, $tenant, 'test', ['*']);

        $token->forceFill(['revoked_at' => now()])->save();

        $this->assertNull(AgentToken::resolve($plain));
    }

    public function test_resolve_skips_expired_tokens(): void
    {
        $user = User::factory()->create();
        $tenant = Tenant::factory()->create(['owner_id' => $user->id]);
        [$token, $plain] = AgentToken::issue(
            $user,
            $tenant,
            'test',
            ['*'],
            expiresAt: Carbon::parse('-1 day'),
        );

        $this->assertNull(AgentToken::resolve($plain));
    }

    public function test_can_call_tool_matches_exact_wildcard_and_prefix(): void
    {
        $user = User::factory()->create();
        $tenant = Tenant::factory()->create(['owner_id' => $user->id]);

        [$exact] = AgentToken::issue($user, $tenant, 'a', ['expenses.list']);
        $this->assertTrue($exact->canCallTool('expenses.list'));
        $this->assertFalse($exact->canCallTool('expenses.create'));

        [$prefix] = AgentToken::issue($user, $tenant, 'b', ['expenses.*']);
        $this->assertTrue($prefix->canCallTool('expenses.list'));
        $this->assertTrue($prefix->canCallTool('expenses.create'));
        $this->assertFalse($prefix->canCallTool('subscriptions.list'));

        [$wildcard] = AgentToken::issue($user, $tenant, 'c', ['*']);
        $this->assertTrue($wildcard->canCallTool('anything.at.all'));
    }

    public function test_record_use_sets_last_used_at(): void
    {
        $user = User::factory()->create();
        $tenant = Tenant::factory()->create(['owner_id' => $user->id]);
        [$token] = AgentToken::issue($user, $tenant, 'test', ['*']);

        $this->assertNull($token->last_used_at);

        $token->recordUse();
        $token->refresh();

        $this->assertNotNull($token->last_used_at);
    }
}
