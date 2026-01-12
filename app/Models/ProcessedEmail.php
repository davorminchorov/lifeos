<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProcessedEmail extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
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
        $query->where('processing_status', 'pending');
    }

    /**
     * Scope a query to only include processed emails.
     */
    public function scopeProcessed(Builder $query): void
    {
        $query->where('processing_status', 'processed');
    }

    /**
     * Scope a query to only include failed emails.
     */
    public function scopeFailed(Builder $query): void
    {
        $query->where('processing_status', 'failed');
    }

    /**
     * Scope a query to only include skipped emails.
     */
    public function scopeSkipped(Builder $query): void
    {
        $query->where('processing_status', 'skipped');
    }

    /**
     * Mark this email as processed.
     */
    public function markAsProcessed(int $expenseId): void
    {
        $this->update([
            'expense_id' => $expenseId,
            'processing_status' => 'processed',
            'processed_at' => now(),
        ]);
    }

    /**
     * Mark this email as failed.
     */
    public function markAsFailed(string $reason): void
    {
        $this->update([
            'processing_status' => 'failed',
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
            'processing_status' => 'skipped',
            'failure_reason' => $reason,
            'processed_at' => now(),
        ]);
    }
}
