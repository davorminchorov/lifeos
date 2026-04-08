<?php

namespace Tests\Unit\Models;

use App\Models\UserNotificationPreference;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserNotificationPreferenceTest extends TestCase
{
    #[Test]
    public function default_preferences_include_job_application_reminder(): void
    {
        $defaults = UserNotificationPreference::getDefaultPreferences();

        $this->assertArrayHasKey('job_application_reminder', $defaults);
        $this->assertTrue($defaults['job_application_reminder']['email_enabled']);
        $this->assertTrue($defaults['job_application_reminder']['database_enabled']);
        $this->assertFalse($defaults['job_application_reminder']['push_enabled']);
    }

    #[Test]
    public function default_preferences_include_all_notification_types(): void
    {
        $defaults = UserNotificationPreference::getDefaultPreferences();

        $expectedTypes = [
            'subscription_renewal',
            'contract_expiration',
            'warranty_expiration',
            'utility_bill_due',
            'job_application_reminder',
            'investment_alert',
            'budget_threshold',
            'spending_pattern',
        ];

        foreach ($expectedTypes as $type) {
            $this->assertArrayHasKey($type, $defaults, "Missing default preference for '{$type}'");
        }
    }
}
