<?php

namespace App\Http\Controllers;

use App\Models\TaxRate;
use Illuminate\Http\Request;

class TaxRateController extends Controller
{
    /**
     * Display a listing of tax rates.
     */
    public function index(Request $request)
    {
        $query = TaxRate::where('user_id', auth()->id());

        // Filter by active status
        if ($request->filled('active')) {
            $query->where('active', $request->active === '1');
        }

        // Search by name
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $taxRates = $query->paginate($request->get('per_page', 20));

        // Calculate summary statistics
        $summary = [
            'total_tax_rates' => TaxRate::where('user_id', auth()->id())->count(),
            'active_tax_rates' => TaxRate::where('user_id', auth()->id())->where('active', true)->count(),
        ];

        return view('tax-rates.index', compact('taxRates', 'summary'));
    }

    /**
     * Show the form for creating a new tax rate.
     */
    public function create()
    {
        return view('tax-rates.create');
    }

    /**
     * Store a newly created tax rate.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'percentage_basis_points' => ['required', 'integer', 'min:0', 'max:1000000'],
            'country' => ['nullable', 'string', 'max:2'],
            'active' => ['boolean'],
            'valid_from' => ['nullable', 'date'],
            'valid_until' => ['nullable', 'date', 'after_or_equal:valid_from'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $validated['user_id'] = auth()->id();
        $validated['active'] = $request->has('active');

        $taxRate = TaxRate::create($validated);

        return redirect()->route('invoicing.tax-rates.index')
            ->with('success', 'Tax rate created successfully!');
    }

    /**
     * Show the form for editing the tax rate.
     */
    public function edit(TaxRate $taxRate)
    {
        // Ensure user owns this tax rate
        if ($taxRate->user_id !== auth()->id()) {
            abort(403);
        }

        return view('tax-rates.edit', compact('taxRate'));
    }

    /**
     * Update the specified tax rate.
     */
    public function update(Request $request, TaxRate $taxRate)
    {
        // Ensure user owns this tax rate
        if ($taxRate->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'percentage_basis_points' => ['required', 'integer', 'min:0', 'max:1000000'],
            'country' => ['nullable', 'string', 'max:2'],
            'active' => ['boolean'],
            'valid_from' => ['nullable', 'date'],
            'valid_until' => ['nullable', 'date', 'after_or_equal:valid_from'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $validated['active'] = $request->has('active');

        $taxRate->update($validated);

        return redirect()->route('invoicing.tax-rates.index')
            ->with('success', 'Tax rate updated successfully!');
    }

    /**
     * Remove the specified tax rate.
     */
    public function destroy(TaxRate $taxRate)
    {
        // Ensure user owns this tax rate
        if ($taxRate->user_id !== auth()->id()) {
            abort(403);
        }

        // Check if tax rate is being used in any invoice items
        if ($taxRate->invoiceItems()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Cannot delete tax rate that is being used in invoices.');
        }

        $taxRate->delete();

        return redirect()->route('invoicing.tax-rates.index')
            ->with('success', 'Tax rate deleted successfully!');
    }
}
