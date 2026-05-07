<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DigestLog extends Model
{
    /** @use HasFactory<\Database\Factories\DigestLogFactory> */
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'agent_run_id',
        'pending_action_id',
        'week_starts_on',
        'recipient_email',
        'subject',
        'body_text',
        'body_html',
        'structured_summary',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'week_starts_on' => 'date',
            'structured_summary' => 'array',
            'sent_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function agentRun(): BelongsTo
    {
        return $this->belongsTo(AgentRun::class);
    }

    public function pendingAction(): BelongsTo
    {
        return $this->belongsTo(PendingAction::class);
    }
}
