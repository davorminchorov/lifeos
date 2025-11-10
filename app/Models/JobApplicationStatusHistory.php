<?php

namespace App\Models;

use App\Enums\ApplicationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobApplicationStatusHistory extends Model
{
    /** @use HasFactory<\Database\Factories\JobApplicationStatusHistoryFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'job_application_id',
        'from_status',
        'to_status',
        'changed_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'from_status' => ApplicationStatus::class,
            'to_status' => ApplicationStatus::class,
            'changed_at' => 'datetime',
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

    public function scopeRecent($query, int $limit = 10)
    {
        return $query->latest('changed_at')->limit($limit);
    }

    public function scopeByStatus($query, ApplicationStatus $status)
    {
        return $query->where('to_status', $status);
    }
}
