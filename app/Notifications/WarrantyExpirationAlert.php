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
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $subject = $this->daysUntilExpiration === 0
            ? "🔔 {$this->warranty->product_name} warranty expires today!"
            : "⏰ {$this->warranty->product_name} warranty expires in {$this->daysUntilExpiration} days";

        $greeting = $this->daysUntilExpiration === 0
            ? 'Your warranty is expiring today'
            : 'Your warranty is expiring soon';

        return (new MailMessage)
            ->subject($subject)
            ->greeting("Hello {$notifiable->name}!")
            ->line($greeting)
            ->line("**Product:** {$this->warranty->product_name}")
            ->when($this->warranty->brand, function ($mail) {
                return $mail->line("**Brand:** {$this->warranty->brand}");
            })
            ->when($this->warranty->model, function ($mail) {
                return $mail->line("**Model:** {$this->warranty->model}");
            })
            ->line("**Purchase Date:** {$this->warranty->purchase_date->format('F j, Y')}")
            ->line("**Warranty Expires:** {$this->warranty->warranty_expiration_date->format('F j, Y')}")
            ->when($this->warranty->warranty_type, function ($mail) {
                return $mail->line("**Warranty Type:** {$this->warranty->warranty_type}");
            })
            ->when($this->warranty->retailer, function ($mail) {
                return $mail->line("**Purchased from:** {$this->warranty->retailer}");
            })
            ->action('View Warranty Details', url('/warranties/'.$this->warranty->id))
            ->line('Consider renewing or extending your warranty if available.')
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
            'warranty_id' => $this->warranty->id,
            'product_name' => $this->warranty->product_name,
            'brand' => $this->warranty->brand,
            'model' => $this->warranty->model,
            'purchase_date' => $this->warranty->purchase_date,
            'warranty_expiration_date' => $this->warranty->warranty_expiration_date,
            'days_until_expiration' => $this->daysUntilExpiration,
        ];
    }
}
