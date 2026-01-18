<?php

namespace App\Services;

use App\Enums\InvoiceStatus;
use App\Events\InvoiceIssued;
use App\Events\InvoicePaid;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class InvoicingService
{
    public function __construct(
        protected NumberingService $numberingService,
        protected TaxService $taxService,
        protected DiscountService $discountService
    ) {}

    /**
     * Create a draft invoice.
     *
     * @param User $user
     * @param array $data
     * @return Invoice
     */
    public function createDraft(User $user, array $data): Invoice
    {
        return DB::transaction(function () use ($user, $data) {
            $invoice = Invoice::create([
                'user_id' => $user->id,
                'customer_id' => $data['customer_id'],
                'status' => InvoiceStatus::DRAFT,
                'currency' => $data['currency'] ?? 'MKD',
                'tax_behavior' => $data['tax_behavior'] ?? 'exclusive',
                'net_terms_days' => $data['net_terms_days'] ?? config('invoicing.net_terms_days', 14),
                'notes' => $data['notes'] ?? null,
                'internal_notes' => $data['internal_notes'] ?? null,
                'subtotal' => 0,
                'tax_total' => 0,
                'discount_total' => 0,
                'total' => 0,
                'amount_due' => 0,
            ]);

            return $invoice;
        });
    }

    /**
     * Add an item to an invoice.
     *
     * @param Invoice $invoice
     * @param array $itemData
     * @return void
     */
    public function addItem(Invoice $invoice, array $itemData): void
    {
        if ($invoice->status !== InvoiceStatus::DRAFT) {
            throw new \Exception('Can only add items to draft invoices');
        }

        $invoice->items()->create($itemData);

        $this->recalculateTotals($invoice);
    }

    /**
     * Update an invoice item.
     *
     * @param Invoice $invoice
     * @param int $itemId
     * @param array $itemData
     * @return void
     */
    public function updateItem(Invoice $invoice, int $itemId, array $itemData): void
    {
        if ($invoice->status !== InvoiceStatus::DRAFT) {
            throw new \Exception('Can only update items on draft invoices');
        }

        $item = $invoice->items()->findOrFail($itemId);
        $item->update($itemData);

        $this->recalculateTotals($invoice);
    }

    /**
     * Remove an item from an invoice.
     *
     * @param Invoice $invoice
     * @param int $itemId
     * @return void
     */
    public function removeItem(Invoice $invoice, int $itemId): void
    {
        if ($invoice->status !== InvoiceStatus::DRAFT) {
            throw new \Exception('Can only remove items from draft invoices');
        }

        $item = $invoice->items()->findOrFail($itemId);
        $item->delete();

        $this->recalculateTotals($invoice);
    }

    /**
     * Recalculate invoice totals based on line items.
     *
     * @param Invoice $invoice
     * @return Invoice
     */
    public function recalculateTotals(Invoice $invoice): Invoice
    {
        $invoice->load(['items.taxRate', 'items.discount']);

        $subtotal = 0;
        $taxTotal = 0;
        $discountTotal = 0;

        foreach ($invoice->items as $item) {
            // Calculate line amount (quantity * unit_amount)
            $lineAmount = (int) round($item->quantity * $item->unit_amount);

            // Apply discount
            $lineDiscount = $this->discountService->calculateLineDiscount(
                $item->discount,
                $lineAmount,
                $invoice->currency
            );

            $amountAfterDiscount = $lineAmount - $lineDiscount;

            // Apply tax (on amount after discount)
            $lineTax = $this->taxService->calculateLineTax(
                $item->taxRate,
                $amountAfterDiscount,
                $invoice->tax_behavior
            );

            // Update item
            $item->update([
                'amount' => $lineAmount,
                'discount_amount' => $lineDiscount,
                'tax_amount' => $lineTax,
                'total_amount' => $amountAfterDiscount + $lineTax,
            ]);

            // Accumulate totals
            $subtotal += $lineAmount;
            $discountTotal += $lineDiscount;
            $taxTotal += $lineTax;
        }

        $total = $subtotal - $discountTotal + $taxTotal;

        $invoice->update([
            'subtotal' => $subtotal,
            'discount_total' => $discountTotal,
            'tax_total' => $taxTotal,
            'total' => $total,
            'amount_due' => $total - $invoice->amount_paid,
        ]);

        return $invoice->fresh();
    }

    /**
     * Issue an invoice (draft → issued).
     *
     * @param Invoice $invoice
     * @return Invoice
     */
    public function issue(Invoice $invoice): Invoice
    {
        if ($invoice->status !== InvoiceStatus::DRAFT) {
            throw new \Exception('Only draft invoices can be issued');
        }

        if ($invoice->items()->count() === 0) {
            throw new \Exception('Cannot issue invoice with no items');
        }

        if ($invoice->total <= 0) {
            throw new \Exception('Cannot issue invoice with zero or negative total');
        }

        return DB::transaction(function () use ($invoice) {
            // Reserve invoice number
            $number = $this->numberingService->reserveInvoiceNumber($invoice->user_id);

            // Calculate due date
            $dueAt = now()->addDays($invoice->net_terms_days);

            // Update invoice
            $invoice->update([
                'number' => $number['number'],
                'sequence_year' => $number['year'],
                'sequence_no' => $number['sequence'],
                'hash' => $this->generateHash(),
                'status' => InvoiceStatus::ISSUED,
                'issued_at' => now(),
                'due_at' => $dueAt,
            ]);

            // Fire event
            event(new InvoiceIssued($invoice));

            return $invoice->fresh();
        });
    }

    /**
     * Void an invoice.
     *
     * @param Invoice $invoice
     * @param string|null $reason
     * @return Invoice
     */
    public function void(Invoice $invoice, ?string $reason = null): Invoice
    {
        if (!in_array($invoice->status, [InvoiceStatus::ISSUED, InvoiceStatus::PARTIALLY_PAID])) {
            throw new \Exception('Only issued or partially paid invoices can be voided');
        }

        $invoice->update([
            'status' => InvoiceStatus::VOID,
            'voided_at' => now(),
            'internal_notes' => ($invoice->internal_notes ?? '') . "\n\nVoided: " . ($reason ?? 'No reason provided'),
        ]);

        return $invoice->fresh();
    }

    /**
     * Write off an invoice.
     *
     * @param Invoice $invoice
     * @param string|null $reason
     * @return Invoice
     */
    public function writeOff(Invoice $invoice, ?string $reason = null): Invoice
    {
        if ($invoice->status !== InvoiceStatus::PAST_DUE) {
            throw new \Exception('Only past due invoices can be written off');
        }

        $invoice->update([
            'status' => InvoiceStatus::WRITTEN_OFF,
            'internal_notes' => ($invoice->internal_notes ?? '') . "\n\nWritten off: " . ($reason ?? 'No reason provided'),
        ]);

        return $invoice->fresh();
    }

    /**
     * Record a payment against an invoice.
     *
     * @param Invoice $invoice
     * @param int $amount Amount in cents
     * @param array $paymentData Additional payment data
     * @return void
     */
    public function recordPayment(Invoice $invoice, int $amount, array $paymentData = []): void
    {
        if ($amount > $invoice->amount_due) {
            throw new \Exception('Payment amount exceeds amount due');
        }

        if ($amount <= 0) {
            throw new \Exception('Payment amount must be greater than zero');
        }

        DB::transaction(function () use ($invoice, $amount, $paymentData) {
            // Create payment record
            $invoice->payments()->create([
                'user_id' => $invoice->user_id,
                'amount' => $amount,
                'currency' => $invoice->currency,
                'status' => 'succeeded',
                'succeeded_at' => now(),
                'provider' => 'manual',
                ...$paymentData,
            ]);

            // Update invoice
            $newAmountPaid = $invoice->amount_paid + $amount;
            $newAmountDue = $invoice->amount_due - $amount;

            $updateData = [
                'amount_paid' => $newAmountPaid,
                'amount_due' => $newAmountDue,
            ];

            // Update status
            if ($newAmountDue === 0) {
                $updateData['status'] = InvoiceStatus::PAID;
                $updateData['paid_at'] = now();
            } else {
                $updateData['status'] = InvoiceStatus::PARTIALLY_PAID;
            }

            $invoice->update($updateData);

            // Fire event if fully paid
            if ($newAmountDue === 0) {
                event(new InvoicePaid($invoice));
            }
        });
    }

    /**
     * Check and update invoice status for overdue invoices.
     *
     * @param Invoice $invoice
     * @return void
     */
    public function checkAndUpdateOverdueStatus(Invoice $invoice): void
    {
        if ($invoice->status === InvoiceStatus::ISSUED && $invoice->due_at && $invoice->due_at->isPast()) {
            $invoice->update(['status' => InvoiceStatus::PAST_DUE]);
        }
    }

    /**
     * Get dashboard metrics for a user.
     *
     * @param User $user
     * @return array
     */
    public function getDashboardMetrics(User $user): array
    {
        $invoices = Invoice::where('user_id', $user->id);

        return [
            'total_revenue' => $invoices->clone()->sum('amount_paid'),
            'outstanding' => $invoices->clone()->unpaid()->sum('amount_due'),
            'past_due' => $invoices->clone()->pastDue()->sum('amount_due'),
            'invoices_count' => $invoices->clone()->count(),
            'paid_count' => $invoices->clone()->where('status', InvoiceStatus::PAID)->count(),
            'unpaid_count' => $invoices->clone()->unpaid()->count(),
            'overdue_count' => $invoices->clone()->pastDue()->count(),
        ];
    }

    /**
     * Generate a short hash for invoice verification.
     *
     * @return string
     */
    protected function generateHash(): string
    {
        return substr(md5(uniqid(rand(), true)), 0, 8);
    }
}
