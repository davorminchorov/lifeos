<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
        'current_tenant_id',
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
     * Get the user's contracts.
     */
    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    /**
     * Get the user's expenses.
     */
    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    /**
     * Get the user's Gmail connections.
     */
    public function gmailConnections(): HasMany
    {
        return $this->hasMany(GmailConnection::class);
    }

    /**
     * Get the user's processed emails.
     */
    public function processedEmails(): HasMany
    {
        return $this->hasMany(ProcessedEmail::class);
    }

    /**
     * Get the user's notification preferences.
     */
    public function notificationPreferences(): HasMany
    {
        return $this->hasMany(UserNotificationPreference::class);
    }

    /**
     * Get the user's project investments.
     */
    public function projectInvestments(): HasMany
    {
        return $this->hasMany(ProjectInvestment::class);
    }

    /**
     * Get the tenants owned by the user.
     */
    public function ownedTenants(): HasMany
    {
        return $this->hasMany(Tenant::class, 'owner_id');
    }

    /**
     * Get all tenants the user belongs to.
     */
    public function tenants(): BelongsToMany
    {
        return $this->belongsToMany(Tenant::class, 'tenant_members')
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Get the user's current tenant.
     */
    public function currentTenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'current_tenant_id');
    }

    /**
     * Switch to a different tenant.
     */
    public function switchTenant(Tenant $tenant): bool
    {
        if ($this->tenants()->where('tenants.id', $tenant->id)->exists()) {
            $this->current_tenant_id = $tenant->id;
            return $this->save();
        }

        return false;
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

        if (! $preference) {
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

        if (! $preference) {
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
