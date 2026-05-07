<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankLine extends Model
{
    /** @use HasFactory<\Database\Factories\BankLineFactory> */
    use BelongsToTenant, HasFactory;

    public const STATUS_UNMATCHED = 'unmatched';

    public const STATUS_MATCHED = 'matched';

    public const STATUS_MATCHED_PENDING = 'matched_pending';

    public const STATUS_IGNORED = 'ignored';

    protected $fillable = [
        'tenant_id',
        'user_id',
        'created_by_agent_token_id',
        'account',
        'posted_at',
        'amount_cents',
        'currency',
        'merchant_raw',
        'description',
        'balance_after_cents',
        'statement_id',
        'statement_row',
        'fingerprint',
        'matched_expense_id',
        'matched_pending_action_id',
        'match_status',
        'match_confidence',
        'match_candidates',
        'source',
    ];

    protected function casts(): array
    {
        return [
            'posted_at' => 'date',
            'amount_cents' => 'integer',
            'balance_after_cents' => 'integer',
            'statement_row' => 'integer',
            'match_confidence' => 'decimal:2',
            'match_candidates' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function matchedExpense(): BelongsTo
    {
        return $this->belongsTo(Expense::class, 'matched_expense_id');
    }

    public function matchedPendingAction(): BelongsTo
    {
        return $this->belongsTo(PendingAction::class, 'matched_pending_action_id');
    }

    public function scopeUnmatched(Builder $query): Builder
    {
        return $query->where('match_status', self::STATUS_UNMATCHED);
    }

    public function scopeMatched(Builder $query): Builder
    {
        return $query->whereIn('match_status', [self::STATUS_MATCHED, self::STATUS_MATCHED_PENDING]);
    }

    public function isDebit(): bool
    {
        return $this->amount_cents < 0;
    }

    public function isCredit(): bool
    {
        return $this->amount_cents > 0;
    }
}
