<?php

namespace App\Models;

use App\Enums\InterviewOutcome;
use App\Enums\InterviewType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class JobApplicationInterview extends Model
{
    /** @use HasFactory<\Database\Factories\JobApplicationInterviewFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'job_application_id',
        'type',
        'scheduled_at',
        'duration_minutes',
        'location',
        'video_link',
        'interviewer_name',
        'notes',
        'feedback',
        'outcome',
        'completed',
    ];

    protected function casts(): array
    {
        return [
            'type' => InterviewType::class,
            'scheduled_at' => 'datetime',
            'duration_minutes' => 'integer',
            'outcome' => InterviewOutcome::class,
            'completed' => 'boolean',
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
    public function scopeForApplication($query, int $jobApplicationId)
    {
        return $query->where('job_application_id', $jobApplicationId);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('scheduled_at', '>=', now())
            ->where('completed', false)
            ->orderBy('scheduled_at');
    }

    public function scopePast($query)
    {
        return $query->where('scheduled_at', '<', now())
            ->orWhere('completed', true)
            ->orderBy('scheduled_at', 'desc');
    }

    public function scopeCompleted($query)
    {
        return $query->where('completed', true);
    }

    public function scopePending($query)
    {
        return $query->where('completed', false);
    }

    public function scopeByType($query, InterviewType $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByOutcome($query, InterviewOutcome $outcome)
    {
        return $query->where('outcome', $outcome);
    }

    // Accessors
    public function getIsPastAttribute(): bool
    {
        return $this->scheduled_at->isPast();
    }

    public function getIsUpcomingAttribute(): bool
    {
        return $this->scheduled_at->isFuture() && ! $this->completed;
    }

    public function getIsTodayAttribute(): bool
    {
        return $this->scheduled_at->isToday();
    }

    public function getIsWithin24HoursAttribute(): bool
    {
        return $this->scheduled_at->isFuture() &&
               $this->scheduled_at->diffInHours(now()) <= 24;
    }
}
