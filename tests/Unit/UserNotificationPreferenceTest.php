<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\UserNotificationPreference;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserNotificationPreferenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_belongs_to_user()
    {
        $user = User::factory()->create();
        $preference = UserNotificationPreference::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $preference->user);
        $this->assertEquals($user->id, $preference->user->id);
    }

    public function test_can_get_notification_days()
    {
        $preference = UserNotificationPreference::factory()->create([
            'settings' => ['days_before' => [30, 7, 1]]
        ]);

        $this->assertEquals([30, 7, 1], $preference->getNotificationDays());
    }

    public function test_returns_default_notification_days_when_not_set()
    {
        $preference = UserNotificationPreference::factory()->create([
            'settings' => null
        ]);

        $this->assertEquals([7, 3, 1, 0], $preference->getNotificationDays());
    }

    public function test_can_set_notification_days()
    {
        $preference = UserNotificationPreference::factory()->create();
        $preference->setNotificationDays([14, 7, 3]);

        $this->assertEquals([14, 7, 3], $preference->getNotificationDays());
        $this->assertEquals(['days_before' => [14, 7, 3]], $preference->settings);
    }

    public function test_can_check_if_channel_is_enabled()
    {
        $preference = UserNotificationPreference::factory()->create([
            'email_enabled' => true,
            'database_enabled' => false,
            'push_enabled' => true
        ]);

        $this->assertTrue($preference->isChannelEnabled('mail'));
        $this->assertFalse($preference->isChannelEnabled('database'));
        $this->assertTrue($preference->isChannelEnabled('broadcast'));
        $this->assertFalse($preference->isChannelEnabled('unknown'));
    }

    public function test_can_get_enabled_channels()
    {
        $preference = UserNotificationPreference::factory()->create([
            'email_enabled' => true,
            'database_enabled' => false,
            'push_enabled' => true
        ]);

        $enabledChannels = $preference->getEnabledChannels();
        $this->assertContains('mail', $enabledChannels);
        $this->assertContains('broadcast', $enabledChannels);
        $this->assertNotContains('database', $enabledChannels);
    }

    public function test_can_get_default_preferences()
    {
        $defaultPreferences = UserNotificationPreference::getDefaultPreferences();

        $this->assertIsArray($defaultPreferences);
        $this->assertArrayHasKey('subscription_renewal', $defaultPreferences);
        $this->assertArrayHasKey('contract_expiration', $defaultPreferences);
        $this->assertArrayHasKey('warranty_expiration', $defaultPreferences);
        $this->assertArrayHasKey('utility_bill_due', $defaultPreferences);
        $this->assertArrayHasKey('investment_alert', $defaultPreferences);
        $this->assertArrayHasKey('budget_threshold', $defaultPreferences);
        $this->assertArrayHasKey('spending_pattern', $defaultPreferences);

        // Check subscription_renewal defaults
        $subscriptionDefaults = $defaultPreferences['subscription_renewal'];
        $this->assertTrue($subscriptionDefaults['email_enabled']);
        $this->assertTrue($subscriptionDefaults['database_enabled']);
        $this->assertFalse($subscriptionDefaults['push_enabled']);
        $this->assertEquals([7, 3, 1, 0], $subscriptionDefaults['settings']['days_before']);
    }

    public function test_fillable_attributes()
    {
        $preference = new UserNotificationPreference();
        $fillable = $preference->getFillable();

        $expectedFillable = [
            'user_id',
            'notification_type',
            'email_enabled',
            'database_enabled',
            'push_enabled',
            'settings',
        ];

        $this->assertEquals($expectedFillable, $fillable);
    }

    public function test_casts_boolean_attributes()
    {
        $preference = UserNotificationPreference::factory()->create([
            'email_enabled' => '1',
            'database_enabled' => '0',
            'push_enabled' => '1',
            'settings' => '{"days_before": [7, 3, 1]}'
        ]);

        $this->assertTrue($preference->email_enabled);
        $this->assertFalse($preference->database_enabled);
        $this->assertTrue($preference->push_enabled);
        $this->assertIsArray($preference->settings);
        $this->assertEquals([7, 3, 1], $preference->settings['days_before']);
    }

    public function test_unique_constraint_on_user_and_notification_type()
    {
        $user = User::factory()->create();

        UserNotificationPreference::factory()->create([
            'user_id' => $user->id,
            'notification_type' => 'subscription_renewal'
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        // This should fail due to unique constraint
        UserNotificationPreference::factory()->create([
            'user_id' => $user->id,
            'notification_type' => 'subscription_renewal'
        ]);
    }

    public function test_preserves_existing_settings_when_updating_days()
    {
        $preference = UserNotificationPreference::factory()->create([
            'settings' => [
                'days_before' => [7, 3, 1],
                'custom_setting' => 'value'
            ]
        ]);

        $preference->setNotificationDays([14, 7]);

        $this->assertEquals([14, 7], $preference->getNotificationDays());
        $this->assertEquals('value', $preference->settings['custom_setting']);
    }

    public function test_handles_null_settings_when_setting_days()
    {
        $preference = UserNotificationPreference::factory()->create([
            'settings' => null
        ]);

        $preference->setNotificationDays([30, 14, 7]);

        $this->assertEquals([30, 14, 7], $preference->getNotificationDays());
        $this->assertEquals(['days_before' => [30, 14, 7]], $preference->settings);
    }
}
