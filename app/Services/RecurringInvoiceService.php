<?php

namespace App\Services;

use App\Enums\BillingInterval;
use App\Enums\InvoiceStatus;
use App\Enums\RecurringStatus;
use App\Models\Invoice;
use App\Models\RecurringInvoice;
use Carbon\Carbon;

class RecurringInvoiceService
{
    public function __construct(
        protected InvoicingService $invoicingService,
        protected InvoiceEmailService $emailService
    ) {}

    /**
     * Generate invoice from recurring invoice template
     *
     * @param RecurringInvoice $recurringInvoice
     * @return Invoice
     */
    public function generateInvoice(RecurringInvoice $recurringInvoice): Invoice
    {
        // Load relationships
        $recurringInvoice->load(['customer', 'items.taxRate', 'items.discount']);

        // Create invoice
        $invoice = Invoice::create([
            'user_id' => $recurringInvoice->user_id,
            'tenant_id' => $recurringInvoice->tenant_id, // Explicit tenant_id for console commands
            'customer_id' => $recurringInvoice->customer_id,
            'subscription_id' => $recurringInvoice->id,
            'status' => InvoiceStatus::DRAFT,
            'currency' => $recurringInvoice->currency,
            'tax_behavior' => $recurringInvoice->tax_behavior,
            'net_terms_days' => $recurringInvoice->net_terms_days,
            'notes' => $this->generateInvoiceNotes($recurringInvoice),
            'internal_notes' => "Auto-generated from recurring invoice: {$recurringInvoice->name}",
        ]);

        // Add line items
        foreach ($recurringInvoice->items as $item) {
            $this->invoicingService->addItem($invoice, [
                'description' => $item->description,
                'quantity' => $item->quantity,
                'unit_amount' => $item->unit_amount,
                'tax_rate_id' => $item->tax_rate_id,
                'discount_id' => $item->discount_id,
            ]);
        }

        // Recalculate totals
        $this->invoicingService->recalculateTotals($invoice);

        // Issue the invoice
        $this->invoicingService->issue($invoice);

        // Send email if auto-send is enabled
        if ($recurringInvoice->auto_send_email && $recurringInvoice->customer->email) {
            try {
                $this->emailService->sendInvoice($invoice);
            } catch (\Exception $e) {
                // Log error but don't fail invoice generation
                logger()->error('Failed to auto-send recurring invoice', [
                    'recurring_invoice_id' => $recurringInvoice->id,
                    'invoice_id' => $invoice->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Update recurring invoice
        $this->updateAfterGeneration($recurringInvoice);

        return $invoice;
    }

    /**
     * Update recurring invoice after generating an invoice
     *
     * @param RecurringInvoice $recurringInvoice
     * @return void
     */
    protected function updateAfterGeneration(RecurringInvoice $recurringInvoice): void
    {
        $recurringInvoice->increment('occurrences_count');
        $recurringInvoice->update([
            'last_generated_at' => now(),
            'next_billing_date' => $this->calculateNextBillingDate($recurringInvoice),
        ]);

        // Check if should be completed
        if ($recurringInvoice->hasReachedLimit() || $recurringInvoice->hasPassedEndDate()) {
            $recurringInvoice->complete();
        }
    }

    /**
     * Calculate next billing date
     *
     * @param RecurringInvoice $recurringInvoice
     * @return Carbon
     */
    public function calculateNextBillingDate(RecurringInvoice $recurringInvoice): Carbon
    {
        $currentDate = Carbon::parse($recurringInvoice->next_billing_date);
        $intervalCount = $recurringInvoice->interval_count;

        $nextDate = match($recurringInvoice->billing_interval) {
            BillingInterval::DAILY => $currentDate->addDays($intervalCount),
            BillingInterval::WEEKLY => $currentDate->addWeeks($intervalCount),
            BillingInterval::MONTHLY => $this->calculateNextMonthlyDate($currentDate, $intervalCount, $recurringInvoice->billing_day_of_month),
            BillingInterval::QUARTERLY => $currentDate->addMonths(3 * $intervalCount),
            BillingInterval::YEARLY => $this->calculateNextYearlyDate($currentDate, $intervalCount, $recurringInvoice->billing_day_of_month),
        };

        return $nextDate;
    }

    /**
     * Calculate next monthly billing date with proper day handling
     *
     * @param Carbon $currentDate
     * @param int $months
     * @param int|null $preferredDay
     * @return Carbon
     */
    protected function calculateNextMonthlyDate(Carbon $currentDate, int $months, ?int $preferredDay): Carbon
    {
        $nextDate = $currentDate->copy()->addMonths($months);

        // If preferred day is set, use it (handling month-end overflow)
        if ($preferredDay !== null) {
            $daysInMonth = $nextDate->daysInMonth;
            $day = min($preferredDay, $daysInMonth);
            $nextDate->day($day);
        }

        return $nextDate;
    }

    /**
     * Calculate next yearly billing date with proper day handling
     *
     * @param Carbon $currentDate
     * @param int $years
     * @param int|null $preferredDay
     * @return Carbon
     */
    protected function calculateNextYearlyDate(Carbon $currentDate, int $years, ?int $preferredDay): Carbon
    {
        $nextDate = $currentDate->copy()->addYears($years);

        // If preferred day is set, use it (handling month-end overflow)
        if ($preferredDay !== null) {
            $daysInMonth = $nextDate->daysInMonth;
            $day = min($preferredDay, $daysInMonth);
            $nextDate->day($day);
        }

        return $nextDate;
    }

    /**
     * Generate invoice notes from recurring invoice
     *
     * @param RecurringInvoice $recurringInvoice
     * @return string|null
     */
    protected function generateInvoiceNotes(RecurringInvoice $recurringInvoice): ?string
    {
        $notes = [];

        if ($recurringInvoice->description) {
            $notes[] = $recurringInvoice->description;
        }

        $notes[] = "Billing Period: " . $recurringInvoice->billing_interval->label();

        if ($recurringInvoice->notes) {
            $notes[] = $recurringInvoice->notes;
        }

        return implode("\n\n", $notes);
    }

    /**
     * Process all recurring invoices due for generation
     *
     * @return array
     */
    public function processAllDue(): array
    {
        $recurringInvoices = RecurringInvoice::with(['customer', 'items'])
            ->dueForGeneration()
            ->get();

        $results = [
            'processed' => 0,
            'generated' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        foreach ($recurringInvoices as $recurringInvoice) {
            $results['processed']++;

            try {
                $invoice = $this->generateInvoice($recurringInvoice);
                $results['generated']++;

                logger()->info('Generated recurring invoice', [
                    'recurring_invoice_id' => $recurringInvoice->id,
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->number,
                ]);
            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = [
                    'recurring_invoice_id' => $recurringInvoice->id,
                    'error' => $e->getMessage(),
                ];

                logger()->error('Failed to generate recurring invoice', [
                    'recurring_invoice_id' => $recurringInvoice->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $results;
    }
}
