<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSubscriptionRequest;
use App\Http\Requests\UpdateSubscriptionRequest;
use App\Http\Resources\SubscriptionResource;
use App\Models\Subscription;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Subscription::query()->with('user');

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
            $query->where('service_name', 'like', '%' . $request->search . '%');
        }

        // Sort by next billing date by default
        $sortBy = $request->get('sort_by', 'next_billing_date');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        $subscriptions = $query->paginate($request->get('per_page', 15));

        if ($request->expectsJson()) {
            return SubscriptionResource::collection($subscriptions);
        }

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

        if ($request->expectsJson()) {
            return new SubscriptionResource($subscription);
        }

        return redirect()->route('subscriptions.show', $subscription)
            ->with('success', 'Subscription created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Subscription $subscription)
    {
        $subscription->load('user');

        if (request()->expectsJson()) {
            return new SubscriptionResource($subscription);
        }

        return view('subscriptions.show', compact('subscription'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Subscription $subscription)
    {
        return view('subscriptions.edit', compact('subscription'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSubscriptionRequest $request, Subscription $subscription)
    {
        $subscription->update($request->validated());

        if ($request->expectsJson()) {
            return new SubscriptionResource($subscription);
        }

        return redirect()->route('subscriptions.show', $subscription)
            ->with('success', 'Subscription updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Subscription $subscription)
    {
        $subscription->delete();

        if (request()->expectsJson()) {
            return response()->json(['message' => 'Subscription deleted successfully']);
        }

        return redirect()->route('subscriptions.index')
            ->with('success', 'Subscription deleted successfully!');
    }

    /**
     * Cancel a subscription.
     */
    public function cancel(Subscription $subscription)
    {
        $subscription->update([
            'status' => 'cancelled',
            'cancellation_date' => now(),
        ]);

        if (request()->expectsJson()) {
            return new SubscriptionResource($subscription);
        }

        return redirect()->route('subscriptions.show', $subscription)
            ->with('success', 'Subscription cancelled successfully!');
    }

    /**
     * Pause a subscription.
     */
    public function pause(Subscription $subscription)
    {
        $subscription->update(['status' => 'paused']);

        if (request()->expectsJson()) {
            return new SubscriptionResource($subscription);
        }

        return redirect()->route('subscriptions.show', $subscription)
            ->with('success', 'Subscription paused successfully!');
    }

    /**
     * Resume a paused subscription.
     */
    public function resume(Subscription $subscription)
    {
        $subscription->update(['status' => 'active']);

        if (request()->expectsJson()) {
            return new SubscriptionResource($subscription);
        }

        return redirect()->route('subscriptions.show', $subscription)
            ->with('success', 'Subscription resumed successfully!');
    }

    /**
     * Get analytics summary for subscriptions.
     */
    public function analyticsSummary(Request $request)
    {
        $userId = auth()->id();

        $summary = [
            'total_subscriptions' => Subscription::where('user_id', $userId)->count(),
            'active_subscriptions' => Subscription::where('user_id', $userId)->where('status', 'active')->count(),
            'cancelled_subscriptions' => Subscription::where('user_id', $userId)->where('status', 'cancelled')->count(),
            'paused_subscriptions' => Subscription::where('user_id', $userId)->where('status', 'paused')->count(),
            'monthly_spending' => Subscription::where('user_id', $userId)
                ->where('status', 'active')
                ->get()
                ->sum('monthly_cost'),
            'yearly_spending' => Subscription::where('user_id', $userId)
                ->where('status', 'active')
                ->get()
                ->sum('yearly_cost'),
            'due_soon' => Subscription::where('user_id', $userId)
                ->dueSoon(7)
                ->count(),
        ];

        return response()->json(['data' => $summary]);
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

        $analytics = [
            'monthly_breakdown' => $subscriptions->groupBy('billing_cycle')->map(function ($group, $cycle) {
                return [
                    'count' => $group->count(),
                    'total_cost' => $group->sum('cost'),
                    'monthly_equivalent' => $group->sum('monthly_cost'),
                ];
            }),
            'spending_trend' => [
                'current_month' => $subscriptions->sum('monthly_cost'),
                'projected_year' => $subscriptions->sum('yearly_cost'),
            ],
            'top_expenses' => $subscriptions->sortByDesc('monthly_cost')->take(5)->values(),
        ];

        return response()->json(['data' => $analytics]);
    }

    /**
     * Get category breakdown for subscriptions.
     */
    public function categoryBreakdown(Request $request)
    {
        $userId = auth()->id();

        $categories = Subscription::where('user_id', $userId)
            ->where('status', 'active')
            ->get()
            ->groupBy('category')
            ->map(function ($group, $category) {
                return [
                    'category' => $category,
                    'count' => $group->count(),
                    'monthly_cost' => $group->sum('monthly_cost'),
                    'yearly_cost' => $group->sum('yearly_cost'),
                    'percentage' => 0, // Will be calculated below
                ];
            });

        $totalMonthly = $categories->sum('monthly_cost');

        $categories = $categories->map(function ($item) use ($totalMonthly) {
            $item['percentage'] = $totalMonthly > 0 ? round(($item['monthly_cost'] / $totalMonthly) * 100, 2) : 0;
            return $item;
        });

        return response()->json(['data' => $categories->values()]);
    }
}
