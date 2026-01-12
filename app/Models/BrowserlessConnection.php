<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BrowserlessConnection extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'portal_name',
        'last_synced_at',
        'last_successful_sync_at',
        'sync_enabled',
        'last_error',
        'consecutive_failures',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'last_synced_at' => 'datetime',
            'last_successful_sync_at' => 'datetime',
            'sync_enabled' => 'boolean',
            'consecutive_failures' => 'integer',
        ];
    }

    /**
     * Get the user that owns the browserless connection.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the investor data for this connection.
     */
    public function investorData(): HasMany
    {
        return $this->hasMany(InvestorData::class);
    }

    /**
     * Check if the connection is active and ready to sync.
     */
    public function isActive(): bool
    {
        return $this->sync_enabled;
    }

    /**
     * Mark the sync as successful.
     */
    public function markSyncSuccessful(): void
    {
        $this->update([
            'last_synced_at' => now(),
            'last_successful_sync_at' => now(),
            'last_error' => null,
            'consecutive_failures' => 0,
        ]);
    }

    /**
     * Mark the sync as failed.
     */
    public function markSyncFailed(string $error): void
    {
        $this->update([
            'last_synced_at' => now(),
            'last_error' => $error,
            'consecutive_failures' => $this->consecutive_failures + 1,
        ]);

        // Disable sync if too many consecutive failures
        if ($this->consecutive_failures >= 5) {
            $this->update(['sync_enabled' => false]);
        }
    }

    /**
     * Check if the connection has recent failures.
     */
    public function hasRecentFailures(): bool
    {
        return $this->consecutive_failures > 0;
    }
}
