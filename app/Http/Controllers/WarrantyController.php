<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWarrantyRequest;
use App\Http\Requests\UpdateWarrantyRequest;
use App\Models\Warranty;
use App\Services\CurrencyService;
use Illuminate\Http\Request;

class WarrantyController extends Controller
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
        $query = Warranty::query()->with('user');

        // Filter by current status
        if ($request->filled('current_status')) {
            $query->where('current_status', $request->current_status);
        }

        // Filter by warranty type
        if ($request->filled('warranty_type')) {
            $query->where('warranty_type', $request->warranty_type);
        }

        // Filter by brand
        if ($request->filled('brand')) {
            $query->where('brand', $request->brand);
        }

        // Filter warranties expiring soon
        if ($request->has('expiring_soon')) {
            $days = $request->get('expiring_soon', 30);
            $query->expiringSoon($days);
        }

        // Filter expired warranties
        if ($request->has('expired')) {
            $query->expired();
        }

        // Search by product name or brand
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('product_name', 'like', '%'.$search.'%')
                    ->orWhere('brand', 'like', '%'.$search.'%')
                    ->orWhere('model', 'like', '%'.$search.'%');
            });
        }

        // Sort by warranty expiration date by default
        $sortBy = $request->get('sort_by', 'warranty_expiration_date');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        $warranties = $query->paginate($request->get('per_page', 15));

        return view('warranties.index', compact('warranties'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('warranties.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreWarrantyRequest $request)
    {
        $warranty = Warranty::create([
            'user_id' => auth()->id(),
            ...$request->validated(),
        ]);

        return redirect()->route('warranties.show', $warranty)
            ->with('success', 'Warranty created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Warranty $warranty)
    {
        $warranty->load('user');

        return view('warranties.show', compact('warranty'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Warranty $warranty)
    {
        return view('warranties.edit', compact('warranty'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateWarrantyRequest $request, Warranty $warranty)
    {
        $warranty->update($request->validated());

        return redirect()->route('warranties.show', $warranty)
            ->with('success', 'Warranty updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Warranty $warranty)
    {
        $warranty->delete();

        return redirect()->route('warranties.index')
            ->with('success', 'Warranty deleted successfully!');
    }

    /**
     * File a warranty claim.
     */
    public function fileClaim(Request $request, Warranty $warranty)
    {
        $request->validate([
            'issue_description' => 'required|string|max:1000',
            'claim_date' => 'nullable|date',
        ]);

        $claimHistory = $warranty->claim_history ?? [];
        $claimHistory[] = [
            'date' => $request->get('claim_date', now()->toDateString()),
            'issue' => $request->issue_description,
            'status' => 'filed',
            'resolution' => null,
        ];

        $warranty->update([
            'claim_history' => $claimHistory,
            'current_status' => 'claimed',
        ]);

        return redirect()->route('warranties.show', $warranty)
            ->with('success', 'Warranty claim filed successfully!');
    }

    /**
     * Update a warranty claim.
     */
    public function updateClaim(Request $request, Warranty $warranty)
    {
        $request->validate([
            'claim_index' => 'required|integer|min:0',
            'resolution' => 'required|string|max:1000',
            'status' => 'required|in:filed,in_progress,resolved,denied',
        ]);

        $claimHistory = $warranty->claim_history ?? [];
        $claimIndex = $request->claim_index;

        if (! isset($claimHistory[$claimIndex])) {
            return back()->withErrors(['error' => 'Claim not found.']);
        }

        $claimHistory[$claimIndex]['resolution'] = $request->resolution;
        $claimHistory[$claimIndex]['status'] = $request->status;
        $claimHistory[$claimIndex]['updated_at'] = now()->toDateString();

        $warranty->update(['claim_history' => $claimHistory]);

        return redirect()->route('warranties.show', $warranty)
            ->with('success', 'Warranty claim updated successfully!');
    }

    /**
     * Transfer warranty ownership.
     */
    public function transfer(Request $request, Warranty $warranty)
    {
        $request->validate([
            'new_owner_name' => 'required|string|max:255',
            'transfer_reason' => 'nullable|string|max:500',
            'transfer_date' => 'nullable|date',
        ]);

        $transferHistory = $warranty->transfer_history ?? [];
        $transferHistory[] = [
            'date' => $request->get('transfer_date', now()->toDateString()),
            'to' => $request->new_owner_name,
            'reason' => $request->transfer_reason,
            'from_user_id' => auth()->id(),
        ];

        $warranty->update([
            'transfer_history' => $transferHistory,
            'current_status' => 'transferred',
        ]);

        return redirect()->route('warranties.show', $warranty)
            ->with('success', 'Warranty transferred successfully!');
    }

    /**
     * Add maintenance reminder.
     */
    public function addMaintenanceReminder(Request $request, Warranty $warranty)
    {
        $request->validate([
            'reminder_type' => 'required|string|max:255',
            'frequency' => 'required|string|max:100',
            'next_due_date' => 'nullable|date',
        ]);

        $maintenanceReminders = $warranty->maintenance_reminders ?? [];
        $maintenanceReminders[] = [
            'type' => $request->reminder_type,
            'frequency' => $request->frequency,
            'next_due' => $request->get('next_due_date', now()->addMonth()->toDateString()),
            'created_at' => now()->toDateString(),
        ];

        $warranty->update(['maintenance_reminders' => $maintenanceReminders]);

        return redirect()->route('warranties.show', $warranty)
            ->with('success', 'Maintenance reminder added successfully!');
    }

    /**
     * Create warranty claim (alias for fileClaim to match API route).
     */
    public function createClaim(Request $request, Warranty $warranty)
    {
        return $this->fileClaim($request, $warranty);
    }

    /**
     * Get analytics summary for warranties.
     */
    public function analyticsSummary(Request $request)
    {
        $userId = auth()->id();

        // Get warranties and convert purchase prices to MKD
        $warranties = Warranty::where('user_id', $userId)
            ->whereNotNull('purchase_price')
            ->get();

        $totalPurchaseValueMKD = 0;
        foreach ($warranties as $warranty) {
            $currency = $warranty->currency ?? config('currency.default', 'MKD');
            $priceInMKD = $this->currencyService->convertToDefault($warranty->purchase_price, $currency);
            $totalPurchaseValueMKD += $priceInMKD;
        }

        $summary = [
            'total_warranties' => Warranty::where('user_id', $userId)->count(),
            'active_warranties' => Warranty::where('user_id', $userId)->where('current_status', 'active')->count(),
            'expired_warranties' => Warranty::where('user_id', $userId)->where('current_status', 'expired')->count(),
            'claimed_warranties' => Warranty::where('user_id', $userId)->where('current_status', 'claimed')->count(),
            'transferred_warranties' => Warranty::where('user_id', $userId)->where('current_status', 'transferred')->count(),
            'expiring_soon' => Warranty::where('user_id', $userId)
                ->where('warranty_expiration_date', '<=', now()->addDays(30))
                ->where('current_status', 'active')
                ->count(),
            'total_purchase_value' => $totalPurchaseValueMKD,
        ];

        return response()->json(['data' => $summary]);
    }

    /**
     * Get expiring warranties analytics.
     */
    public function expiringAnalytics(Request $request)
    {
        $userId = auth()->id();

        $expiringWarranties = Warranty::where('user_id', $userId)
            ->where('warranty_expiration_date', '>=', now())
            ->where('warranty_expiration_date', '<=', now()->addDays(90))
            ->orderBy('warranty_expiration_date', 'asc')
            ->get();

        $analytics = [
            'expiring_30_days' => $expiringWarranties->filter(function ($w) {
                return $w->warranty_expiration_date <= now()->addDays(30);
            })->count(),
            'expiring_60_days' => $expiringWarranties->filter(function ($w) {
                return $w->warranty_expiration_date <= now()->addDays(60) &&
                       $w->warranty_expiration_date > now()->addDays(30);
            })->count(),
            'expiring_90_days' => $expiringWarranties->filter(function ($w) {
                return $w->warranty_expiration_date <= now()->addDays(90) &&
                       $w->warranty_expiration_date > now()->addDays(60);
            })->count(),
            'warranties_by_type' => $expiringWarranties->groupBy('warranty_type')->map->count(),
            'high_value_expiring' => $expiringWarranties->where('purchase_price', '>', 500)->count(),
        ];

        return response()->json(['data' => $analytics]);
    }

    /**
     * Get claims analytics for warranties.
     */
    public function claimsAnalytics(Request $request)
    {
        $userId = auth()->id();

        $warranties = Warranty::where('user_id', $userId)
            ->whereNotNull('claim_history')
            ->get();

        $allClaims = $warranties->pluck('claim_history')->flatten(1);

        $analytics = [
            'total_claims' => $allClaims->count(),
            'claims_by_status' => $allClaims->groupBy('status')->map->count(),
            'successful_claims' => $allClaims->where('status', 'resolved')->count(),
            'denied_claims' => $allClaims->where('status', 'denied')->count(),
            'pending_claims' => $allClaims->whereIn('status', ['filed', 'in_progress'])->count(),
            'success_rate' => $allClaims->count() > 0 ?
                round(($allClaims->where('status', 'resolved')->count() / $allClaims->count()) * 100, 2) : 0,
            'most_claimed_products' => $warranties->filter(function ($w) {
                return count($w->claim_history ?? []) > 0;
            })->sortByDesc(function ($w) {
                return count($w->claim_history ?? []);
            })->take(5)->values(),
        ];

        return response()->json(['data' => $analytics]);
    }
}
