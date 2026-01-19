<?php

namespace App\Http\Controllers;

use App\Models\Discount;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\TaxRate;
use App\Services\InvoicingService;
use Illuminate\Http\Request;

class InvoiceItemController extends Controller
{
    public function __construct(
        protected InvoicingService $invoicingService
    ) {}

    /**
     * Store a newly created line item.
     */
    public function store(Request $request, Invoice $invoice)
    {
        // Ensure user owns this invoice
        if ($invoice->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'description' => ['required', 'string', 'max:500'],
            'quantity' => ['required', 'numeric', 'min:0.001', 'max:999999'],
            'unit_amount' => ['required', 'integer', 'min:0'],
            'tax_rate_id' => ['nullable', 'exists:tax_rates,id'],
            'discount_id' => ['nullable', 'exists:discounts,id'],
        ]);

        // Validate tax_rate belongs to user if provided
        if (isset($validated['tax_rate_id'])) {
            $taxRate = TaxRate::find($validated['tax_rate_id']);
            if ($taxRate && $taxRate->user_id !== auth()->id()) {
                abort(403, 'Invalid tax rate');
            }
        }

        // Validate discount belongs to user if provided
        if (isset($validated['discount_id'])) {
            $discount = Discount::find($validated['discount_id']);
            if ($discount && $discount->user_id !== auth()->id()) {
                abort(403, 'Invalid discount');
            }
        }

        try {
            $this->invoicingService->addItem($invoice, $validated);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Line item added successfully',
                    'invoice' => $invoice->fresh(['items.taxRate', 'items.discount']),
                ]);
            }

            return redirect()->route('invoicing.invoices.show', $invoice)
                ->with('success', 'Line item added successfully!');
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
     * Update the specified line item.
     */
    public function update(Request $request, Invoice $invoice, InvoiceItem $item)
    {
        // Ensure user owns this invoice
        if ($invoice->user_id !== auth()->id()) {
            abort(403);
        }

        // Ensure item belongs to this invoice
        if ($item->invoice_id !== $invoice->id) {
            abort(404);
        }

        $validated = $request->validate([
            'description' => ['required', 'string', 'max:500'],
            'quantity' => ['required', 'numeric', 'min:0.001', 'max:999999'],
            'unit_amount' => ['required', 'integer', 'min:0'],
            'tax_rate_id' => ['nullable', 'exists:tax_rates,id'],
            'discount_id' => ['nullable', 'exists:discounts,id'],
        ]);

        // Validate tax_rate belongs to user if provided
        if (isset($validated['tax_rate_id'])) {
            $taxRate = TaxRate::find($validated['tax_rate_id']);
            if ($taxRate && $taxRate->user_id !== auth()->id()) {
                abort(403, 'Invalid tax rate');
            }
        }

        // Validate discount belongs to user if provided
        if (isset($validated['discount_id'])) {
            $discount = Discount::find($validated['discount_id']);
            if ($discount && $discount->user_id !== auth()->id()) {
                abort(403, 'Invalid discount');
            }
        }

        try {
            $this->invoicingService->updateItem($invoice, $item->id, $validated);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Line item updated successfully',
                    'invoice' => $invoice->fresh(['items.taxRate', 'items.discount']),
                ]);
            }

            return redirect()->route('invoicing.invoices.show', $invoice)
                ->with('success', 'Line item updated successfully!');
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
     * Remove the specified line item.
     */
    public function destroy(Request $request, Invoice $invoice, InvoiceItem $item)
    {
        // Ensure user owns this invoice
        if ($invoice->user_id !== auth()->id()) {
            abort(403);
        }

        // Ensure item belongs to this invoice
        if ($item->invoice_id !== $invoice->id) {
            abort(404);
        }

        try {
            $this->invoicingService->removeItem($invoice, $item->id);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Line item removed successfully',
                    'invoice' => $invoice->fresh(['items.taxRate', 'items.discount']),
                ]);
            }

            return redirect()->route('invoicing.invoices.show', $invoice)
                ->with('success', 'Line item removed successfully!');
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
