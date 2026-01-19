<?php

namespace App\Http\Controllers;

use App\Enums\DiscountType;
use App\Models\Discount;
use Illuminate\Http\Request;

class DiscountController extends Controller
{
    /**
     * Display a listing of discounts.
     */
    public function index(Request $request)
    {
        $query = Discount::where('user_id', auth()->id());

        // Filter by active status
        if ($request->filled('active')) {
            $query->where('active', $request->active === '1');
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Search by code
        if ($request->filled('search')) {
            $query->where('code', 'like', '%' . $request->search . '%');
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $discounts = $query->paginate($request->get('per_page', 20));

        // Calculate summary statistics
        $summary = [
            'total_discounts' => Discount::where('user_id', auth()->id())->count(),
            'active_discounts' => Discount::where('user_id', auth()->id())->where('active', true)->count(),
            'total_redemptions' => Discount::where('user_id', auth()->id())->sum('redemptions_count'),
        ];

        return view('discounts.index', compact('discounts', 'summary'));
    }

    /**
     * Show the form for creating a new discount.
     */
    public function create()
    {
        return view('discounts.create');
    }

    /**
     * Store a newly created discount.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:255', 'unique:discounts,code,NULL,id,user_id,' . auth()->id()],
            'type' => ['required', 'string', 'in:percent,fixed'],
            'value' => ['required', 'integer', 'min:0'],
            'active' => ['boolean'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'max_redemptions' => ['nullable', 'integer', 'min:1'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        // Validate percentage value
        if ($validated['type'] === 'percent' && $validated['value'] > 10000) {
            return redirect()->back()
                ->with('error', 'Percentage discount cannot exceed 100% (10000 basis points).')
                ->withInput();
        }

        $validated['user_id'] = auth()->id();
        $validated['active'] = $request->has('active');
        $validated['current_redemptions'] = 0;

        $discount = Discount::create($validated);

        return redirect()->route('invoicing.discounts.index')
            ->with('success', 'Discount created successfully!');
    }

    /**
     * Show the form for editing the discount.
     */
    public function edit(Discount $discount)
    {
        // Ensure user owns this discount
        if ($discount->user_id !== auth()->id()) {
            abort(403);
        }

        return view('discounts.edit', compact('discount'));
    }

    /**
     * Update the specified discount.
     */
    public function update(Request $request, Discount $discount)
    {
        // Ensure user owns this discount
        if ($discount->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:255', 'unique:discounts,code,' . $discount->id . ',id,user_id,' . auth()->id()],
            'type' => ['required', 'string', 'in:percent,fixed'],
            'value' => ['required', 'integer', 'min:0'],
            'active' => ['boolean'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'max_redemptions' => ['nullable', 'integer', 'min:1'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        // Validate percentage value
        if ($validated['type'] === 'percent' && $validated['value'] > 10000) {
            return redirect()->back()
                ->with('error', 'Percentage discount cannot exceed 100% (10000 basis points).')
                ->withInput();
        }

        $validated['active'] = $request->has('active');

        $discount->update($validated);

        return redirect()->route('invoicing.discounts.index')
            ->with('success', 'Discount updated successfully!');
    }

    /**
     * Remove the specified discount.
     */
    public function destroy(Discount $discount)
    {
        // Ensure user owns this discount
        if ($discount->user_id !== auth()->id()) {
            abort(403);
        }

        // Check if discount is being used in any invoice items
        if ($discount->invoiceItems()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Cannot delete discount that is being used in invoices.');
        }

        $discount->delete();

        return redirect()->route('invoicing.discounts.index')
            ->with('success', 'Discount deleted successfully!');
    }
}
