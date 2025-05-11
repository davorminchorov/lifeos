<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SubscriptionReminderMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        private readonly object $reminder
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Payment Reminder: {$this->reminder->subscription_name}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $daysUntil = \Carbon\Carbon::parse($this->reminder->payment_date)
            ->diffInDays(\Carbon\Carbon::today());

        $dayText = $daysUntil === 1 ? '1 day' : "{$daysUntil} days";

        return new Content(
            markdown: 'emails.subscriptions.reminder',
            with: [
                'subscriptionName' => $this->reminder->subscription_name,
                'paymentDate' => $this->reminder->payment_date,
                'amount' => $this->reminder->amount,
                'currency' => $this->reminder->currency,
                'daysUntil' => $dayText,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
