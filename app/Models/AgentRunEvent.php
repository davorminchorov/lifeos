<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgentRunEvent extends Model
{
    /** @use HasFactory<\Database\Factories\AgentRunEventFactory> */
    use HasFactory;

    public const TYPE_TOOL_CALL = 'tool_call';

    public const TYPE_TOOL_RESULT = 'tool_result';

    public const TYPE_TEXT = 'text';

    public const TYPE_ERROR = 'error';

    public const TYPE_SYSTEM = 'system';

    public $timestamps = false;

    protected $fillable = [
        'agent_run_id',
        'sequence',
        'type',
        'payload',
        'occurred_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'sequence' => 'integer',
            'occurred_at' => 'datetime',
        ];
    }

    public function run(): BelongsTo
    {
        return $this->belongsTo(AgentRun::class, 'agent_run_id');
    }
}
