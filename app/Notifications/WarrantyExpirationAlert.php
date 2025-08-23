<?php

namespace App\Notifications;

use App\Models\Warranty;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WarrantyExpirationAlert extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private Warranty $warranty,
        private int $daysUntilExpiration
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
        return $notifiable->getEnabledNotificationChannels('warranty_expiration');
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $subject = $this->daysUntilExpiration === 0
            ? "🔔 {$this->warranty->product_name} warranty expires today!"
            : "⏰ {$this->warranty->product_name} warranty expires in {$this->daysUntilExpiration} days";

        return (new MailMessage)
            ->subject($subject)
            ->view('emails.notifications.warranty-expiration-alert', [
                'user' => $notifiable,
                'warranty' => $this->warranty,
                'daysUntilExpiration' => $this->daysUntilExpiration,
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
        $title = $this->daysUntilExpiration === 0
            ? "{$this->warranty->product_name} warranty expires today!"
            : "{$this->warranty->product_name} warranty expires in {$this->daysUntilExpiration} days";

        $message = $this->daysUntilExpiration === 0
            ? 'Your warranty is expiring today'
            : 'Your warranty is expiring soon';

        return [
            'title' => $title,
            'message' => $message,
            'type' => 'warranty_expiration',
            'warranty_id' => $this->warranty->id,
            'product_name' => $this->warranty->product_name,
            'brand' => $this->warranty->brand,
            'model' => $this->warranty->model,
            'purchase_date' => $this->warranty->purchase_date->toDateString(),
            'warranty_expiration_date' => $this->warranty->warranty_expiration_date->toDateString(),
            'days_until_expiration' => $this->daysUntilExpiration,
            'warranty_type' => $this->warranty->warranty_type,
            'retailer' => $this->warranty->retailer,
            'action_url' => url('/warranties/'.$this->warranty->id),
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
