<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AgentRun extends Model
{
    /** @use HasFactory<\Database\Factories\AgentRunFactory> */
    use BelongsToTenant, HasFactory;

    public const STATUS_RUNNING = 'running';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_FAILED = 'failed';

    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'tenant_id',
        'user_id',
        'agent_token_id',
        'agent_slug',
        'session_id',
        'model',
        'status',
        'tools_called',
        'pending_actions_created',
        'tokens_in',
        'tokens_out',
        'cost_usd',
        'error',
        'started_at',
        'ended_at',
    ];

    protected function casts(): array
    {
        return [
            'tools_called' => 'array',
            'pending_actions_created' => 'integer',
            'tokens_in' => 'integer',
            'tokens_out' => 'integer',
            'cost_usd' => 'decimal:4',
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function agentToken(): BelongsTo
    {
        return $this->belongsTo(AgentToken::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(AgentRunEvent::class)->orderBy('sequence');
    }

    public function scopeRunning(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_RUNNING);
    }

    public function durationSeconds(): ?int
    {
        if ($this->started_at === null) {
            return null;
        }

        $end = $this->ended_at ?? now();

        return (int) $this->started_at->diffInSeconds($end);
    }
}
