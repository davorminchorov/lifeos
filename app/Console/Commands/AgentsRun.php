<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\AgentRun;
use App\Models\AgentToken;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Agents\AgentDefinition;
use App\Services\Agents\AgentRegistry;
use App\Services\Agents\AgentRunRecorder;
use App\Services\Agents\AgentSessionConfig;
use App\Services\Agents\ManagedAgentsClient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Throwable;

class AgentsRun extends Command
{
    protected $signature = 'agents:run
        {slug : Agent slug, e.g. "email-ingestion"}
        {--tenant= : Tenant slug or id (defaults to all enabled tenants)}
        {--user= : Override the user the agent runs as (email or id). Default: tenant owner.}
        {--dry-run : Resolve config + create the AgentRun row but do not call the Managed Agents API.}';

    protected $description = 'Start a Managed Agents session for the named agent against a tenant.';

    public function handle(
        AgentRegistry $registry,
        ManagedAgentsClient $client,
        AgentRunRecorder $recorder,
    ): int {
        $slug = (string) $this->argument('slug');

        try {
            $definition = $registry->find($slug);
        } catch (Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        if (! $registry->isEnabled($definition)) {
            $this->warn("Agent [{$slug}] is disabled by feature flag {$definition->featureFlag}.");

            return self::SUCCESS;
        }

        $tenants = $this->resolveTenants();

        if ($tenants === []) {
            $this->warn('No eligible tenants for this agent.');

            return self::SUCCESS;
        }

        $exit = self::SUCCESS;

        foreach ($tenants as $tenant) {
            if ($tenant->agents_writes_disabled) {
                $this->warn("Skipping tenant [{$tenant->slug}] — agents_writes_disabled.");

                continue;
            }

            $user = $this->resolveUser($tenant);

            if ($user === null) {
                $this->error("Tenant [{$tenant->slug}] has no resolvable user; skipping.");
                $exit = self::FAILURE;

                continue;
            }

            $this->runOnce($definition, $tenant, $user, $client, $recorder);
        }

        return $exit;
    }

    private function runOnce(
        AgentDefinition $definition,
        Tenant $tenant,
        User $user,
        ManagedAgentsClient $client,
        AgentRunRecorder $recorder,
    ): void {
        $this->info("agents:run {$definition->slug} → {$tenant->slug}");

        // Per-run, ephemeral agent token. We retain the row in agent_tokens
        // (so applied pending_actions can resolve agent_token_id) but expire
        // it shortly after the session ends.
        [$token, $plain] = AgentToken::issue(
            user: $user,
            tenant: $tenant,
            name: "agents:run {$definition->slug} ".now()->toIso8601String(),
            abilities: $definition->allowedTools,
            agentSlug: $definition->slug,
            expiresAt: now()->addSeconds($definition->maxSessionDurationSeconds + 600),
        );

        // Pass the plaintext into the lifeos MCP server config the API will
        // see, but never persist it on disk or in the run row.
        $servers = (array) Config::get('agents.mcp_servers', []);
        if (isset($servers['lifeos'])) {
            $servers['lifeos']['_plaintext'] = $plain;
            Config::set('agents.mcp_servers', $servers);
        }

        $run = $recorder->start($definition, $token);

        if ($this->option('dry-run')) {
            $this->line('  dry-run: skipping Managed Agents API call.');
            $recorder->complete($run);

            return;
        }

        try {
            $sessionConfig = (new AgentSessionConfig($definition, $token))->toArray();
            $session = $client->createSession($sessionConfig);
            $recorder->setSession($run, $session['id']);

            $sequence = 0;
            foreach ($client->streamEvents($session['id']) as $event) {
                $recorder->recordEvent($run, $event, ++$sequence);
            }

            $recorder->complete($run);
            $this->info("  done. session={$session['id']} pending_actions={$run->fresh()->pending_actions_created}");
        } catch (Throwable $e) {
            Log::error('agents:run failed', [
                'agent' => $definition->slug,
                'tenant' => $tenant->slug,
                'error' => $e->getMessage(),
            ]);
            $recorder->fail($run, $e);
            $this->error('  '.$e->getMessage());
        } finally {
            $token->forceFill(['revoked_at' => now()])->save();
        }
    }

    /**
     * @return array<int, Tenant>
     */
    private function resolveTenants(): array
    {
        $explicit = (string) ($this->option('tenant') ?? '');

        if ($explicit !== '') {
            $tenant = ctype_digit($explicit)
                ? Tenant::find((int) $explicit)
                : Tenant::where('slug', $explicit)->first();

            return $tenant === null ? [] : [$tenant];
        }

        return Tenant::query()->where('agents_writes_disabled', false)->get()->all();
    }

    private function resolveUser(Tenant $tenant): ?User
    {
        $explicit = (string) ($this->option('user') ?? '');

        if ($explicit !== '') {
            $user = ctype_digit($explicit)
                ? User::find((int) $explicit)
                : User::where('email', $explicit)->first();

            if ($user === null) {
                return null;
            }

            $hasAccess = $user->tenants()->where('tenants.id', $tenant->id)->exists()
                || $user->ownedTenants()->where('id', $tenant->id)->exists();

            return $hasAccess ? $user : null;
        }

        return $tenant->owner;
    }
}
