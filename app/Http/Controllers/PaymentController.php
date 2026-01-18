<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use App\Services\InvoicingService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(
        protected InvoicingService $invoicingService
    ) {}

    /**
     * Store a newly created payment.
     */
    public function store(Request $request, Invoice $invoice)
    {
        // Ensure user owns this invoice
        if ($invoice->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'amount' => ['required', 'integer', 'min:1'],
            'payment_date' => ['required', 'date'],
            'payment_method' => ['required', 'string', 'in:bank_transfer,cash,check,credit_card,debit_card,other'],
            'reference' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        // Validate payment amount doesn't exceed amount due
        if ($validated['amount'] > $invoice->amount_due) {
            return redirect()->back()
                ->with('error', 'Payment amount cannot exceed the amount due.')
                ->withInput();
        }

        try {
            // Record payment via InvoicingService
            $this->invoicingService->recordPayment($invoice, $validated['amount'], [
                'payment_date' => $validated['payment_date'],
                'payment_method' => $validated['payment_method'],
                'reference' => $validated['reference'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            // Get the created payment
            $payment = $invoice->payments()->latest()->first();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Payment recorded successfully',
                    'payment' => $payment,
                    'invoice' => $invoice->fresh(['payments']),
                ]);
            }

            return redirect()->route('invoicing.invoices.show', $invoice)
                ->with('success', 'Payment recorded successfully!');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return redirect()->back()
                ->with('error', $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified payment.
     */
    public function show(Invoice $invoice, Payment $payment)
    {
        // Ensure user owns this invoice
        if ($invoice->user_id !== auth()->id()) {
            abort(403);
        }

        // Ensure payment belongs to this invoice
        if ($payment->invoice_id !== $invoice->id) {
            abort(404);
        }

        return view('payments.show', compact('invoice', 'payment'));
    }

    /**
     * Remove the specified payment.
     */
    public function destroy(Request $request, Invoice $invoice, Payment $payment)
    {
        // Ensure user owns this invoice
        if ($invoice->user_id !== auth()->id()) {
            abort(403);
        }

        // Ensure payment belongs to this invoice
        if ($payment->invoice_id !== $invoice->id) {
            abort(404);
        }

        try {
            // Delete payment
            $payment->delete();

            // Recalculate invoice totals and status
            $this->invoicingService->recalculateTotals($invoice);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Payment deleted successfully',
                    'invoice' => $invoice->fresh(['payments']),
                ]);
            }

            return redirect()->route('invoicing.invoices.show', $invoice)
                ->with('success', 'Payment deleted successfully!');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }
}
