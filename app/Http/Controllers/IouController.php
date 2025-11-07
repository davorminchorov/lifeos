<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreIouRequest;
use App\Http\Requests\UpdateIouRequest;
use App\Models\Iou;
use App\Services\CurrencyService;
use Illuminate\Http\Request;

class IouController extends Controller
{
    protected CurrencyService $currencyService;

    public function __construct(CurrencyService $currencyService)
    {
        $this->currencyService = $currencyService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Iou::where('user_id', auth()->id());

        // Filter by type (owe or owed)
        if ($request->filled('type')) {
            if ($request->type === 'owe') {
                $query->owe();
            } elseif ($request->type === 'owed') {
                $query->owed();
            }
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by person
        if ($request->filled('person')) {
            $query->byPerson($request->person);
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Filter overdue
        if ($request->has('overdue')) {
            $query->overdue();
        }

        // Search by description or person name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', '%'.$search.'%')
                    ->orWhere('person_name', 'like', '%'.$search.'%')
                    ->orWhere('notes', 'like', '%'.$search.'%');
            });
        }

        // Sort
        $sortBy = $request->get('sort_by', 'transaction_date');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $ious = $query->paginate($request->get('per_page', 15));

        // Calculate summary statistics
        $summary = [
            'total_owe' => Iou::where('user_id', auth()->id())
                ->owe()
                ->whereIn('status', ['pending', 'partially_paid'])
                ->get()
                ->sum(function ($iou) {
                    return $this->currencyService->convertToDefault($iou->remaining_balance, $iou->currency);
                }),
            'total_owed' => Iou::where('user_id', auth()->id())
                ->owed()
                ->whereIn('status', ['pending', 'partially_paid'])
                ->get()
                ->sum(function ($iou) {
                    return $this->currencyService->convertToDefault($iou->remaining_balance, $iou->currency);
                }),
            'overdue_count' => Iou::where('user_id', auth()->id())->overdue()->count(),
        ];

        return view('ious.index', compact('ious', 'summary'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('ious.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreIouRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = auth()->id();

        // Set default values if not provided
        if (! isset($data['status'])) {
            $data['status'] = 'pending';
        }

        if (! isset($data['amount_paid'])) {
            $data['amount_paid'] = 0;
        }

        if (! isset($data['currency'])) {
            $data['currency'] = 'MKD';
        }

        $iou = Iou::create($data);

        return redirect()->route('ious.show', $iou)
            ->with('success', 'IOU created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Iou $iou)
    {
        // Ensure user owns this IOU
        if ($iou->user_id !== auth()->id()) {
            abort(403);
        }

        return view('ious.show', compact('iou'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Iou $iou)
    {
        // Ensure user owns this IOU
        if ($iou->user_id !== auth()->id()) {
            abort(403);
        }

        return view('ious.edit', compact('iou'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateIouRequest $request, Iou $iou)
    {
        $iou->update($request->validated());

        return redirect()->route('ious.show', $iou)
            ->with('success', 'IOU updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Iou $iou)
    {
        // Ensure user owns this IOU
        if ($iou->user_id !== auth()->id()) {
            abort(403);
        }

        $iou->delete();

        return redirect()->route('ious.index')
            ->with('success', 'IOU deleted successfully!');
    }

    /**
     * Record a payment towards the IOU.
     */
    public function recordPayment(Request $request, Iou $iou)
    {
        // Ensure user owns this IOU
        if ($iou->user_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'payment_amount' => 'required|numeric|min:0.01|max:'.($iou->amount - $iou->amount_paid),
            'payment_date' => 'required|date',
            'payment_method' => 'nullable|string|max:255',
        ]);

        $newAmountPaid = $iou->amount_paid + $request->payment_amount;

        // Update status based on payment
        $status = $iou->status;
        if ($newAmountPaid >= $iou->amount) {
            $status = 'paid';
            $newAmountPaid = $iou->amount; // Cap at total amount
        } elseif ($newAmountPaid > 0) {
            $status = 'partially_paid';
        }

        $iou->update([
            'amount_paid' => $newAmountPaid,
            'status' => $status,
            'payment_method' => $request->payment_method ?? $iou->payment_method,
        ]);

        return redirect()->route('ious.show', $iou)
            ->with('success', 'Payment recorded successfully!');
    }

    /**
     * Mark IOU as paid.
     */
    public function markPaid(Iou $iou)
    {
        // Ensure user owns this IOU
        if ($iou->user_id !== auth()->id()) {
            abort(403);
        }

        $iou->update([
            'amount_paid' => $iou->amount,
            'status' => 'paid',
        ]);

        return redirect()->route('ious.show', $iou)
            ->with('success', 'IOU marked as paid!');
    }

    /**
     * Cancel an IOU.
     */
    public function cancel(Iou $iou)
    {
        // Ensure user owns this IOU
        if ($iou->user_id !== auth()->id()) {
            abort(403);
        }

        $iou->update([
            'status' => 'cancelled',
        ]);

        return redirect()->route('ious.show', $iou)
            ->with('success', 'IOU cancelled successfully!');
    }
}
