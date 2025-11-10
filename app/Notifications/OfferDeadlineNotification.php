<?php

namespace App\Notifications;

use App\Models\JobApplicationOffer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OfferDeadlineNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private JobApplicationOffer $offer
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
        $daysUntil = now()->diffInDays($this->offer->decision_deadline);
        $subject = $daysUntil === 0
            ? "⏰ Offer deadline TODAY: {$this->offer->jobApplication->company_name}"
            : "⏰ Offer deadline in {$daysUntil} days: {$this->offer->jobApplication->company_name}";

        $currencyService = app(\App\Services\CurrencyService::class);

        return (new MailMessage)
            ->subject($subject)
            ->replyTo(config('mail.from.address'), config('mail.from.name'))
            ->line("Your job offer from **{$this->offer->jobApplication->company_name}** for **{$this->offer->jobApplication->job_title}** has an upcoming decision deadline.")
            ->line("**Decision Deadline:** {$this->offer->decision_deadline->format('l, F j, Y')}")
            ->line('**Base Salary:** '.$currencyService->format($this->offer->base_salary, $this->offer->currency))
            ->when($this->offer->bonus, fn ($mail) => $mail->line('**Bonus:** '.$currencyService->format($this->offer->bonus, $this->offer->currency)))
            ->when($this->offer->equity, fn ($mail) => $mail->line("**Equity:** {$this->offer->equity}"))
            ->line('**Status:** '.ucfirst($this->offer->status->value))
            ->action('Review Offer', url('/job-applications/'.$this->offer->jobApplication->id))
            ->line($daysUntil === 0
                ? 'Make your decision today! ⚡'
                : 'Make sure to respond before the deadline! 📝');
    }

    /**
     * Get the database representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        $daysUntil = now()->diffInDays($this->offer->decision_deadline);

        return [
            'title' => $daysUntil === 0
                ? "Offer deadline TODAY: {$this->offer->jobApplication->company_name}"
                : "Offer deadline in {$daysUntil} days: {$this->offer->jobApplication->company_name}",
            'message' => "Decision deadline for {$this->offer->jobApplication->job_title} is on {$this->offer->decision_deadline->format('M j, Y')}",
            'type' => 'job_application_offer_deadline',
            'job_application_id' => $this->offer->job_application_id,
            'offer_id' => $this->offer->id,
            'company_name' => $this->offer->jobApplication->company_name,
            'job_title' => $this->offer->jobApplication->job_title,
            'base_salary' => $this->offer->base_salary,
            'currency' => $this->offer->currency,
            'decision_deadline' => $this->offer->decision_deadline->toDateString(),
            'days_until_deadline' => $daysUntil,
            'action_url' => url('/job-applications/'.$this->offer->job_application_id),
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
