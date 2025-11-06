<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserNotificationPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'notification_type',
        'email_enabled',
        'database_enabled',
        'push_enabled',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'email_enabled' => 'boolean',
            'database_enabled' => 'boolean',
            'push_enabled' => 'boolean',
            'settings' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the notification days for this preference.
     */
    public function getNotificationDays(): array
    {
        return $this->settings['days_before'] ?? [7, 3, 1, 0];
    }

    /**
     * Set the notification days for this preference.
     */
    public function setNotificationDays(array $days): void
    {
        $settings = $this->settings ?? [];
        $settings['days_before'] = $days;
        $this->settings = $settings;
    }

    /**
     * Check if a specific notification channel is enabled.
     */
    public function isChannelEnabled(string $channel): bool
    {
        return match ($channel) {
            'mail' => $this->email_enabled,
            'database' => $this->database_enabled,
            'broadcast' => $this->push_enabled,
            default => false,
        };
    }

    /**
     * Get enabled notification channels for this preference.
     */
    public function getEnabledChannels(): array
    {
        $channels = [];

        if ($this->email_enabled) {
            $channels[] = 'mail';
        }

        if ($this->database_enabled) {
            $channels[] = 'database';
        }

        if ($this->push_enabled) {
            $channels[] = 'broadcast';
        }

        return $channels;
    }

    /**
     * Get default preferences for all notification types.
     */
    public static function getDefaultPreferences(): array
    {
        return [
            'subscription_renewal' => [
                'email_enabled' => true,
                'database_enabled' => true,
                'push_enabled' => false,
                'settings' => ['days_before' => [7, 3, 1, 0]],
            ],
            'contract_expiration' => [
                'email_enabled' => true,
                'database_enabled' => true,
                'push_enabled' => false,
                'settings' => ['days_before' => [30, 7, 1, 0]],
            ],
            'warranty_expiration' => [
                'email_enabled' => true,
                'database_enabled' => true,
                'push_enabled' => false,
                'settings' => ['days_before' => [30, 7, 1, 0]],
            ],
            'utility_bill_due' => [
                'email_enabled' => true,
                'database_enabled' => true,
                'push_enabled' => false,
                'settings' => ['days_before' => [7, 3, 1, 0]],
            ],
            'investment_alert' => [
                'email_enabled' => false,
                'database_enabled' => true,
                'push_enabled' => false,
                'settings' => [],
            ],
            'budget_threshold' => [
                'email_enabled' => false,
                'database_enabled' => true,
                'push_enabled' => false,
                'settings' => [],
            ],
            'spending_pattern' => [
                'email_enabled' => false,
                'database_enabled' => true,
                'push_enabled' => false,
                'settings' => [],
            ],
        ];
    }
}
