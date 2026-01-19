<?php

namespace App\Http\Controllers;

use App\Models\RecurringInvoice;
use App\Models\RecurringInvoiceItem;
use Illuminate\Http\Request;

class RecurringInvoiceItemController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, RecurringInvoice $recurringInvoice)
    {
        // Ensure user owns this recurring invoice
        if ($recurringInvoice->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'description' => ['required', 'string', 'max:500'],
            'quantity' => ['required', 'numeric', 'min:0.001'],
            'unit_amount' => ['required', 'integer', 'min:0'],
            'tax_rate_id' => ['nullable', 'exists:tax_rates,id'],
            'discount_id' => ['nullable', 'exists:discounts,id'],
        ]);

        $validated['user_id'] = auth()->id();
        $validated['recurring_invoice_id'] = $recurringInvoice->id;

        // Get next sort order
        $maxSortOrder = $recurringInvoice->items()->max('sort_order') ?? 0;
        $validated['sort_order'] = $maxSortOrder + 1;

        RecurringInvoiceItem::create($validated);

        return redirect()->route('invoicing.recurring-invoices.show', $recurringInvoice)
            ->with('success', 'Item added successfully!');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, RecurringInvoice $recurringInvoice, RecurringInvoiceItem $item)
    {
        // Ensure user owns this item
        if ($item->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'description' => ['required', 'string', 'max:500'],
            'quantity' => ['required', 'numeric', 'min:0.001'],
            'unit_amount' => ['required', 'integer', 'min:0'],
            'tax_rate_id' => ['nullable', 'exists:tax_rates,id'],
            'discount_id' => ['nullable', 'exists:discounts,id'],
        ]);

        $item->update($validated);

        return redirect()->route('invoicing.recurring-invoices.show', $recurringInvoice)
            ->with('success', 'Item updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RecurringInvoice $recurringInvoice, RecurringInvoiceItem $item)
    {
        // Ensure user owns this item
        if ($item->user_id !== auth()->id()) {
            abort(403);
        }

        $item->delete();

        return redirect()->route('invoicing.recurring-invoices.show', $recurringInvoice)
            ->with('success', 'Item deleted successfully!');
    }
}
