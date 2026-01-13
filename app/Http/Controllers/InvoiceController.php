<?php

namespace App\Http\Controllers;

use App\Enums\InvoiceStatus;
use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\UpdateInvoiceRequest;
use App\Models\Customer;
use App\Models\Invoice;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Invoice::where('user_id', auth()->id())
            ->with(['customer']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by customer
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        // Search by invoice number
        if ($request->filled('search')) {
            $query->where('number', 'like', '%' . $request->search . '%');
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $invoices = $query->paginate($request->get('per_page', 20));

        // Calculate summary statistics
        $summary = [
            'total_invoices' => Invoice::where('user_id', auth()->id())->count(),
            'draft_count' => Invoice::where('user_id', auth()->id())->draft()->count(),
            'total_outstanding' => Invoice::where('user_id', auth()->id())->unpaid()->sum('amount_due'),
            'total_overdue' => Invoice::where('user_id', auth()->id())->pastDue()->sum('amount_due'),
        ];

        // Get customers for filter
        $customers = Customer::where('user_id', auth()->id())
            ->orderBy('name')
            ->get();

        return view('invoices.index', compact('invoices', 'summary', 'customers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $customers = Customer::where('user_id', auth()->id())
            ->orderBy('name')
            ->get();

        // Pre-select customer if provided
        $selectedCustomerId = $request->get('customer_id');

        return view('invoices.create', compact('customers', 'selectedCustomerId'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreInvoiceRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = auth()->id();
        $data['status'] = InvoiceStatus::DRAFT;

        // Set default net terms if not provided
        if (!isset($data['net_terms_days'])) {
            $data['net_terms_days'] = config('invoicing.net_terms_days', 14);
        }

        $invoice = Invoice::create($data);

        return redirect()->route('invoicing.invoices.show', $invoice)
            ->with('success', 'Invoice draft created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Invoice $invoice)
    {
        // Ensure user owns this invoice
        if ($invoice->user_id !== auth()->id()) {
            abort(403);
        }

        // Load relationships
        $invoice->load(['customer', 'items.taxRate', 'items.discount', 'payments']);

        return view('invoices.show', compact('invoice'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Invoice $invoice)
    {
        // Ensure user owns this invoice
        if ($invoice->user_id !== auth()->id()) {
            abort(403);
        }

        // Only draft invoices can be edited
        if ($invoice->status !== InvoiceStatus::DRAFT) {
            return redirect()->route('invoicing.invoices.show', $invoice)
                ->with('error', 'Only draft invoices can be edited.');
        }

        $customers = Customer::where('user_id', auth()->id())
            ->orderBy('name')
            ->get();

        return view('invoices.edit', compact('invoice', 'customers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateInvoiceRequest $request, Invoice $invoice)
    {
        // Ensure user owns this invoice
        if ($invoice->user_id !== auth()->id()) {
            abort(403);
        }

        // Only draft invoices can be updated
        if ($invoice->status !== InvoiceStatus::DRAFT) {
            return redirect()->route('invoicing.invoices.show', $invoice)
                ->with('error', 'Only draft invoices can be edited.');
        }

        $invoice->update($request->validated());

        return redirect()->route('invoicing.invoices.show', $invoice)
            ->with('success', 'Invoice updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Invoice $invoice)
    {
        // Ensure user owns this invoice
        if ($invoice->user_id !== auth()->id()) {
            abort(403);
        }

        // Only draft invoices can be deleted
        if ($invoice->status !== InvoiceStatus::DRAFT) {
            return redirect()->route('invoicing.invoices.index')
                ->with('error', 'Only draft invoices can be deleted.');
        }

        $invoice->delete();

        return redirect()->route('invoicing.invoices.index')
            ->with('success', 'Invoice deleted successfully!');
    }
}
