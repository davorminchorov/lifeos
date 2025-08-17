<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSubscriptionRequest;
use App\Http\Requests\UpdateSubscriptionRequest;
use App\Models\Subscription;
use App\Services\CurrencyService;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
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
        $query = Subscription::where('user_id', auth()->id())->with('user');

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by category
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        // Filter subscriptions due soon
        if ($request->has('due_soon')) {
            $days = $request->get('due_soon', 7);
            $query->dueSoon($days);
        }

        // Search by service name
        if ($request->has('search')) {
            $query->where('service_name', 'like', '%'.$request->search.'%');
        }

        // Sort by next billing date by default
        $sortBy = $request->get('sort_by', 'next_billing_date');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        $subscriptions = $query->paginate($request->get('per_page', 15));

        return view('subscriptions.index', compact('subscriptions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('subscriptions.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSubscriptionRequest $request)
    {
        $subscription = Subscription::create([
            'user_id' => auth()->id(),
            ...$request->validated(),
        ]);

        return redirect()->route('subscriptions.show', $subscription)
            ->with('success', 'Subscription created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Subscription $subscription)
    {
        // Ensure the subscription belongs to the authenticated user
        if ($subscription->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to subscription.');
        }

        $subscription->load('user');

        return view('subscriptions.show', compact('subscription'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Subscription $subscription)
    {
        // Ensure the subscription belongs to the authenticated user
        if ($subscription->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to subscription.');
        }

        return view('subscriptions.edit', compact('subscription'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSubscriptionRequest $request, Subscription $subscription)
    {
        // Ensure the subscription belongs to the authenticated user
        if ($subscription->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to subscription.');
        }

        $subscription->update($request->validated());

        return redirect()->route('subscriptions.show', $subscription)
            ->with('success', 'Subscription updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Subscription $subscription)
    {
        // Ensure the subscription belongs to the authenticated user
        if ($subscription->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to subscription.');
        }

        $subscription->delete();

        return redirect()->route('subscriptions.index')
            ->with('success', 'Subscription deleted successfully!');
    }

    /**
     * Cancel a subscription.
     */
    public function cancel(Subscription $subscription)
    {
        // Ensure the subscription belongs to the authenticated user
        if ($subscription->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to subscription.');
        }

        $subscription->update([
            'status' => 'cancelled',
            'cancellation_date' => now(),
        ]);

        return redirect()->route('subscriptions.show', $subscription)
            ->with('success', 'Subscription cancelled successfully!');
    }

    /**
     * Pause a subscription.
     */
    public function pause(Subscription $subscription)
    {
        // Ensure the subscription belongs to the authenticated user
        if ($subscription->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to subscription.');
        }

        $subscription->update(['status' => 'paused']);

        return redirect()->route('subscriptions.show', $subscription)
            ->with('success', 'Subscription paused successfully!');
    }

    /**
     * Resume a paused subscription.
     */
    public function resume(Subscription $subscription)
    {
        // Ensure the subscription belongs to the authenticated user
        if ($subscription->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to subscription.');
        }

        $subscription->update(['status' => 'active']);

        return redirect()->route('subscriptions.show', $subscription)
            ->with('success', 'Subscription resumed successfully!');
    }

    /**
     * Get analytics summary for subscriptions.
     */
    public function analyticsSummary(Request $request)
    {
        $userId = auth()->id();

        // Calculate spending in MKD
        $activeSubscriptions = Subscription::where('user_id', $userId)->where('status', 'active')->get();
        $monthlySpendingMKD = 0;

        foreach ($activeSubscriptions as $subscription) {
            $currency = $subscription->currency ?? config('currency.default', 'MKD');
            $costInMKD = $this->currencyService->convertToDefault($subscription->cost, $currency);

            // Convert to monthly equivalent
            $monthlyCostMKD = match ($subscription->billing_cycle) {
                'monthly' => $costInMKD,
                'yearly' => $costInMKD / 12,
                'weekly' => $costInMKD * 4.33,
                'custom' => $subscription->billing_cycle_days ? ($costInMKD * 30.44) / $subscription->billing_cycle_days : 0,
                default => 0,
            };

            $monthlySpendingMKD += $monthlyCostMKD;
        }

        $data = [
            'total_subscriptions' => Subscription::where('user_id', $userId)->count(),
            'active_subscriptions' => $activeSubscriptions->count(),
            'cancelled_subscriptions' => Subscription::where('user_id', $userId)->where('status', 'cancelled')->count(),
            'paused_subscriptions' => Subscription::where('user_id', $userId)->where('status', 'paused')->count(),
            'monthly_spending' => $monthlySpendingMKD,
            'yearly_spending' => $monthlySpendingMKD * 12,
            'due_soon' => Subscription::where('user_id', $userId)
                ->dueSoon(7)
                ->count(),
        ];

        return response()->json(['data' => $data]);
    }

    /**
     * Get spending analytics for subscriptions.
     */
    public function spendingAnalytics(Request $request)
    {
        $userId = auth()->id();

        $subscriptions = Subscription::where('user_id', $userId)
            ->where('status', 'active')
            ->get();

        // Convert all costs to MKD and calculate monthly equivalents
        $subscriptionsWithMKD = $subscriptions->map(function ($subscription) {
            $currency = $subscription->currency ?? config('currency.default', 'MKD');
            $costInMKD = $this->currencyService->convertToDefault($subscription->cost, $currency);

            $monthlyCostMKD = match ($subscription->billing_cycle) {
                'monthly' => $costInMKD,
                'yearly' => $costInMKD / 12,
                'weekly' => $costInMKD * 4.33,
                'custom' => $subscription->billing_cycle_days ? ($costInMKD * 30.44) / $subscription->billing_cycle_days : 0,
                default => 0,
            };

            $subscription->cost_mkd = $costInMKD;
            $subscription->monthly_cost_mkd = $monthlyCostMKD;
            $subscription->yearly_cost_mkd = $monthlyCostMKD * 12;

            return $subscription;
        });

        $data = [
            'monthly_breakdown' => $subscriptionsWithMKD->groupBy('billing_cycle')->map(function ($group, $cycle) {
                return [
                    'count' => $group->count(),
                    'total_cost' => $group->sum('cost_mkd'),
                    'monthly_equivalent' => $group->sum('monthly_cost_mkd'),
                ];
            }),
            'spending_trend' => [
                'current_month' => $subscriptionsWithMKD->sum('monthly_cost_mkd'),
                'projected_year' => $subscriptionsWithMKD->sum('yearly_cost_mkd'),
            ],
            'top_expenses' => $subscriptionsWithMKD->sortByDesc('monthly_cost_mkd')->take(5)->map(function ($subscription) {
                return [
                    'id' => $subscription->id,
                    'service_name' => $subscription->service_name,
                    'category' => $subscription->category,
                    'monthly_cost' => $subscription->monthly_cost_mkd,
                    'billing_cycle' => $subscription->billing_cycle,
                ];
            })->values(),
        ];

        return response()->json(['data' => $data]);
    }

    /**
     * Get category breakdown for subscriptions.
     */
    public function categoryBreakdown(Request $request)
    {
        $userId = auth()->id();

        $subscriptions = Subscription::where('user_id', $userId)
            ->where('status', 'active')
            ->get();

        // Convert all costs to MKD first
        $subscriptionsWithMKD = $subscriptions->map(function ($subscription) {
            $currency = $subscription->currency ?? config('currency.default', 'MKD');
            $costInMKD = $this->currencyService->convertToDefault($subscription->cost, $currency);

            $monthlyCostMKD = match ($subscription->billing_cycle) {
                'monthly' => $costInMKD,
                'yearly' => $costInMKD / 12,
                'weekly' => $costInMKD * 4.33,
                'custom' => $subscription->billing_cycle_days ? ($costInMKD * 30.44) / $subscription->billing_cycle_days : 0,
                default => 0,
            };

            $subscription->monthly_cost_mkd = $monthlyCostMKD;
            $subscription->yearly_cost_mkd = $monthlyCostMKD * 12;

            return $subscription;
        });

        $categories = $subscriptionsWithMKD
            ->groupBy('category')
            ->map(function ($group, $category) {
                return [
                    'category' => $category,
                    'count' => $group->count(),
                    'monthly_cost' => $group->sum('monthly_cost_mkd'),
                    'yearly_cost' => $group->sum('yearly_cost_mkd'),
                    'percentage' => 0, // Will be calculated below
                ];
            });

        $totalMonthly = $categories->sum('monthly_cost');

        $data = $categories->map(function ($item) use ($totalMonthly) {
            $item['percentage'] = $totalMonthly > 0 ? round(($item['monthly_cost'] / $totalMonthly) * 100, 2) : 0;

            return $item;
        })->values(); // Convert to indexed array for JSON

        return response()->json(['data' => $data]);
    }
}
