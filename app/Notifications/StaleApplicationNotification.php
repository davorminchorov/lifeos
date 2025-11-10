<?php

namespace App\Notifications;

use App\Models\JobApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StaleApplicationNotification extends Notification implements ShouldQueue
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
        $daysSinceUpdate = $this->application->days_in_current_status;
        $subject = "📋 Stale application: {$this->application->company_name}";

        return (new MailMessage)
            ->subject($subject)
            ->replyTo(config('mail.from.address'), config('mail.from.name'))
            ->line("Your application to **{$this->application->company_name}** for **{$this->application->job_title}** hasn't been updated in **{$daysSinceUpdate} days**.")
            ->line("**Current Status:** {$this->application->status->label()}")
            ->when($this->application->applied_at, fn ($mail) => $mail->line("**Applied:** {$this->application->applied_at->format('F j, Y')}"))
            ->line('Consider taking action:')
            ->line('• Follow up with the recruiter or hiring manager')
            ->line('• Update the application status if you have new information')
            ->line('• Archive the application if it\'s no longer relevant')
            ->action('Review Application', url('/job-applications/'.$this->application->id))
            ->line('Keep your job search active! 💼');
    }

    /**
     * Get the database representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        $daysSinceUpdate = $this->application->days_in_current_status;

        return [
            'title' => "Stale application: {$this->application->company_name}",
            'message' => "{$this->application->job_title} hasn't been updated in {$daysSinceUpdate} days",
            'type' => 'job_application_stale',
            'job_application_id' => $this->application->id,
            'company_name' => $this->application->company_name,
            'job_title' => $this->application->job_title,
            'status' => $this->application->status->value,
            'days_since_update' => $daysSinceUpdate,
            'action_url' => url('/job-applications/'.$this->application->id),
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
