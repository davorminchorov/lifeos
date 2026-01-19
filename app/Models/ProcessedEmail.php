<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProcessedEmail extends Model
{
    use BelongsToTenant;

    /**
     * Processing status constants.
     */
    public const STATUS_PENDING = 'pending';

    public const STATUS_PROCESSED = 'processed';

    public const STATUS_FAILED = 'failed';

    public const STATUS_SKIPPED = 'skipped';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tenant_id',
        'user_id',
        'gmail_message_id',
        'expense_id',
        'processed_at',
        'processing_status',
        'failure_reason',
        'email_data',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'processed_at' => 'datetime',
            'email_data' => 'array',
        ];
    }

    /**
     * Get the user that owns the processed email.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the expense associated with this processed email.
     */
    public function expense(): BelongsTo
    {
        return $this->belongsTo(Expense::class);
    }

    /**
     * Scope a query to only include pending emails.
     */
    public function scopePending(Builder $query): void
    {
        $query->where('processing_status', self::STATUS_PENDING);
    }

    /**
     * Scope a query to only include processed emails.
     */
    public function scopeProcessed(Builder $query): void
    {
        $query->where('processing_status', self::STATUS_PROCESSED);
    }

    /**
     * Scope a query to only include failed emails.
     */
    public function scopeFailed(Builder $query): void
    {
        $query->where('processing_status', self::STATUS_FAILED);
    }

    /**
     * Scope a query to only include skipped emails.
     */
    public function scopeSkipped(Builder $query): void
    {
        $query->where('processing_status', self::STATUS_SKIPPED);
    }

    /**
     * Mark this email as processed.
     */
    public function markAsProcessed(int $expenseId): void
    {
        $this->update([
            'expense_id' => $expenseId,
            'processing_status' => self::STATUS_PROCESSED,
            'processed_at' => now(),
            'failure_reason' => null,
        ]);
    }

    /**
     * Mark this email as failed.
     */
    public function markAsFailed(string $reason): void
    {
        $this->update([
            'processing_status' => self::STATUS_FAILED,
            'failure_reason' => $reason,
            'processed_at' => now(),
        ]);
    }

    /**
     * Mark this email as skipped.
     */
    public function markAsSkipped(string $reason): void
    {
        $this->update([
            'processing_status' => self::STATUS_SKIPPED,
            'failure_reason' => $reason,
            'processed_at' => now(),
        ]);
    }
}
