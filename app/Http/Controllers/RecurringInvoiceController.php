<?php

namespace App\Http\Controllers;

use App\Enums\BillingInterval;
use App\Enums\RecurringStatus;
use App\Models\Customer;
use App\Models\Discount;
use App\Models\RecurringInvoice;
use App\Models\TaxRate;
use App\Services\RecurringInvoiceService;
use Illuminate\Http\Request;

class RecurringInvoiceController extends Controller
{
    public function __construct(
        protected RecurringInvoiceService $recurringService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = RecurringInvoice::where('user_id', auth()->id())
            ->with(['customer']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by customer
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        // Search by name
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $recurringInvoices = $query->paginate($request->get('per_page', 20));

        // Calculate summary statistics
        $summary = [
            'total' => RecurringInvoice::where('user_id', auth()->id())->count(),
            'active' => RecurringInvoice::where('user_id', auth()->id())->where('status', RecurringStatus::ACTIVE)->count(),
            'paused' => RecurringInvoice::where('user_id', auth()->id())->where('status', RecurringStatus::PAUSED)->count(),
        ];

        // Get customers for filter
        $customers = Customer::where('user_id', auth()->id())
            ->orderBy('name')
            ->get();

        return view('recurring-invoices.index', compact('recurringInvoices', 'summary', 'customers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $customers = Customer::where('user_id', auth()->id())
            ->orderBy('name')
            ->get();

        $taxRates = TaxRate::where('user_id', auth()->id())
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $discounts = Discount::where('user_id', auth()->id())
            ->where('status', 'active')
            ->orderBy('code')
            ->get();

        // Pre-select customer if provided
        $selectedCustomerId = $request->get('customer_id');

        return view('recurring-invoices.create', compact('customers', 'taxRates', 'discounts', 'selectedCustomerId'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'billing_interval' => ['required', 'string', 'in:daily,weekly,monthly,quarterly,yearly'],
            'interval_count' => ['required', 'integer', 'min:1', 'max:12'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after:start_date'],
            'billing_day_of_month' => ['nullable', 'integer', 'min:1', 'max:31'],
            'occurrences_limit' => ['nullable', 'integer', 'min:1'],
            'currency' => ['required', 'string', 'size:3'],
            'tax_behavior' => ['required', 'in:inclusive,exclusive'],
            'net_terms_days' => ['required', 'integer', 'min:0', 'max:365'],
            'auto_send_email' => ['boolean'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $validated['user_id'] = auth()->id();
        $validated['status'] = RecurringStatus::ACTIVE;
        $validated['next_billing_date'] = $validated['start_date'];
        $validated['occurrences_count'] = 0;

        $recurringInvoice = RecurringInvoice::create($validated);

        return redirect()->route('invoicing.recurring-invoices.show', $recurringInvoice)
            ->with('success', 'Recurring invoice created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(RecurringInvoice $recurringInvoice)
    {
        // Ensure user owns this recurring invoice
        if ($recurringInvoice->user_id !== auth()->id()) {
            abort(403);
        }

        // Load relationships
        $recurringInvoice->load(['customer', 'items.taxRate', 'items.discount', 'generatedInvoices' => function($query) {
            $query->orderBy('created_at', 'desc')->limit(10);
        }]);

        $taxRates = TaxRate::where('user_id', auth()->id())
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $discounts = Discount::where('user_id', auth()->id())
            ->where('status', 'active')
            ->orderBy('code')
            ->get();

        return view('recurring-invoices.show', compact('recurringInvoice', 'taxRates', 'discounts'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(RecurringInvoice $recurringInvoice)
    {
        // Ensure user owns this recurring invoice
        if ($recurringInvoice->user_id !== auth()->id()) {
            abort(403);
        }

        $customers = Customer::where('user_id', auth()->id())
            ->orderBy('name')
            ->get();

        return view('recurring-invoices.edit', compact('recurringInvoice', 'customers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, RecurringInvoice $recurringInvoice)
    {
        // Ensure user owns this recurring invoice
        if ($recurringInvoice->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'billing_interval' => ['required', 'string', 'in:daily,weekly,monthly,quarterly,yearly'],
            'interval_count' => ['required', 'integer', 'min:1', 'max:12'],
            'end_date' => ['nullable', 'date', 'after:start_date'],
            'billing_day_of_month' => ['nullable', 'integer', 'min:1', 'max:31'],
            'occurrences_limit' => ['nullable', 'integer', 'min:1'],
            'currency' => ['required', 'string', 'size:3'],
            'tax_behavior' => ['required', 'in:inclusive,exclusive'],
            'net_terms_days' => ['required', 'integer', 'min:0', 'max:365'],
            'auto_send_email' => ['boolean'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $recurringInvoice->update($validated);

        return redirect()->route('invoicing.recurring-invoices.show', $recurringInvoice)
            ->with('success', 'Recurring invoice updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RecurringInvoice $recurringInvoice)
    {
        // Ensure user owns this recurring invoice
        if ($recurringInvoice->user_id !== auth()->id()) {
            abort(403);
        }

        // Check if has generated invoices
        if ($recurringInvoice->generatedInvoices()->count() > 0) {
            return redirect()->route('invoicing.recurring-invoices.index')
                ->with('error', 'Cannot delete recurring invoice that has generated invoices.');
        }

        $recurringInvoice->delete();

        return redirect()->route('invoicing.recurring-invoices.index')
            ->with('success', 'Recurring invoice deleted successfully!');
    }

    /**
     * Pause recurring invoice
     */
    public function pause(RecurringInvoice $recurringInvoice)
    {
        // Ensure user owns this recurring invoice
        if ($recurringInvoice->user_id !== auth()->id()) {
            abort(403);
        }

        if ($recurringInvoice->status !== RecurringStatus::ACTIVE) {
            return redirect()->back()
                ->with('error', 'Only active recurring invoices can be paused.');
        }

        $recurringInvoice->pause();

        return redirect()->route('invoicing.recurring-invoices.show', $recurringInvoice)
            ->with('success', 'Recurring invoice paused successfully!');
    }

    /**
     * Resume recurring invoice
     */
    public function resume(RecurringInvoice $recurringInvoice)
    {
        // Ensure user owns this recurring invoice
        if ($recurringInvoice->user_id !== auth()->id()) {
            abort(403);
        }

        if ($recurringInvoice->status !== RecurringStatus::PAUSED) {
            return redirect()->back()
                ->with('error', 'Only paused recurring invoices can be resumed.');
        }

        $recurringInvoice->resume();

        return redirect()->route('invoicing.recurring-invoices.show', $recurringInvoice)
            ->with('success', 'Recurring invoice resumed successfully!');
    }

    /**
     * Cancel recurring invoice
     */
    public function cancel(RecurringInvoice $recurringInvoice)
    {
        // Ensure user owns this recurring invoice
        if ($recurringInvoice->user_id !== auth()->id()) {
            abort(403);
        }

        if (!in_array($recurringInvoice->status, [RecurringStatus::ACTIVE, RecurringStatus::PAUSED])) {
            return redirect()->back()
                ->with('error', 'Only active or paused recurring invoices can be cancelled.');
        }

        $recurringInvoice->cancel();

        return redirect()->route('invoicing.recurring-invoices.show', $recurringInvoice)
            ->with('success', 'Recurring invoice cancelled successfully!');
    }

    /**
     * Manually generate invoice from recurring template
     */
    public function generateNow(RecurringInvoice $recurringInvoice)
    {
        // Ensure user owns this recurring invoice
        if ($recurringInvoice->user_id !== auth()->id()) {
            abort(403);
        }

        try {
            $invoice = $this->recurringService->generateInvoice($recurringInvoice);

            return redirect()->route('invoicing.invoices.show', $invoice)
                ->with('success', 'Invoice generated successfully!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to generate invoice: ' . $e->getMessage());
        }
    }
}
