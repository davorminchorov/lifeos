<?php

namespace App\Models;

use App\Enums\ApplicationSource;
use App\Enums\ApplicationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class JobApplication extends Model
{
    /** @use HasFactory<\Database\Factories\JobApplicationFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'company_name',
        'company_website',
        'job_title',
        'job_description',
        'job_url',
        'location',
        'remote',
        'salary_min',
        'salary_max',
        'currency',
        'status',
        'source',
        'applied_at',
        'next_action_at',
        'priority',
        'contact_name',
        'contact_email',
        'contact_phone',
        'notes',
        'tags',
        'file_attachments',
        'archived_at',
    ];

    protected function casts(): array
    {
        return [
            'remote' => 'boolean',
            'salary_min' => 'decimal:2',
            'salary_max' => 'decimal:2',
            'applied_at' => 'date',
            'next_action_at' => 'datetime',
            'priority' => 'integer',
            'tags' => 'array',
            'file_attachments' => 'array',
            'archived_at' => 'datetime',
            'status' => ApplicationStatus::class,
            'source' => ApplicationSource::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(JobApplicationStatusHistory::class);
    }

    public function interviews(): HasMany
    {
        return $this->hasMany(JobApplicationInterview::class);
    }

    public function offer(): HasOne
    {
        return $this->hasOne(JobApplicationOffer::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->whereNull('archived_at');
    }

    public function scopeArchived($query)
    {
        return $query->whereNotNull('archived_at');
    }

    public function scopeByStatus($query, ApplicationStatus $status)
    {
        return $query->where('status', $status);
    }

    public function scopeBySource($query, ApplicationSource $source)
    {
        return $query->where('source', $source);
    }

    public function scopeByPriority($query, int $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeWithUpcomingAction($query)
    {
        return $query->whereNotNull('next_action_at')
            ->where('next_action_at', '>=', now());
    }

    public function scopeWithOverdueAction($query)
    {
        return $query->whereNotNull('next_action_at')
            ->where('next_action_at', '<', now());
    }

    public function scopeRemote($query)
    {
        return $query->where('remote', true);
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('company_name', 'like', "%{$search}%")
                ->orWhere('job_title', 'like', "%{$search}%")
                ->orWhere('location', 'like', "%{$search}%");
        });
    }

    // Accessors
    public function getDaysInCurrentStatusAttribute(): int
    {
        $latestHistory = $this->statusHistories()
            ->where('to_status', $this->status)
            ->latest('changed_at')
            ->first();

        if ($latestHistory) {
            return now()->diffInDays($latestHistory->changed_at);
        }

        return now()->diffInDays($this->created_at);
    }

    public function getDaysSinceAppliedAttribute(): ?int
    {
        if (! $this->applied_at) {
            return null;
        }

        return now()->diffInDays($this->applied_at);
    }

    public function getIsArchivedAttribute(): bool
    {
        return $this->archived_at !== null;
    }

    public function getFormattedSalaryRangeAttribute(): ?string
    {
        if (! $this->salary_min && ! $this->salary_max) {
            return null;
        }

        $currencyService = app(\App\Services\CurrencyService::class);

        if ($this->salary_min && $this->salary_max) {
            return $currencyService->format($this->salary_min, $this->currency).' - '.
                   $currencyService->format($this->salary_max, $this->currency);
        }

        if ($this->salary_min) {
            return $currencyService->format($this->salary_min, $this->currency).'+';
        }

        return 'Up to '.$currencyService->format($this->salary_max, $this->currency);
    }
}
