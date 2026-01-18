<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceReminder;
use App\Enums\InvoiceStatus;
use Carbon\Carbon;

class InvoiceReminderService
{
    public function __construct(
        protected InvoiceEmailService $emailService
    ) {}

    /**
     * Get default reminder schedule (days after due date)
     *
     * @return array
     */
    protected function getDefaultSchedule(): array
    {
        return [
            ['days' => 3, 'type' => 'first'],
            ['days' => 7, 'type' => 'second'],
            ['days' => 14, 'type' => 'final'],
        ];
    }

    /**
     * Find invoices that need reminders
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getInvoicesNeedingReminders()
    {
        $schedule = $this->getDefaultSchedule();

        return Invoice::with(['customer', 'reminders'])
            ->whereIn('status', [
                InvoiceStatus::ISSUED,
                InvoiceStatus::PARTIALLY_PAID,
                InvoiceStatus::PAST_DUE,
            ])
            ->where('amount_due', '>', 0)
            ->whereNotNull('due_at')
            ->where('due_at', '<', now())
            ->get()
            ->filter(function ($invoice) use ($schedule) {
                return $this->shouldSendReminder($invoice, $schedule);
            });
    }

    /**
     * Check if invoice should receive a reminder
     *
     * @param Invoice $invoice
     * @param array $schedule
     * @return bool
     */
    protected function shouldSendReminder(Invoice $invoice, array $schedule): bool
    {
        $daysOverdue = now()->diffInDays($invoice->due_at, false);

        foreach ($schedule as $reminder) {
            $targetDays = $reminder['days'];

            // Check if we've reached this reminder milestone
            if ($daysOverdue >= $targetDays) {
                // Check if we've already sent this type of reminder
                $alreadySent = $invoice->reminders()
                    ->where('reminder_type', $reminder['type'])
                    ->where('days_after_due', $targetDays)
                    ->exists();

                if (!$alreadySent) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Get the next reminder to send for an invoice
     *
     * @param Invoice $invoice
     * @return array|null
     */
    protected function getNextReminder(Invoice $invoice): ?array
    {
        $schedule = $this->getDefaultSchedule();
        $daysOverdue = now()->diffInDays($invoice->due_at, false);

        foreach ($schedule as $reminder) {
            if ($daysOverdue >= $reminder['days']) {
                $alreadySent = $invoice->reminders()
                    ->where('reminder_type', $reminder['type'])
                    ->where('days_after_due', $reminder['days'])
                    ->exists();

                if (!$alreadySent) {
                    return $reminder;
                }
            }
        }

        return null;
    }

    /**
     * Send reminder for an invoice
     *
     * @param Invoice $invoice
     * @param string|null $customMessage
     * @return InvoiceReminder|null
     */
    public function sendReminder(Invoice $invoice, ?string $customMessage = null): ?InvoiceReminder
    {
        $nextReminder = $this->getNextReminder($invoice);

        if (!$nextReminder) {
            return null;
        }

        // Generate reminder message if not provided
        $message = $customMessage ?? $this->generateReminderMessage($invoice, $nextReminder['type']);

        try {
            // Send the reminder email
            $this->emailService->sendReminder($invoice, $message);

            // Record the reminder
            $reminder = InvoiceReminder::create([
                'user_id' => $invoice->user_id,
                'invoice_id' => $invoice->id,
                'days_after_due' => $nextReminder['days'],
                'reminder_type' => $nextReminder['type'],
                'message' => $message,
                'sent_at' => now(),
                'email_sent' => true,
            ]);

            return $reminder;
        } catch (\Exception $e) {
            // Record failed reminder
            $reminder = InvoiceReminder::create([
                'user_id' => $invoice->user_id,
                'invoice_id' => $invoice->id,
                'days_after_due' => $nextReminder['days'],
                'reminder_type' => $nextReminder['type'],
                'message' => $message,
                'sent_at' => now(),
                'email_sent' => false,
                'email_error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Generate reminder message based on type
     *
     * @param Invoice $invoice
     * @param string $type
     * @return string
     */
    protected function generateReminderMessage(Invoice $invoice, string $type): string
    {
        $daysOverdue = now()->diffInDays($invoice->due_at, false);

        return match($type) {
            'first' => "This is a friendly reminder that Invoice {$invoice->number} is now {$daysOverdue} day(s) overdue. Please submit payment at your earliest convenience to avoid any service interruptions.",
            'second' => "This is a second reminder that Invoice {$invoice->number} is now {$daysOverdue} day(s) overdue. Please submit payment immediately to bring your account current.",
            'final' => "FINAL NOTICE: Invoice {$invoice->number} is now {$daysOverdue} day(s) overdue. Immediate payment is required to avoid further action. Please contact us if you have any questions or concerns.",
            default => "Payment reminder for Invoice {$invoice->number}, which is {$daysOverdue} day(s) overdue.",
        };
    }

    /**
     * Process all invoices needing reminders
     *
     * @return array
     */
    public function processAllReminders(): array
    {
        $invoices = $this->getInvoicesNeedingReminders();

        $results = [
            'processed' => 0,
            'sent' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        foreach ($invoices as $invoice) {
            $results['processed']++;

            try {
                $reminder = $this->sendReminder($invoice);

                if ($reminder) {
                    $results['sent']++;

                    logger()->info('Sent payment reminder', [
                        'invoice_id' => $invoice->id,
                        'invoice_number' => $invoice->number,
                        'reminder_type' => $reminder->reminder_type,
                        'days_overdue' => $reminder->days_after_due,
                    ]);
                }
            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = [
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->number,
                    'error' => $e->getMessage(),
                ];

                logger()->error('Failed to send payment reminder', [
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->number,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $results;
    }

    /**
     * Get reminder statistics for an invoice
     *
     * @param Invoice $invoice
     * @return array
     */
    public function getReminderStats(Invoice $invoice): array
    {
        $reminders = $invoice->reminders;

        return [
            'total_sent' => $reminders->where('email_sent', true)->count(),
            'total_failed' => $reminders->where('email_sent', false)->count(),
            'last_sent_at' => $reminders->where('email_sent', true)->first()?->sent_at,
            'next_reminder_type' => $this->getNextReminder($invoice)['type'] ?? null,
        ];
    }
}
