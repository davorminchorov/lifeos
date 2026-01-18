<?php

namespace App\Http\Controllers;

use App\Enums\CreditNoteStatus;
use App\Models\CreditNote;
use App\Models\Customer;
use App\Models\Invoice;
use App\Services\InvoicingService;
use Illuminate\Http\Request;

class CreditNoteController extends Controller
{
    public function __construct(
        protected InvoicingService $invoicingService
    ) {}

    /**
     * Display a listing of credit notes.
     */
    public function index(Request $request)
    {
        $query = CreditNote::where('user_id', auth()->id())
            ->with(['customer', 'invoice']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by customer
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        // Search by credit note number
        if ($request->filled('search')) {
            $query->where('number', 'like', '%' . $request->search . '%');
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $creditNotes = $query->paginate($request->get('per_page', 20));

        // Calculate summary statistics
        $summary = [
            'total_credit_notes' => CreditNote::where('user_id', auth()->id())->count(),
            'total_amount' => CreditNote::where('user_id', auth()->id())->sum('total'),
            'available_credit' => CreditNote::where('user_id', auth()->id())
                ->where('status', CreditNoteStatus::AVAILABLE)
                ->sum('remaining_amount'),
        ];

        // Get customers for filter
        $customers = Customer::where('user_id', auth()->id())
            ->orderBy('name')
            ->get();

        return view('credit-notes.index', compact('creditNotes', 'summary', 'customers'));
    }

    /**
     * Show the form for creating a new credit note.
     */
    public function create(Request $request)
    {
        $customers = Customer::where('user_id', auth()->id())
            ->orderBy('name')
            ->get();

        // Get invoices for selected customer or all customers
        $invoicesQuery = Invoice::where('user_id', auth()->id())
            ->whereIn('status', ['issued', 'partially_paid', 'paid', 'past_due']);

        if ($request->filled('customer_id')) {
            $invoicesQuery->where('customer_id', $request->customer_id);
        }

        $invoices = $invoicesQuery->with('customer')->get();

        // Pre-select customer and invoice if provided
        $selectedCustomerId = $request->get('customer_id');
        $selectedInvoiceId = $request->get('invoice_id');

        return view('credit-notes.create', compact('customers', 'invoices', 'selectedCustomerId', 'selectedInvoiceId'));
    }

    /**
     * Store a newly created credit note.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'invoice_id' => ['nullable', 'exists:invoices,id'],
            'currency' => ['required', 'string', 'size:3', 'in:MKD,USD,EUR,GBP,CAD,AUD,JPY,CHF,RSD,BGN'],
            'amount' => ['required', 'integer', 'min:1'],
            'reason' => ['required', 'string', 'in:product_return,service_cancellation,billing_error,goodwill,duplicate_payment,other'],
            'description' => ['nullable', 'string', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        // Validate customer belongs to user
        $customer = Customer::findOrFail($validated['customer_id']);
        if ($customer->user_id !== auth()->id()) {
            abort(403);
        }

        // Validate invoice if provided
        if (isset($validated['invoice_id'])) {
            $invoice = Invoice::findOrFail($validated['invoice_id']);
            if ($invoice->user_id !== auth()->id() || $invoice->customer_id !== $customer->id) {
                abort(403);
            }
        }

        $validated['user_id'] = auth()->id();
        $validated['status'] = CreditNoteStatus::AVAILABLE;
        $validated['total'] = $validated['amount'];
        $validated['remaining_amount'] = $validated['amount'];

        $creditNote = CreditNote::create($validated);

        // Generate credit note number
        $creditNote->number = $this->generateCreditNoteNumber($creditNote);
        $creditNote->save();

        return redirect()->route('invoicing.credit-notes.show', $creditNote)
            ->with('success', 'Credit note created successfully!');
    }

    /**
     * Display the specified credit note.
     */
    public function show(CreditNote $creditNote)
    {
        // Ensure user owns this credit note
        if ($creditNote->user_id !== auth()->id()) {
            abort(403);
        }

        // Load relationships
        $creditNote->load(['customer', 'invoice', 'applications.invoice']);

        // Get available invoices for applying credit
        $availableInvoices = Invoice::where('user_id', auth()->id())
            ->where('customer_id', $creditNote->customer_id)
            ->where('amount_due', '>', 0)
            ->whereIn('status', ['issued', 'partially_paid', 'past_due'])
            ->with('customer')
            ->get();

        return view('credit-notes.show', compact('creditNote', 'availableInvoices'));
    }

    /**
     * Remove the specified credit note.
     */
    public function destroy(CreditNote $creditNote)
    {
        // Ensure user owns this credit note
        if ($creditNote->user_id !== auth()->id()) {
            abort(403);
        }

        // Can't delete if it has been applied
        if ($creditNote->applications()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Cannot delete credit note that has been applied to invoices.');
        }

        $creditNote->delete();

        return redirect()->route('invoicing.credit-notes.index')
            ->with('success', 'Credit note deleted successfully!');
    }

    /**
     * Apply credit note to an invoice.
     */
    public function apply(Request $request, CreditNote $creditNote)
    {
        // Ensure user owns this credit note
        if ($creditNote->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'invoice_id' => ['required', 'exists:invoices,id'],
            'amount' => ['required', 'integer', 'min:1'],
        ]);

        // Validate invoice
        $invoice = Invoice::findOrFail($validated['invoice_id']);
        if ($invoice->user_id !== auth()->id()) {
            abort(403);
        }

        // Validate amount
        if ($validated['amount'] > $creditNote->remaining_amount) {
            return redirect()->back()
                ->with('error', 'Credit amount cannot exceed remaining credit note amount.')
                ->withInput();
        }

        if ($validated['amount'] > $invoice->amount_due) {
            return redirect()->back()
                ->with('error', 'Credit amount cannot exceed invoice amount due.')
                ->withInput();
        }

        try {
            // Create credit note application
            $application = $creditNote->applications()->create([
                'user_id' => auth()->id(),
                'invoice_id' => $invoice->id,
                'amount' => $validated['amount'],
            ]);

            // Update credit note remaining amount
            $creditNote->remaining_amount -= $validated['amount'];
            if ($creditNote->remaining_amount <= 0) {
                $creditNote->status = CreditNoteStatus::APPLIED;
            }
            $creditNote->save();

            // Record as payment on invoice
            $this->invoicingService->recordPayment($invoice, [
                'user_id' => auth()->id(),
                'invoice_id' => $invoice->id,
                'amount' => $validated['amount'],
                'payment_date' => now()->toDateString(),
                'payment_method' => 'credit_note',
                'reference' => 'Credit Note: ' . $creditNote->number,
                'notes' => 'Applied from credit note ' . $creditNote->number,
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Credit note applied successfully',
                    'creditNote' => $creditNote->fresh(['applications']),
                ]);
            }

            return redirect()->route('invoicing.credit-notes.show', $creditNote)
                ->with('success', 'Credit note applied to invoice successfully!');
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
     * Generate credit note number.
     */
    protected function generateCreditNoteNumber(CreditNote $creditNote): string
    {
        $prefix = config('invoicing.credit_note_prefix', 'CN');
        $year = now()->year;

        // Get the latest credit note number for this year
        $latestCreditNote = CreditNote::where('user_id', auth()->id())
            ->where('number', 'like', $prefix . '-' . $year . '-%')
            ->orderBy('number', 'desc')
            ->first();

        if ($latestCreditNote && preg_match('/' . $prefix . '-' . $year . '-(\d+)/', $latestCreditNote->number, $matches)) {
            $sequence = intval($matches[1]) + 1;
        } else {
            $sequence = 1;
        }

        return sprintf('%s-%s-%06d', $prefix, $year, $sequence);
    }
}
