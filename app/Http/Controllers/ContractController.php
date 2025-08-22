<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContractRequest;
use App\Http\Requests\UpdateContractRequest;
use App\Models\Contract;
use App\Services\CurrencyService;
use Illuminate\Http\Request;

class ContractController extends Controller
{
    protected CurrencyService $currencyService;

    public function __construct(CurrencyService $currencyService)
    {
        // Middleware is now handled in bootstrap/app.php or route definitions
        $this->currencyService = $currencyService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Contract::query()->where('user_id', auth()->id())->with('user');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by contract type
        if ($request->filled('contract_type')) {
            $query->where('contract_type', $request->contract_type);
        }

        // Filter contracts expiring soon
        if ($request->has('expiring_soon')) {
            $days = $request->get('expiring_soon', 30);
            $query->expiringSoon($days);
        }

        // Filter contracts requiring notice
        if ($request->has('requiring_notice')) {
            $query->requiringNotice();
        }

        // Search by title or counterparty
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%'.$search.'%')
                    ->orWhere('counterparty', 'like', '%'.$search.'%');
            });
        }

        // Sort by end date by default
        $sortBy = $request->get('sort_by', 'end_date');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        $contracts = $query->paginate($request->get('per_page', 15));

        if ($request->expectsJson()) {
            return ContractResource::collection($contracts);
        }

        return view('contracts.index', compact('contracts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('contracts.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreContractRequest $request)
    {
        $contract = Contract::create([
            'user_id' => auth()->id(),
            ...$request->validated(),
        ]);

        if ($request->expectsJson()) {
            return new ContractResource($contract);
        }

        return redirect()->route('contracts.show', $contract)
            ->with('success', 'Contract created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Contract $contract)
    {
        // Ensure the contract belongs to the authenticated user
        if ($contract->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to contract.');
        }

        $contract->load('user');

        if (request()->expectsJson()) {
            return new ContractResource($contract);
        }

        return view('contracts.show', compact('contract'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Contract $contract)
    {
        // Ensure the contract belongs to the authenticated user
        if ($contract->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to contract.');
        }

        return view('contracts.edit', compact('contract'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateContractRequest $request, Contract $contract)
    {
        // Ensure the contract belongs to the authenticated user
        if ($contract->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to contract.');
        }

        $contract->update($request->validated());

        if ($request->expectsJson()) {
            return new ContractResource($contract);
        }

        return redirect()->route('contracts.show', $contract)
            ->with('success', 'Contract updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Contract $contract)
    {
        // Ensure the contract belongs to the authenticated user
        if ($contract->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to contract.');
        }

        $contract->delete();

        if (request()->expectsJson()) {
            return response()->json(['message' => 'Contract deleted successfully']);
        }

        return redirect()->route('contracts.index')
            ->with('success', 'Contract deleted successfully!');
    }

    /**
     * Terminate a contract.
     */
    public function terminate(Contract $contract)
    {
        // Ensure the contract belongs to the authenticated user
        if ($contract->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to contract.');
        }

        $contract->update([
            'status' => 'terminated',
            'end_date' => now(),
        ]);

        if (request()->expectsJson()) {
            return new ContractResource($contract);
        }

        return redirect()->route('contracts.show', $contract)
            ->with('success', 'Contract terminated successfully!');
    }

    /**
     * Renew a contract.
     */
    public function renew(Request $request, Contract $contract)
    {
        // Ensure the contract belongs to the authenticated user
        if ($contract->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to contract.');
        }

        $request->validate([
            'new_end_date' => 'required|date|after:today',
        ]);

        // Add renewal to history
        $renewalHistory = $contract->renewal_history ?? [];
        $renewalHistory[] = [
            'date' => now()->toDateString(),
            'previous_end_date' => $contract->end_date?->toDateString(),
            'new_end_date' => $request->new_end_date,
            'action' => 'renewed',
        ];

        $contract->update([
            'end_date' => $request->new_end_date,
            'status' => 'active',
            'renewal_history' => $renewalHistory,
        ]);

        if ($request->expectsJson()) {
            return new ContractResource($contract);
        }

        return redirect()->route('contracts.show', $contract)
            ->with('success', 'Contract renewed successfully!');
    }

    /**
     * Add amendment to a contract.
     */
    public function addAmendment(Request $request, Contract $contract)
    {
        // Ensure the contract belongs to the authenticated user
        if ($contract->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to contract.');
        }

        $request->validate([
            'amendment_description' => 'required|string|max:1000',
        ]);

        $amendments = $contract->amendments ?? [];
        $amendments[] = [
            'date' => now()->toDateString(),
            'description' => $request->amendment_description,
        ];

        $contract->update(['amendments' => $amendments]);

        if ($request->expectsJson()) {
            return new ContractResource($contract);
        }

        return redirect()->route('contracts.show', $contract)
            ->with('success', 'Amendment added successfully!');
    }

    /**
     * Get analytics summary for contracts.
     */
    public function analyticsSummary(Request $request)
    {
        $userId = auth()->id();

        // Get active contracts and convert values to MKD
        $activeContracts = Contract::where('user_id', $userId)
            ->where('status', 'active')
            ->whereNotNull('contract_value')
            ->get();

        $totalContractValueMKD = 0;
        foreach ($activeContracts as $contract) {
            $currency = $contract->currency ?? config('currency.default', 'MKD');
            $valueInMKD = $this->currencyService->convertToDefault($contract->contract_value, $currency);
            $totalContractValueMKD += $valueInMKD;
        }

        $summary = [
            'total_contracts' => Contract::where('user_id', $userId)->count(),
            'active_contracts' => Contract::where('user_id', $userId)->where('status', 'active')->count(),
            'terminated_contracts' => Contract::where('user_id', $userId)->where('status', 'terminated')->count(),
            'expired_contracts' => Contract::where('user_id', $userId)->where('status', 'expired')->count(),
            'total_contract_value' => $totalContractValueMKD,
            'expiring_soon' => Contract::where('user_id', $userId)
                ->expiringSoon(30)
                ->count(),
            'requiring_notice' => Contract::where('user_id', $userId)
                ->whereNotNull('notice_period_days')
                ->whereNotNull('end_date')
                ->where('status', 'active')
                ->where('end_date', '<=', now()->addDays(60))
                ->count(),
        ];

        return response()->json(['data' => $summary]);
    }

    /**
     * Get expiring contracts analytics.
     */
    public function expiringAnalytics(Request $request)
    {
        $userId = auth()->id();

        $expiringContracts = Contract::where('user_id', $userId)
            ->expiringSoon(90)
            ->orderBy('end_date', 'asc')
            ->get();

        $analytics = [
            'expiring_30_days' => $expiringContracts->filter(fn ($c) => $c->days_until_expiration <= 30)->count(),
            'expiring_60_days' => $expiringContracts->filter(fn ($c) => $c->days_until_expiration <= 60 && $c->days_until_expiration > 30)->count(),
            'expiring_90_days' => $expiringContracts->filter(fn ($c) => $c->days_until_expiration <= 90 && $c->days_until_expiration > 60)->count(),
            'contracts_by_type' => $expiringContracts->groupBy('contract_type')->map->count(),
            'upcoming_renewals' => $expiringContracts->where('auto_renewal', true)->count(),
            'manual_renewals_needed' => $expiringContracts->where('auto_renewal', false)->count(),
        ];

        return response()->json(['data' => $analytics]);
    }

    /**
     * Get performance analytics for contracts.
     */
    public function performanceAnalytics(Request $request)
    {
        $userId = auth()->id();

        $contracts = Contract::where('user_id', $userId)
            ->whereNotNull('performance_rating')
            ->get();

        $analytics = [
            'average_performance' => $contracts->avg('performance_rating'),
            'performance_distribution' => $contracts->groupBy('performance_rating')->map->count(),
            'top_performers' => $contracts->where('performance_rating', '>=', 4)->sortByDesc('performance_rating')->take(5)->values(),
            'poor_performers' => $contracts->where('performance_rating', '<=', 2)->sortBy('performance_rating')->take(5)->values(),
            'performance_by_type' => $contracts->groupBy('contract_type')->map(function ($group) {
                return [
                    'count' => $group->count(),
                    'average_rating' => round($group->avg('performance_rating'), 2),
                ];
            }),
        ];

        return response()->json(['data' => $analytics]);
    }
}
