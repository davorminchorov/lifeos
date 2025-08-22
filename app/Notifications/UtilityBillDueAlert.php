<?php

namespace App\Notifications;

use App\Models\UtilityBill;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UtilityBillDueAlert extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private UtilityBill $bill,
        private int $daysTillDue
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
        return $notifiable->getEnabledNotificationChannels('utility_bill_due');
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $subject = $this->daysTillDue === 0
            ? "🔔 {$this->bill->utility_type} bill payment is due today!"
            : "⏰ {$this->bill->utility_type} bill payment due in {$this->daysTillDue} days";

        $greeting = $this->daysTillDue === 0
            ? 'Your utility bill payment is due today'
            : 'Your utility bill payment is due soon';

        return (new MailMessage)
            ->subject($subject)
            ->greeting("Hello {$notifiable->name}!")
            ->line($greeting)
            ->line("**Utility Type:** {$this->bill->utility_type}")
            ->when($this->bill->service_provider, function ($mail) {
                return $mail->line("**Service Provider:** {$this->bill->service_provider}");
            })
            ->line("**Bill Amount:** \${$this->bill->bill_amount}")
            ->line("**Due Date:** {$this->bill->due_date->format('F j, Y')}")
            ->line("**Billing Period:** {$this->bill->bill_period_start->format('M j')} - {$this->bill->bill_period_end->format('M j, Y')}")
            ->when($this->bill->account_number, function ($mail) {
                return $mail->line("**Account Number:** {$this->bill->account_number}");
            })
            ->when($this->bill->usage_amount, function ($mail) {
                return $mail->line("**Usage:** {$this->bill->usage_amount} {$this->bill->usage_unit}");
            })
            ->when($this->bill->auto_pay_enabled, function ($mail) {
                return $mail->line('This bill has auto-pay enabled and should be processed automatically.');
            }, function ($mail) {
                return $mail->line('This bill requires manual payment.');
            })
            ->when($this->bill->is_over_budget, function ($mail) {
                return $mail->line("⚠️ **Alert:** This bill exceeds your budget threshold of \${$this->bill->budget_alert_threshold}");
            })
            ->action('View Bill Details', url('/utility-bills/'.$this->bill->id))
            ->line('Avoid late fees by paying before the due date.')
            ->salutation('Best regards, LifeOS Team');
    }

    /**
     * Get the database representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        $title = $this->daysTillDue === 0
            ? "{$this->bill->utility_type} bill payment is due today!"
            : "{$this->bill->utility_type} bill payment due in {$this->daysTillDue} days";

        $message = $this->daysTillDue === 0
            ? 'Your utility bill payment is due today'
            : 'Your utility bill payment is due soon';

        return [
            'title' => $title,
            'message' => $message,
            'type' => 'utility_bill_due',
            'bill_id' => $this->bill->id,
            'utility_type' => $this->bill->utility_type,
            'service_provider' => $this->bill->service_provider,
            'bill_amount' => $this->bill->bill_amount,
            'due_date' => $this->bill->due_date->toDateString(),
            'account_number' => $this->bill->account_number,
            'usage_amount' => $this->bill->usage_amount,
            'usage_unit' => $this->bill->usage_unit,
            'days_till_due' => $this->daysTillDue,
            'auto_pay_enabled' => $this->bill->auto_pay_enabled,
            'is_over_budget' => $this->bill->is_over_budget,
            'action_url' => url('/utility-bills/'.$this->bill->id),
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
