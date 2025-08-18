<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the user's subscriptions.
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Get the user's notification preferences.
     */
    public function notificationPreferences(): HasMany
    {
        return $this->hasMany(UserNotificationPreference::class);
    }

    /**
     * Get notification preference for a specific type.
     */
    public function getNotificationPreference(string $type): ?UserNotificationPreference
    {
        return $this->notificationPreferences()->where('notification_type', $type)->first();
    }

    /**
     * Get enabled notification channels for a specific notification type.
     */
    public function getEnabledNotificationChannels(string $type): array
    {
        $preference = $this->getNotificationPreference($type);

        if (!$preference) {
            // Return default channels if no preference exists
            $defaults = UserNotificationPreference::getDefaultPreferences();
            $defaultPref = $defaults[$type] ?? [];

            $channels = [];
            if ($defaultPref['email_enabled'] ?? true) {
                $channels[] = 'mail';
            }
            if ($defaultPref['database_enabled'] ?? true) {
                $channels[] = 'database';
            }
            if ($defaultPref['push_enabled'] ?? false) {
                $channels[] = 'broadcast';
            }

            return $channels;
        }

        return $preference->getEnabledChannels();
    }

    /**
     * Get notification days for a specific notification type.
     */
    public function getNotificationDays(string $type): array
    {
        $preference = $this->getNotificationPreference($type);

        if (!$preference) {
            // Return default days if no preference exists
            $defaults = UserNotificationPreference::getDefaultPreferences();
            return $defaults[$type]['settings']['days_before'] ?? [7, 3, 1, 0];
        }

        return $preference->getNotificationDays();
    }

    /**
     * Create default notification preferences for the user.
     */
    public function createDefaultNotificationPreferences(): void
    {
        $defaults = UserNotificationPreference::getDefaultPreferences();

        foreach ($defaults as $type => $settings) {
            $this->notificationPreferences()->updateOrCreate(
                ['notification_type' => $type],
                $settings
            );
        }
    }
}
