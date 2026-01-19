<?php

namespace App\Models;

use App\Enums\OfferStatus;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobApplicationOffer extends Model
{
    /** @use HasFactory<\Database\Factories\JobApplicationOfferFactory> */
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'job_application_id',
        'base_salary',
        'bonus',
        'equity',
        'currency',
        'benefits',
        'start_date',
        'decision_deadline',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'base_salary' => 'decimal:2',
            'bonus' => 'decimal:2',
            'start_date' => 'date',
            'decision_deadline' => 'date',
            'status' => OfferStatus::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function jobApplication(): BelongsTo
    {
        return $this->belongsTo(JobApplication::class);
    }

    // Scopes
    public function scopeByStatus($query, OfferStatus $status)
    {
        return $query->where('status', $status);
    }

    public function scopePending($query)
    {
        return $query->where('status', OfferStatus::PENDING);
    }

    public function scopeAccepted($query)
    {
        return $query->where('status', OfferStatus::ACCEPTED);
    }

    public function scopeWithUpcomingDeadline($query, int $days = 7)
    {
        return $query->whereNotNull('decision_deadline')
            ->where('decision_deadline', '>=', now())
            ->where('decision_deadline', '<=', now()->addDays($days));
    }

    // Accessors
    public function getTotalCompensationAttribute(): float
    {
        return (float) $this->base_salary + (float) ($this->bonus ?? 0);
    }

    public function getFormattedBaseSalaryAttribute(): string
    {
        $currencyService = app(\App\Services\CurrencyService::class);

        return $currencyService->format($this->base_salary, $this->currency);
    }

    public function getFormattedTotalCompensationAttribute(): string
    {
        $currencyService = app(\App\Services\CurrencyService::class);

        return $currencyService->format($this->total_compensation, $this->currency);
    }

    public function getDaysUntilDeadlineAttribute(): ?int
    {
        if (! $this->decision_deadline) {
            return null;
        }

        return now()->diffInDays($this->decision_deadline, false);
    }

    public function getIsDeadlineApproachingAttribute(): bool
    {
        if (! $this->decision_deadline) {
            return false;
        }

        $daysUntil = $this->days_until_deadline;

        return $daysUntil !== null && $daysUntil >= 0 && $daysUntil <= 3;
    }

    public function getIsExpiredAttribute(): bool
    {
        if (! $this->decision_deadline) {
            return false;
        }

        return $this->decision_deadline->isPast();
    }
}
