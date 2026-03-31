<?php

namespace Tests\Feature\Listeners;

use App\Events\BudgetThresholdCrossed;
use App\Listeners\SendBudgetThresholdNotification;
use App\Models\Budget;
use App\Models\UserNotificationPreference;
use App\Notifications\BudgetThresholdAlert;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SendBudgetThresholdNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected $tenant;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        Cache::flush();
        ['user' => $this->user, 'tenant' => $this->tenant] = $this->setupTenantContext();
    }

    #[Test]
    public function it_sends_budget_threshold_notification_to_user(): void
    {
        $budget = Budget::factory()->active()->monthly()->create([
            'user_id' => $this->user->id,
            'tenant_id' => $this->tenant->id,
        ]);

        $event = new BudgetThresholdCrossed($budget, 'up');
        $listener = new SendBudgetThresholdNotification;
        $listener->handle($event);

        Notification::assertSentTo($this->user, BudgetThresholdAlert::class, function ($notification) use ($budget) {
            return $notification->budget->id === $budget->id && $notification->direction === 'up';
        });
    }

    #[Test]
    public function it_skips_notification_when_all_channels_disabled(): void
    {
        $this->user->createDefaultNotificationPreferences();

        $preference = $this->user->getNotificationPreference('budget_threshold');
        $preference->email_enabled = false;
        $preference->database_enabled = false;
        $preference->push_enabled = false;
        $preference->save();

        $budget = Budget::factory()->active()->monthly()->create([
            'user_id' => $this->user->id,
            'tenant_id' => $this->tenant->id,
        ]);

        $event = new BudgetThresholdCrossed($budget, 'up');
        $listener = new SendBudgetThresholdNotification;
        $listener->handle($event);

        Notification::assertNothingSent();
    }

    #[Test]
    public function it_deduplicates_notifications_for_same_budget_and_direction(): void
    {
        $budget = Budget::factory()->active()->monthly()->create([
            'user_id' => $this->user->id,
            'tenant_id' => $this->tenant->id,
        ]);

        $event = new BudgetThresholdCrossed($budget, 'up');
        $listener = new SendBudgetThresholdNotification;

        // First call should send
        $listener->handle($event);

        // Second call should be deduplicated
        $listener->handle($event);

        Notification::assertSentToTimes($this->user, BudgetThresholdAlert::class, 1);
    }

    #[Test]
    public function it_sends_separate_notifications_for_different_directions(): void
    {
        $budget = Budget::factory()->active()->monthly()->create([
            'user_id' => $this->user->id,
            'tenant_id' => $this->tenant->id,
        ]);

        $listener = new SendBudgetThresholdNotification;

        $listener->handle(new BudgetThresholdCrossed($budget, 'up'));
        $listener->handle(new BudgetThresholdCrossed($budget, 'down'));

        Notification::assertSentToTimes($this->user, BudgetThresholdAlert::class, 2);
    }

    #[Test]
    public function it_respects_user_email_preference_for_channels(): void
    {
        $this->user->createDefaultNotificationPreferences();

        $preference = $this->user->getNotificationPreference('budget_threshold');
        $preference->email_enabled = true;
        $preference->database_enabled = true;
        $preference->save();

        $budget = Budget::factory()->active()->monthly()->create([
            'user_id' => $this->user->id,
            'tenant_id' => $this->tenant->id,
        ]);

        $event = new BudgetThresholdCrossed($budget, 'up');
        $listener = new SendBudgetThresholdNotification;
        $listener->handle($event);

        Notification::assertSentTo($this->user, BudgetThresholdAlert::class);

        // Verify that the notification via() method returns channels including mail
        $channels = $this->user->getEnabledNotificationChannels('budget_threshold');
        $this->assertContains('mail', $channels);
        $this->assertContains('database', $channels);
    }
}
