<?php

namespace App\Notifications;

use App\Models\JobApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NextActionReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private JobApplication $application
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
        return $notifiable->getEnabledNotificationChannels('job_application_reminder');
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $daysOverdue = now()->diffInDays($this->application->next_action_at);
        $subject = $daysOverdue === 0
            ? "⏰ Action needed TODAY: {$this->application->company_name}"
            : "⚠️ Overdue action: {$this->application->company_name}";

        return (new MailMessage)
            ->subject($subject)
            ->replyTo(config('mail.from.address'), config('mail.from.name'))
            ->line("You have an overdue action for your application to **{$this->application->company_name}** for the position of **{$this->application->job_title}**.")
            ->line("**Next Action Was Due:** {$this->application->next_action_at->format('l, F j, Y \a\t g:i A')}")
            ->line("**Current Status:** {$this->application->status->label()}")
            ->when($this->application->contact_name, fn ($mail) => $mail->line("**Contact:** {$this->application->contact_name}"))
            ->when($this->application->contact_email, fn ($mail) => $mail->line("**Email:** {$this->application->contact_email}"))
            ->action('Update Application', url('/job-applications/'.$this->application->id.'/edit'))
            ->line($daysOverdue === 0
                ? 'Take action today to keep your application moving forward! 🚀'
                : "This action is {$daysOverdue} day(s) overdue. Update your application! ⚡");
    }

    /**
     * Get the database representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        $daysOverdue = now()->diffInDays($this->application->next_action_at);

        return [
            'title' => $daysOverdue === 0
                ? "Action needed TODAY: {$this->application->company_name}"
                : "Overdue action: {$this->application->company_name}",
            'message' => "Next action for {$this->application->job_title} was due on {$this->application->next_action_at->format('M j, Y')}",
            'type' => 'job_application_next_action',
            'job_application_id' => $this->application->id,
            'company_name' => $this->application->company_name,
            'job_title' => $this->application->job_title,
            'status' => $this->application->status->value,
            'next_action_at' => $this->application->next_action_at->toDateTimeString(),
            'days_overdue' => $daysOverdue,
            'action_url' => url('/job-applications/'.$this->application->id.'/edit'),
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
