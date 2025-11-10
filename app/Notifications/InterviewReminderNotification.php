<?php

namespace App\Notifications;

use App\Models\JobApplicationInterview;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InterviewReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private JobApplicationInterview $interview
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
        $hoursUntil = now()->diffInHours($this->interview->scheduled_at);
        $subject = $hoursUntil <= 1
            ? "🎯 Interview coming up soon: {$this->interview->jobApplication->company_name}"
            : "📅 Interview tomorrow: {$this->interview->jobApplication->company_name}";

        return (new MailMessage)
            ->subject($subject)
            ->replyTo(config('mail.from.address'), config('mail.from.name'))
            ->line("You have an upcoming interview for **{$this->interview->jobApplication->job_title}** at **{$this->interview->jobApplication->company_name}**.")
            ->line('**Interview Type:** '.ucfirst(str_replace('_', ' ', $this->interview->type->value)))
            ->line("**Scheduled:** {$this->interview->scheduled_at->format('l, F j, Y \a\t g:i A')}")
            ->when($this->interview->duration_minutes, fn ($mail) => $mail->line("**Duration:** {$this->interview->duration_minutes} minutes"))
            ->when($this->interview->location, fn ($mail) => $mail->line("**Location:** {$this->interview->location}"))
            ->when($this->interview->video_link, fn ($mail) => $mail->line("**Video Link:** {$this->interview->video_link}"))
            ->when($this->interview->interviewer_name, fn ($mail) => $mail->line("**Interviewer:** {$this->interview->interviewer_name}"))
            ->action('View Application Details', url('/job-applications/'.$this->interview->jobApplication->id))
            ->line('Good luck with your interview! 🍀');
    }

    /**
     * Get the database representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        $hoursUntil = now()->diffInHours($this->interview->scheduled_at);

        return [
            'title' => $hoursUntil <= 1
                ? "Interview coming up soon: {$this->interview->jobApplication->company_name}"
                : "Interview tomorrow: {$this->interview->jobApplication->company_name}",
            'message' => "Interview for {$this->interview->jobApplication->job_title} scheduled at {$this->interview->scheduled_at->format('g:i A')}",
            'type' => 'job_application_interview',
            'job_application_id' => $this->interview->job_application_id,
            'interview_id' => $this->interview->id,
            'company_name' => $this->interview->jobApplication->company_name,
            'job_title' => $this->interview->jobApplication->job_title,
            'interview_type' => $this->interview->type->value,
            'scheduled_at' => $this->interview->scheduled_at->toDateTimeString(),
            'action_url' => url('/job-applications/'.$this->interview->job_application_id),
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
