<?php

namespace App\Http\Controllers;

use App\Enums\InvoiceStatus;
use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\UpdateInvoiceRequest;
use App\Models\Customer;
use App\Models\Invoice;
use App\Services\InvoiceEmailService;
use App\Services\InvoicingService;
use App\Services\InvoicePdfService;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function __construct(
        protected InvoicingService $invoicingService,
        protected InvoicePdfService $pdfService,
        protected InvoiceEmailService $emailService
    ) {}
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

    /**
     * Issue an invoice (draft → issued).
     */
    public function issue(Invoice $invoice)
    {
        // Ensure user owns this invoice
        if ($invoice->user_id !== auth()->id()) {
            abort(403);
        }

        try {
            $this->invoicingService->issue($invoice);

            return redirect()->route('invoicing.invoices.show', $invoice)
                ->with('success', 'Invoice issued successfully!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Void an invoice.
     */
    public function void(Request $request, Invoice $invoice)
    {
        // Ensure user owns this invoice
        if ($invoice->user_id !== auth()->id()) {
            abort(403);
        }

        $reason = $request->input('reason');

        try {
            $this->invoicingService->void($invoice, $reason);

            return redirect()->route('invoicing.invoices.show', $invoice)
                ->with('success', 'Invoice voided successfully!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Download invoice as PDF.
     */
    public function downloadPdf(Invoice $invoice)
    {
        // Ensure user owns this invoice
        if ($invoice->user_id !== auth()->id()) {
            abort(403);
        }

        return $this->pdfService->download($invoice);
    }

    /**
     * View invoice PDF in browser.
     */
    public function viewPdf(Invoice $invoice)
    {
        // Ensure user owns this invoice
        if ($invoice->user_id !== auth()->id()) {
            abort(403);
        }

        return $this->pdfService->stream($invoice);
    }

    /**
     * Send invoice via email to customer.
     */
    public function sendEmail(Request $request, Invoice $invoice)
    {
        // Ensure user owns this invoice
        if ($invoice->user_id !== auth()->id()) {
            abort(403);
        }

        // Validate request
        $request->validate([
            'message' => ['nullable', 'string', 'max:1000'],
            'attach_pdf' => ['nullable', 'boolean'],
        ]);

        try {
            // Check if invoice can be sent
            $errors = $this->emailService->getSendValidationErrors($invoice);
            if (!empty($errors)) {
                return redirect()->back()
                    ->with('error', 'Cannot send invoice: ' . implode(' ', $errors));
            }

            $this->emailService->sendInvoice(
                $invoice,
                $request->input('message'),
                $request->boolean('attach_pdf', true)
            );

            return redirect()->route('invoicing.invoices.show', $invoice)
                ->with('success', 'Invoice sent successfully to ' . $invoice->customer->email);
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Send invoice reminder via email.
     */
    public function sendReminder(Request $request, Invoice $invoice)
    {
        // Ensure user owns this invoice
        if ($invoice->user_id !== auth()->id()) {
            abort(403);
        }

        // Validate request
        $request->validate([
            'message' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $this->emailService->sendReminder(
                $invoice,
                $request->input('message')
            );

            return redirect()->route('invoicing.invoices.show', $invoice)
                ->with('success', 'Reminder sent successfully to ' . $invoice->customer->email);
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }
}
