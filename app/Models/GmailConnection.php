<?php

namespace App\Models;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class GmailConnection extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'email_address',
        'access_token',
        'refresh_token',
        'token_expires_at',
        'last_synced_at',
        'sync_enabled',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'token_expires_at' => 'datetime',
            'last_synced_at' => 'datetime',
            'sync_enabled' => 'boolean',
        ];
    }

    /**
     * Get the user that owns the Gmail connection.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the processed emails for this connection.
     */
    public function processedEmails(): HasMany
    {
        return $this->hasMany(ProcessedEmail::class, 'user_id', 'user_id');
    }

    /**
     * Interact with the access token.
     */
    protected function accessToken(): Attribute
    {
        return Attribute::make(
            get: function (?string $value) {
                if (! $value) {
                    return null;
                }

                try {
                    return Crypt::decryptString($value);
                } catch (DecryptException $e) {
                    Log::error('Failed to decrypt access token', [
                        'connection_id' => $this->id,
                        'error' => $e->getMessage(),
                    ]);

                    return null;
                }
            },
            set: fn (?string $value) => $value ? Crypt::encryptString($value) : null,
        );
    }

    /**
     * Interact with the refresh token.
     */
    protected function refreshToken(): Attribute
    {
        return Attribute::make(
            get: function (?string $value) {
                if (! $value) {
                    return null;
                }

                try {
                    return Crypt::decryptString($value);
                } catch (DecryptException $e) {
                    Log::error('Failed to decrypt refresh token', [
                        'connection_id' => $this->id,
                        'error' => $e->getMessage(),
                    ]);

                    return null;
                }
            },
            set: fn (?string $value) => $value ? Crypt::encryptString($value) : null,
        );
    }

    /**
     * Check if the access token has expired.
     */
    public function isTokenExpired(): bool
    {
        return $this->token_expires_at === null || $this->token_expires_at->isPast();
    }

    /**
     * Check if the connection is active and ready to sync.
     */
    public function isActive(): bool
    {
        return $this->sync_enabled && !empty($this->access_token) && !empty($this->refresh_token);
    }
}
