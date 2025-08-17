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
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $subject = $this->daysUntilRenewal === 0
            ? "🔔 {$this->subscription->service_name} renews today!"
            : "⏰ {$this->subscription->service_name} renews in {$this->daysUntilRenewal} days";

        $greeting = $this->daysUntilRenewal === 0
            ? 'Your subscription is renewing today'
            : 'Your subscription is renewing soon';

        return (new MailMessage)
            ->subject($subject)
            ->greeting("Hello {$notifiable->name}!")
            ->line($greeting)
            ->line("**Service:** {$this->subscription->service_name}")
            ->line("**Cost:** {$this->subscription->currency} {$this->subscription->cost}")
            ->line("**Next billing date:** {$this->subscription->next_billing_date->format('F j, Y')}")
            ->when($this->subscription->payment_method, function ($mail) {
                return $mail->line("**Payment method:** {$this->subscription->payment_method}");
            })
            ->when($this->subscription->auto_renewal, function ($mail) {
                return $mail->line('This subscription will automatically renew.');
            }, function ($mail) {
                return $mail->line('This subscription requires manual renewal.');
            })
            ->action('Manage Subscription', url('/subscriptions/'.$this->subscription->id))
            ->line('You can cancel or modify this subscription anytime from your dashboard.')
            ->salutation('Best regards, LifeOS Team');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'subscription_id' => $this->subscription->id,
            'service_name' => $this->subscription->service_name,
            'cost' => $this->subscription->cost,
            'currency' => $this->subscription->currency,
            'next_billing_date' => $this->subscription->next_billing_date,
            'days_until_renewal' => $this->daysUntilRenewal,
        ];
    }
}
