<?php

namespace App\Notifications;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriptionRenewalAlert extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private Subscription $subscription,
        private int $daysUntilRenewal
    ) {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return $notifiable->getEnabledNotificationChannels('subscription_renewal');
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $subject = $this->daysUntilRenewal === 0
            ? "🔔 {$this->subscription->service_name} renews today!"
            : "⏰ {$this->subscription->service_name} renews in {$this->daysUntilRenewal} days";

        return (new MailMessage)
            ->subject($subject)
            ->view('emails.notifications.subscription-renewal-alert', [
                'user' => $notifiable,
                'subscription' => $this->subscription,
                'daysUntilRenewal' => $this->daysUntilRenewal,
                'subject' => $subject,
            ]);
    }

    /**
     * Get the database representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => $this->daysUntilRenewal === 0
                ? "{$this->subscription->service_name} renews today!"
                : "{$this->subscription->service_name} renews in {$this->daysUntilRenewal} days",
            'message' => $this->daysUntilRenewal === 0
                ? 'Your subscription is renewing today'
                : 'Your subscription is renewing soon',
            'type' => 'subscription_renewal',
            'subscription_id' => $this->subscription->id,
            'service_name' => $this->subscription->service_name,
            'cost' => $this->subscription->cost,
            'currency' => $this->subscription->currency,
            'next_billing_date' => $this->subscription->next_billing_date->toDateString(),
            'days_until_renewal' => $this->daysUntilRenewal,
            'action_url' => url('/subscriptions/'.$this->subscription->id),
        ];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
