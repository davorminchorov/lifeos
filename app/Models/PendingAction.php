<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PendingAction extends Model
{
    /** @use HasFactory<\Database\Factories\PendingActionFactory> */
    use BelongsToTenant, HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_APPLIED = 'applied';

    public const STATUS_FAILED = 'failed';

    public const STATUS_REVERTED = 'reverted';

    public const STATUS_SUPERSEDED = 'superseded';

    public const ACTION_CREATE = 'create';

    public const ACTION_UPDATE = 'update';

    public const ACTION_DELETE = 'delete';

    public const ACTION_BULK_CREATE = 'bulk_create';

    public const ACTION_REVERT = 'revert';

    protected $fillable = [
        'tenant_id',
        'user_id',
        'agent_token_id',
        'agent_slug',
        'session_id',
        'tool',
        'action',
        'target_type',
        'target_id',
        'payload',
        'preview',
        'idempotency_key',
        'status',
        'applied_diff',
        'failure_reason',
        'reviewed_by',
        'reviewed_at',
        'applied_at',
        'reverted_by',
        'reverted_at',
        'reverted_pending_action_id',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'preview' => 'array',
            'applied_diff' => 'array',
            'reviewed_at' => 'datetime',
            'applied_at' => 'datetime',
            'reverted_at' => 'datetime',
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

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function reverter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reverted_by');
    }

    public function target(): MorphTo
    {
        return $this->morphTo();
    }

    public function revertedFrom(): BelongsTo
    {
        return $this->belongsTo(PendingAction::class, 'reverted_pending_action_id');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeForTool(Builder $query, string $tool): Builder
    {
        return $query->where('tool', $tool);
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApplied(): bool
    {
        return $this->status === self::STATUS_APPLIED;
    }

    public function isRevertable(int $windowMinutes = 10): bool
    {
        if (! $this->isApplied()) {
            return false;
        }

        if ($this->applied_at === null) {
            return false;
        }

        return $this->applied_at->diffInMinutes(now()) <= $windowMinutes;
    }

    public function module(): string
    {
        return str_contains($this->tool, '.')
            ? explode('.', $this->tool, 2)[0]
            : $this->tool;
    }
}
