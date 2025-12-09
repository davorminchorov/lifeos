<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queueing\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DailyMenuNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /** @var array<string,mixed> */
    protected array $payload;

    /**
     * @param array<string,mixed> $payload
     */
    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }

    public function via(object $notifiable): array
    {
        // In-app (database) by default; mail optional if configured later
        return ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        // Optional mail channel; not used by default
        $message = (new MailMessage())
            ->subject('Today\'s Cycle Menu')
            ->greeting('Good morning!')
            ->line('Here\'s what\'s on your menu today:');

        foreach (($this->payload['items'] ?? []) as $i) {
            $message->line('- '.$i['display']);
        }

        if (! empty($this->payload['url'])) {
            $message->action('View today\'s menu', $this->payload['url']);
        }

        return $message;
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(object $notifiable): array
    {
        return $this->payload;
    }
}
