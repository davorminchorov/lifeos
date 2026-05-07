<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Agents;

use App\Services\Agents\AgentRegistry;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Tests\TestCase;

class AgentRegistryTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        parent::setUp();

        $this->root = storage_path('framework/testing/agents-'.uniqid());
        File::ensureDirectoryExists($this->root);
        Config::set('agents.definitions_path', $this->root);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->root);
        parent::tearDown();
    }

    private function writeAgent(string $slug, array $config, string $prompt): void
    {
        $dir = $this->root.'/'.$slug;
        File::ensureDirectoryExists($dir);
        File::put($dir.'/agent.json', json_encode($config));
        File::put($dir.'/system.md', $prompt);
    }

    public function test_loads_agent_from_filesystem(): void
    {
        $this->writeAgent('email-ingestion', [
            'slug' => 'email-ingestion',
            'model' => 'claude-opus-4-7',
            'mcp_servers' => ['lifeos', 'gmail'],
            'allowed_tools' => ['expenses.create'],
            'feature_flag' => 'agents.email_ingestion.enabled',
            'max_session_duration_seconds' => 600,
            'max_tool_calls' => 200,
        ], '## test prompt');

        $registry = new AgentRegistry;
        $def = $registry->find('email-ingestion');

        $this->assertSame('email-ingestion', $def->slug);
        $this->assertSame('claude-opus-4-7', $def->model);
        $this->assertSame(['lifeos', 'gmail'], $def->mcpServers);
        $this->assertSame(['expenses.create'], $def->allowedTools);
        $this->assertSame('## test prompt', $def->systemPrompt);
    }

    public function test_throws_when_agent_missing(): void
    {
        $registry = new AgentRegistry;

        $this->expectException(RuntimeException::class);
        $registry->find('nonexistent');
    }

    public function test_skips_directories_without_required_files(): void
    {
        File::ensureDirectoryExists($this->root.'/incomplete');
        // Only agent.json, no system.md.
        File::put($this->root.'/incomplete/agent.json', json_encode([
            'slug' => 'incomplete',
            'model' => 'x',
            'mcp_servers' => [],
            'allowed_tools' => [],
            'feature_flag' => 'x',
        ]));

        $registry = new AgentRegistry;

        $this->assertSame([], $registry->all());
    }

    public function test_feature_flag_resolves_via_config(): void
    {
        $this->writeAgent('flagged', [
            'slug' => 'flagged',
            'model' => 'm',
            'mcp_servers' => [],
            'allowed_tools' => [],
            'feature_flag' => 'agents.flagged.enabled',
        ], 'p');

        Config::set('agents.flags.agents.flagged.enabled', true);

        $registry = new AgentRegistry;
        $this->assertTrue($registry->isEnabled($registry->find('flagged')));

        Config::set('agents.flags.agents.flagged.enabled', false);
        $registry->flush();
        $this->assertFalse($registry->isEnabled($registry->find('flagged')));
    }
}
