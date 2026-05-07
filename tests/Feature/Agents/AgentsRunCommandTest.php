<?php

declare(strict_types=1);

namespace Tests\Feature\Agents;

use App\Models\AgentRun;
use App\Models\AgentToken;
use App\Models\Expense;
use App\Models\PendingAction;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Agents\AgentRegistry;
use App\Services\Agents\ManagedAgentsClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class AgentsRunCommandTest extends TestCase
{
    use RefreshDatabase;

    private string $agentsPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agentsPath = storage_path('framework/testing/agents-'.uniqid());
        File::ensureDirectoryExists($this->agentsPath.'/email-ingestion');
        File::put($this->agentsPath.'/email-ingestion/agent.json', json_encode([
            'slug' => 'email-ingestion',
            'model' => 'claude-opus-4-7',
            'mcp_servers' => ['lifeos'],
            'allowed_tools' => ['expenses.create'],
            'feature_flag' => 'agents.email_ingestion.enabled',
        ]));
        File::put($this->agentsPath.'/email-ingestion/system.md', '## test prompt');

        Config::set('agents.definitions_path', $this->agentsPath);
        Config::set('agents.flags.agents.email_ingestion.enabled', true);
        Config::set('agents.mcp_servers', [
            'lifeos' => ['url' => 'http://localhost/mcp/lifeos', 'auth' => 'agent_token'],
        ]);
        Config::set('agents.anthropic.api_key', 'test');
        Config::set('agents.anthropic.base_url', 'https://api.anthropic.test');

        app(AgentRegistry::class)->flush();
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->agentsPath);
        parent::tearDown();
    }

    public function test_dry_run_creates_run_row_without_calling_api(): void
    {
        $user = User::factory()->create();
        $tenant = Tenant::factory()->create(['owner_id' => $user->id]);
        $user->forceFill(['current_tenant_id' => $tenant->id])->save();

        $this->artisan('agents:run', [
            'slug' => 'email-ingestion',
            '--tenant' => $tenant->slug,
            '--dry-run' => true,
        ])->assertSuccessful();

        $this->assertSame(1, AgentRun::query()->count());
        $run = AgentRun::query()->first();
        $this->assertSame(AgentRun::STATUS_COMPLETED, $run->status);
        $this->assertSame('email-ingestion', $run->agent_slug);
    }

    public function test_disabled_feature_flag_short_circuits(): void
    {
        Config::set('agents.flags.agents.email_ingestion.enabled', false);

        $user = User::factory()->create();
        $tenant = Tenant::factory()->create(['owner_id' => $user->id]);

        $this->artisan('agents:run', [
            'slug' => 'email-ingestion',
            '--tenant' => $tenant->slug,
        ])->expectsOutputToContain('disabled')
          ->assertSuccessful();

        $this->assertSame(0, AgentRun::query()->count());
    }

    public function test_writes_disabled_tenant_is_skipped(): void
    {
        $user = User::factory()->create();
        $tenant = Tenant::factory()->create([
            'owner_id' => $user->id,
            'agents_writes_disabled' => true,
        ]);

        $this->artisan('agents:run', [
            'slug' => 'email-ingestion',
            '--tenant' => $tenant->slug,
        ])->assertSuccessful();

        $this->assertSame(0, AgentRun::query()->count());
    }

    public function test_command_drives_session_via_mocked_client(): void
    {
        $user = User::factory()->create();
        $tenant = Tenant::factory()->create(['owner_id' => $user->id]);
        $user->forceFill(['current_tenant_id' => $tenant->id])->save();

        $client = $this->mock(ManagedAgentsClient::class);
        $client->shouldReceive('createSession')
            ->once()
            ->andReturn(['id' => 'sess_test', 'status' => 'running']);
        $client->shouldReceive('streamEvents')
            ->once()
            ->with('sess_test')
            ->andReturn((function (): \Generator {
                yield ['type' => 'tool_call', 'name' => 'expenses.create'];
                yield [
                    'type' => 'tool_result',
                    'structured_content' => ['pending_action_id' => 1, 'status' => 'pending'],
                ];
                yield ['type' => 'text', 'usage' => ['input_tokens' => 50, 'output_tokens' => 25]];
            })());

        $this->artisan('agents:run', [
            'slug' => 'email-ingestion',
            '--tenant' => $tenant->slug,
        ])->assertSuccessful();

        $run = AgentRun::query()->first();
        $this->assertNotNull($run);
        $this->assertSame('sess_test', $run->session_id);
        $this->assertSame(AgentRun::STATUS_COMPLETED, $run->status);
        $this->assertSame(50, (int) $run->tokens_in);
        $this->assertSame(25, (int) $run->tokens_out);
        $this->assertSame(['expenses.create' => 1], $run->tools_called);
        $this->assertSame(3, $run->events()->count());

        // The agent token issued for the run is revoked at the end.
        $this->assertSame(1, AgentToken::query()->whereNotNull('revoked_at')->count());
    }
}
