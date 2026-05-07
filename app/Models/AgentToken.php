<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class AgentToken extends Model
{
    /** @use HasFactory<\Database\Factories\AgentTokenFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'tenant_id',
        'name',
        'agent_slug',
        'token_hash',
        'abilities',
        'last_used_at',
        'expires_at',
        'revoked_at',
    ];

    protected function casts(): array
    {
        return [
            'abilities' => 'array',
            'last_used_at' => 'datetime',
            'expires_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Issue a new agent token. Returns [AgentToken, plaintext token] — the plaintext
     * is shown once and never persisted in cleartext.
     *
     * @param  array<int, string>  $abilities
     * @return array{0: AgentToken, 1: string}
     */
    public static function issue(
        User $user,
        Tenant $tenant,
        string $name,
        array $abilities,
        ?string $agentSlug = null,
        ?Carbon $expiresAt = null,
    ): array {
        $plain = 'lifeos_agent_'.Str::random(48);

        $token = static::create([
            'user_id' => $user->id,
            'tenant_id' => $tenant->id,
            'name' => $name,
            'agent_slug' => $agentSlug,
            'token_hash' => hash('sha256', $plain),
            'abilities' => array_values(array_unique($abilities)),
            'expires_at' => $expiresAt,
        ]);

        return [$token, $plain];
    }

    /**
     * Resolve a plaintext token to an active AgentToken row, or null.
     */
    public static function resolve(string $plain): ?self
    {
        return static::query()
            ->where('token_hash', hash('sha256', $plain))
            ->active()
            ->first();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query
            ->whereNull('revoked_at')
            ->where(function (Builder $q): void {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    public function isActive(): bool
    {
        if ($this->revoked_at !== null) {
            return false;
        }

        if ($this->expires_at !== null && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Check whether this token may invoke the given tool name.
     *
     * Abilities are tool name patterns: '*' (any tool), 'expenses.*' (any expenses tool),
     * 'expenses.list' (exact). Read-only convenience: 'read:*' allows any tool whose
     * canonical name starts with a known read-tool prefix (the MCP server is the
     * authoritative source — abilities are not interpreted by tool classification).
     */
    public function canCallTool(string $tool): bool
    {
        foreach ($this->abilities ?? [] as $ability) {
            if ($ability === '*' || $ability === $tool) {
                return true;
            }

            if (str_ends_with($ability, '.*')) {
                $prefix = substr($ability, 0, -1);
                if (str_starts_with($tool, $prefix)) {
                    return true;
                }
            }
        }

        return false;
    }

    public function recordUse(): void
    {
        $this->forceFill(['last_used_at' => now()])->saveQuietly();
    }
}
