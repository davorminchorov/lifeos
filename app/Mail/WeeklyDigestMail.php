<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * One-page weekly summary composed by the weekly-digest agent and sent to the
 * user when a digest.send pending action is applied. Body text is plaintext —
 * agents should write Markdown that renders cleanly even without HTML.
 */
class WeeklyDigestMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * @param  array<string, mixed>|null  $structuredSummary
     */
    public function __construct(
        public readonly string $weekStartsOn,
        public readonly string $bodyText,
        public readonly ?string $bodyHtml = null,
        public readonly ?array $structuredSummary = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "LifeOS weekly digest — week of {$this->weekStartsOn}",
        );
    }

    public function content(): Content
    {
        // Pass-through markdown / plaintext body.
        return new Content(
            view: 'emails.weekly-digest',
            with: [
                'weekStartsOn' => $this->weekStartsOn,
                'body' => $this->bodyText,
                'bodyHtml' => $this->bodyHtml,
                'structuredSummary' => $this->structuredSummary,
            ],
        );
    }
}
