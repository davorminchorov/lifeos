<?php

namespace App\Notifications;

use App\Models\Contract;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ContractExpirationAlert extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private Contract $contract,
        private int $daysUntilExpiration,
        private bool $isNoticeAlert = false
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
        return $notifiable->getEnabledNotificationChannels('contract_expiration');
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        if ($this->isNoticeAlert) {
            return $this->buildNoticeAlert($notifiable);
        }

        return $this->buildExpirationAlert($notifiable);
    }

    /**
     * Build notice period alert email.
     */
    private function buildNoticeAlert(object $notifiable): MailMessage
    {
        $subject = $this->daysUntilExpiration === 0
            ? "🔔 Notice period deadline for {$this->contract->title} is today!"
            : "⏰ Notice period for {$this->contract->title} ends in {$this->daysUntilExpiration} days";

        $greeting = $this->daysUntilExpiration === 0
            ? 'Your contract notice period deadline is today'
            : 'Your contract notice period is ending soon';

        return (new MailMessage)
            ->subject($subject)
            ->greeting("Hello {$notifiable->name}!")
            ->line($greeting)
            ->line("**Contract:** {$this->contract->title}")
            ->when($this->contract->counterparty, function ($mail) {
                return $mail->line("**Counterparty:** {$this->contract->counterparty}");
            })
            ->line("**Contract expires:** {$this->contract->end_date->format('F j, Y')}")
            ->line("**Notice period:** {$this->contract->notice_period_days} days")
            ->line("**Notice deadline:** {$this->contract->notice_deadline->format('F j, Y')}")
            ->when($this->contract->contract_value, function ($mail) {
                return $mail->line("**Contract value:** \${$this->contract->contract_value}");
            })
            ->line('If you want to terminate this contract, you must provide notice before the deadline.')
            ->action('View Contract Details', url('/contracts/'.$this->contract->id))
            ->line('Review the contract terms and termination clauses for specific requirements.')
            ->salutation('Best regards, LifeOS Team');
    }

    /**
     * Build expiration alert email.
     */
    private function buildExpirationAlert(object $notifiable): MailMessage
    {
        $subject = $this->daysUntilExpiration === 0
            ? "🔔 {$this->contract->title} contract expires today!"
            : "⏰ {$this->contract->title} contract expires in {$this->daysUntilExpiration} days";

        $greeting = $this->daysUntilExpiration === 0
            ? 'Your contract is expiring today'
            : 'Your contract is expiring soon';

        return (new MailMessage)
            ->subject($subject)
            ->greeting("Hello {$notifiable->name}!")
            ->line($greeting)
            ->line("**Contract:** {$this->contract->title}")
            ->when($this->contract->counterparty, function ($mail) {
                return $mail->line("**Counterparty:** {$this->contract->counterparty}");
            })
            ->line("**Contract type:** {$this->contract->contract_type}")
            ->line("**Start date:** {$this->contract->start_date->format('F j, Y')}")
            ->line("**End date:** {$this->contract->end_date->format('F j, Y')}")
            ->when($this->contract->contract_value, function ($mail) {
                return $mail->line("**Contract value:** \${$this->contract->contract_value}");
            })
            ->when($this->contract->auto_renewal, function ($mail) {
                return $mail->line('This contract has auto-renewal enabled.');
            }, function ($mail) {
                return $mail->line('This contract requires manual renewal.');
            })
            ->action('View Contract Details', url('/contracts/'.$this->contract->id))
            ->line('Consider renewing or renegotiating terms if you wish to continue.')
            ->salutation('Best regards, LifeOS Team');
    }

    /**
     * Get the database representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        if ($this->isNoticeAlert) {
            $title = $this->daysUntilExpiration === 0
                ? "Notice period deadline for {$this->contract->title} is today!"
                : "Notice period for {$this->contract->title} ends in {$this->daysUntilExpiration} days";
            $message = $this->daysUntilExpiration === 0
                ? 'Your contract notice period deadline is today'
                : 'Your contract notice period is ending soon';
        } else {
            $title = $this->daysUntilExpiration === 0
                ? "{$this->contract->title} contract expires today!"
                : "{$this->contract->title} contract expires in {$this->daysUntilExpiration} days";
            $message = $this->daysUntilExpiration === 0
                ? 'Your contract is expiring today'
                : 'Your contract is expiring soon';
        }

        return [
            'title' => $title,
            'message' => $message,
            'type' => 'contract_expiration',
            'contract_id' => $this->contract->id,
            'contract_title' => $this->contract->title,
            'counterparty' => $this->contract->counterparty,
            'contract_type' => $this->contract->contract_type,
            'start_date' => $this->contract->start_date->toDateString(),
            'end_date' => $this->contract->end_date->toDateString(),
            'contract_value' => $this->contract->contract_value,
            'days_until_expiration' => $this->daysUntilExpiration,
            'is_notice_alert' => $this->isNoticeAlert,
            'notice_period_days' => $this->contract->notice_period_days,
            'action_url' => url('/contracts/'.$this->contract->id),
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
