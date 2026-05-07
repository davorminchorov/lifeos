<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\AgentToken;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class AgentsTokensIssue extends Command
{
    protected $signature = 'agents:tokens:issue
        {user : User email or id}
        {tenant : Tenant slug or id}
        {--name= : Human-readable token name}
        {--abilities=read:* : Comma-separated tool names or patterns (e.g. "expenses.*,subscriptions.list")}
        {--agent= : Agent slug this token is bound to (optional)}
        {--expires= : Expiry as ISO timestamp or relative (e.g. "+30 days")}';

    protected $description = 'Issue an agent token bound to a (user, tenant) pair. Prints the plaintext token once.';

    public function handle(): int
    {
        $user = $this->resolveUser((string) $this->argument('user'));

        if ($user === null) {
            $this->error('User not found.');

            return self::FAILURE;
        }

        $tenant = $this->resolveTenant((string) $this->argument('tenant'));

        if ($tenant === null) {
            $this->error('Tenant not found.');

            return self::FAILURE;
        }

        if (! $this->userHasAccess($user, $tenant)) {
            $this->error("User {$user->email} does not have access to tenant {$tenant->slug}.");

            return self::FAILURE;
        }

        $abilities = collect(explode(',', (string) $this->option('abilities')))
            ->map(fn ($ability) => trim((string) $ability))
            ->filter()
            ->values()
            ->all();

        if ($abilities === []) {
            $this->error('At least one ability is required.');

            return self::FAILURE;
        }

        $name = (string) ($this->option('name') ?? "agent token for {$user->email} @ {$tenant->slug}");
        $expiresAt = $this->resolveExpiry((string) ($this->option('expires') ?? ''));

        [$token, $plain] = AgentToken::issue(
            user: $user,
            tenant: $tenant,
            name: $name,
            abilities: $abilities,
            agentSlug: $this->option('agent') !== null ? (string) $this->option('agent') : null,
            expiresAt: $expiresAt,
        );

        $this->info('Agent token issued. Save this token now — it will not be shown again.');
        $this->newLine();
        $this->line("ID:        {$token->id}");
        $this->line("Name:      {$token->name}");
        $this->line("User:      {$user->email} (#{$user->id})");
        $this->line("Tenant:    {$tenant->slug} (#{$tenant->id})");
        $this->line('Abilities: '.implode(', ', $abilities));
        $this->line('Expires:   '.($token->expires_at?->toIso8601String() ?? 'never'));
        $this->newLine();
        $this->line('Token:     '.$plain);

        return self::SUCCESS;
    }

    private function resolveUser(string $value): ?User
    {
        if (ctype_digit($value)) {
            return User::find((int) $value);
        }

        return User::where('email', $value)->first();
    }

    private function resolveTenant(string $value): ?Tenant
    {
        if (ctype_digit($value)) {
            return Tenant::find((int) $value);
        }

        return Tenant::where('slug', $value)->first();
    }

    private function userHasAccess(User $user, Tenant $tenant): bool
    {
        return $user->tenants()->where('tenants.id', $tenant->id)->exists()
            || $user->ownedTenants()->where('id', $tenant->id)->exists();
    }

    private function resolveExpiry(string $raw): ?Carbon
    {
        if ($raw === '') {
            return null;
        }

        try {
            return Carbon::parse($raw);
        } catch (\Throwable) {
            $this->warn("Could not parse expiry '{$raw}'; issuing a non-expiring token.");

            return null;
        }
    }
}
