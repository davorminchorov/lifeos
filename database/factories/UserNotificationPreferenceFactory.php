<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserNotificationPreference;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserNotificationPreference>
 */
class UserNotificationPreferenceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'notification_type' => $this->faker->randomElement([
                'subscription_renewal',
                'contract_expiration',
                'warranty_expiration',
                'utility_bill_due',
                'investment_alert',
                'budget_threshold',
                'spending_pattern',
            ]),
            'email_enabled' => $this->faker->boolean(80), // 80% chance of being true
            'database_enabled' => $this->faker->boolean(90), // 90% chance of being true
            'push_enabled' => $this->faker->boolean(30), // 30% chance of being true
            'settings' => [
                'days_before' => $this->faker->randomElements([30, 14, 7, 3, 1, 0], $this->faker->numberBetween(1, 4)),
            ],
        ];
    }

    /**
     * Indicate that the notification preference is for subscription renewals.
     */
    public function subscriptionRenewal(): static
    {
        return $this->state(fn (array $attributes) => [
            'notification_type' => 'subscription_renewal',
            'settings' => [
                'days_before' => [7, 3, 1, 0],
            ],
        ]);
    }

    /**
     * Indicate that the notification preference is for contract expirations.
     */
    public function contractExpiration(): static
    {
        return $this->state(fn (array $attributes) => [
            'notification_type' => 'contract_expiration',
            'settings' => [
                'days_before' => [30, 7, 1],
            ],
        ]);
    }

    /**
     * Indicate that the notification preference is for warranty expirations.
     */
    public function warrantyExpiration(): static
    {
        return $this->state(fn (array $attributes) => [
            'notification_type' => 'warranty_expiration',
            'settings' => [
                'days_before' => [30, 7, 1],
            ],
        ]);
    }

    /**
     * Indicate that the notification preference is for utility bill due dates.
     */
    public function utilityBillDue(): static
    {
        return $this->state(fn (array $attributes) => [
            'notification_type' => 'utility_bill_due',
            'settings' => [
                'days_before' => [7, 3, 1, 0],
            ],
        ]);
    }

    /**
     * Indicate that all notification channels are enabled.
     */
    public function allChannelsEnabled(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_enabled' => true,
            'database_enabled' => true,
            'push_enabled' => true,
        ]);
    }

    /**
     * Indicate that all notification channels are disabled.
     */
    public function allChannelsDisabled(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_enabled' => false,
            'database_enabled' => false,
            'push_enabled' => false,
        ]);
    }

    /**
     * Indicate that only email notifications are enabled.
     */
    public function emailOnly(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_enabled' => true,
            'database_enabled' => false,
            'push_enabled' => false,
        ]);
    }

    /**
     * Indicate that only in-app notifications are enabled.
     */
    public function databaseOnly(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_enabled' => false,
            'database_enabled' => true,
            'push_enabled' => false,
        ]);
    }

    /**
     * Create preferences with custom notification days.
     */
    public function withDays(array $days): static
    {
        return $this->state(fn (array $attributes) => [
            'settings' => [
                'days_before' => $days,
            ],
        ]);
    }
}
