<?php

namespace App\Services;

use App\Mail\InvoiceMail;
use App\Mail\PaymentConfirmationMail;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Support\Facades\Mail;

class InvoiceEmailService
{
    /**
     * Send invoice to customer
     *
     * @param Invoice $invoice
     * @param string|null $customMessage
     * @param bool $attachPdf
     * @return bool
     */
    public function sendInvoice(Invoice $invoice, ?string $customMessage = null, bool $attachPdf = true): bool
    {
        try {
            // Ensure customer has email
            if (!$invoice->customer->email) {
                throw new \Exception('Customer does not have an email address.');
            }

            // Send email
            Mail::to($invoice->customer->email)
                ->send(new InvoiceMail($invoice, $customMessage, $attachPdf));

            // Update invoice metadata
            $invoice->update([
                'last_sent_at' => now(),
            ]);

            return true;
        } catch (\Exception $e) {
            throw new \Exception('Failed to send invoice email: ' . $e->getMessage());
        }
    }

    /**
     * Send payment confirmation to customer
     *
     * @param Payment $payment
     * @param Invoice $invoice
     * @param string|null $customMessage
     * @return bool
     */
    public function sendPaymentConfirmation(Payment $payment, Invoice $invoice, ?string $customMessage = null): bool
    {
        try {
            // Ensure customer has email
            if (!$invoice->customer->email) {
                throw new \Exception('Customer does not have an email address.');
            }

            // Send email
            Mail::to($invoice->customer->email)
                ->send(new PaymentConfirmationMail($payment, $invoice, $customMessage));

            return true;
        } catch (\Exception $e) {
            throw new \Exception('Failed to send payment confirmation email: ' . $e->getMessage());
        }
    }

    /**
     * Send invoice reminder to customer
     *
     * @param Invoice $invoice
     * @param string|null $customMessage
     * @return bool
     */
    public function sendReminder(Invoice $invoice, ?string $customMessage = null): bool
    {
        $defaultMessage = "This is a friendly reminder that payment for this invoice is ";

        if ($invoice->due_at->isPast()) {
            $daysOverdue = now()->diffInDays($invoice->due_at);
            $defaultMessage .= "now {$daysOverdue} day(s) overdue. Please submit payment at your earliest convenience.";
        } else {
            $daysUntilDue = now()->diffInDays($invoice->due_at);
            $defaultMessage .= "due in {$daysUntilDue} day(s). Please ensure payment is submitted by {$invoice->due_at->format('F d, Y')}.";
        }

        $message = $customMessage ?? $defaultMessage;

        return $this->sendInvoice($invoice, $message, true);
    }

    /**
     * Check if an invoice can be sent
     *
     * @param Invoice $invoice
     * @return bool
     */
    public function canSendInvoice(Invoice $invoice): bool
    {
        // Must have customer email
        if (!$invoice->customer->email) {
            return false;
        }

        // Must have at least one line item
        if ($invoice->items->count() === 0) {
            return false;
        }

        // Must have a total greater than zero
        if ($invoice->total <= 0) {
            return false;
        }

        return true;
    }

    /**
     * Get validation errors for sending invoice
     *
     * @param Invoice $invoice
     * @return array
     */
    public function getSendValidationErrors(Invoice $invoice): array
    {
        $errors = [];

        if (!$invoice->customer->email) {
            $errors[] = 'Customer does not have an email address.';
        }

        if ($invoice->items->count() === 0) {
            $errors[] = 'Invoice must have at least one line item.';
        }

        if ($invoice->total <= 0) {
            $errors[] = 'Invoice total must be greater than zero.';
        }

        return $errors;
    }
}
