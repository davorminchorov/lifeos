<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectInvestmentRequest;
use App\Http\Requests\UpdateProjectInvestmentRequest;
use App\Models\ProjectInvestment;
use App\Services\CurrencyService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProjectInvestmentController extends Controller
{
    /**
     * Allowed columns for sorting.
     */
    private const ALLOWED_SORT_COLUMNS = ['start_date', 'name', 'current_value', 'created_at', 'status', 'stage'];

    private const ALLOWED_SORT_ORDERS = ['asc', 'desc'];

    /**
     * Authorize that the user owns the project investment.
     */
    private function authorizeOwnership(ProjectInvestment $projectInvestment): void
    {
        if ($projectInvestment->user_id !== auth()->id()) {
            abort(403);
        }
    }

    /**
     * Resolve the currency used for portfolio-wide aggregates (the tenant's default currency).
     */
    private function portfolioCurrency(): string
    {
        return app(CurrencyService::class)->getTenantDefaultCurrency(auth()->user()?->currentTenant);
    }

    /**
     * Display a listing of project investments.
     */
    public function index(Request $request)
    {
        $query = ProjectInvestment::query()
            ->where('user_id', auth()->id());

        // Filter by stage
        if ($request->filled('stage')) {
            $query->byStage($request->stage);
        }

        // Filter by business model
        if ($request->filled('business_model')) {
            $query->byBusinessModel($request->business_model);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search by name or project type
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%')
                    ->orWhere('project_type', 'like', '%'.$search.'%');
            });
        }

        // Validate and apply sorting
        $sortBy = $request->get('sort_by', 'start_date');
        $sortOrder = $request->get('sort_order', 'desc');

        // Validate sort parameters against allowlist
        if (! in_array($sortBy, self::ALLOWED_SORT_COLUMNS, true)) {
            $sortBy = 'start_date';
        }
        if (! in_array($sortOrder, self::ALLOWED_SORT_ORDERS, true)) {
            $sortOrder = 'desc';
        }

        $query->orderBy($sortBy, $sortOrder);

        // Eager load transactions for display
        $query->with('transactions');

        $projectInvestments = $query->paginate($request->get('per_page', 15));

        // Expose each row's own primary currency and converted total so list
        // figures show the correct currency per project.
        $projectInvestments->through(fn (ProjectInvestment $project) => $project->append(['primary_currency', 'total_invested']));

        // Portfolio-wide summary, converted into the tenant's default currency.
        $currency = $this->portfolioCurrency();

        $allProjects = ProjectInvestment::where('user_id', auth()->id())
            ->with('transactions')
            ->get();

        $totalInvested = $allProjects->sum(fn (ProjectInvestment $project) => $project->totalInvestedIn($currency));
        $totalCurrentValue = $allProjects->sum(fn (ProjectInvestment $project) => $project->currentValueIn($currency));

        $summaryData = [
            'total_projects' => $allProjects->count(),
            'active_projects' => $allProjects->where('status', 'active')->count(),
            'total_invested' => round($totalInvested, 2),
            'total_current_value' => round($totalCurrentValue, 2),
            'total_gain_loss' => round($totalCurrentValue - $totalInvested, 2),
            'currency' => $currency,
        ];

        return Inertia::render('ProjectInvestments/Index', [
            'projectInvestments' => $projectInvestments,
            'summary' => $summaryData,
            'filters' => $request->only(['search', 'stage', 'business_model', 'status']),
        ]);
    }

    /**
     * Show the form for creating a new project investment.
     */
    public function create()
    {
        return Inertia::render('ProjectInvestments/Create');
    }

    /**
     * Store a newly created project investment.
     */
    public function store(StoreProjectInvestmentRequest $request)
    {
        $validated = $request->validated();

        // Extract investment details for the initial transaction
        $investmentAmount = $validated['investment_amount'];
        $currency = $validated['currency'] ?? 'USD';

        // Remove investment_amount and currency from project data
        unset($validated['investment_amount'], $validated['currency']);

        // Create the project investment
        $projectInvestment = ProjectInvestment::create([
            'user_id' => auth()->id(),
            ...$validated,
        ]);

        // Create initial transaction
        $projectInvestment->transactions()->create([
            'user_id' => auth()->id(),
            'amount' => $investmentAmount,
            'currency' => $currency,
            'transaction_date' => $validated['start_date'] ?? now()->toDateString(),
            'notes' => 'Initial investment',
        ]);

        return redirect()->route('project-investments.show', $projectInvestment)
            ->with('success', 'Project investment created successfully!');
    }

    /**
     * Display the specified project investment.
     */
    public function show(ProjectInvestment $projectInvestment)
    {
        $this->authorizeOwnership($projectInvestment);

        // Eager load transactions ordered by date
        $projectInvestment->load(['transactions' => function ($query) {
            $query->orderByDesc('transaction_date')->orderByDesc('created_at');
        }]);

        // Expose the primary currency and the converted total invested so the
        // financial summary is labelled with the investment's actual currency
        // and aggregates transactions across currencies correctly.
        $projectInvestment->append(['primary_currency', 'total_invested']);

        return Inertia::render('ProjectInvestments/Show', compact('projectInvestment'));
    }

    /**
     * Show the form for editing the specified project investment.
     */
    public function edit(ProjectInvestment $projectInvestment)
    {
        $this->authorizeOwnership($projectInvestment);

        return Inertia::render('ProjectInvestments/Edit', compact('projectInvestment'));
    }

    /**
     * Update the specified project investment.
     */
    public function update(UpdateProjectInvestmentRequest $request, ProjectInvestment $projectInvestment)
    {
        $this->authorizeOwnership($projectInvestment);

        $projectInvestment->update($request->validated());

        return redirect()->route('project-investments.show', $projectInvestment)
            ->with('success', 'Project investment updated successfully!');
    }

    /**
     * Remove the specified project investment.
     */
    public function destroy(ProjectInvestment $projectInvestment)
    {
        $this->authorizeOwnership($projectInvestment);

        $projectInvestment->delete();

        return redirect()->route('project-investments.index')
            ->with('success', 'Project investment deleted successfully!');
    }

    /**
     * Update the current value of a project investment.
     */
    public function updateValue(Request $request, ProjectInvestment $projectInvestment)
    {
        $this->authorizeOwnership($projectInvestment);

        $request->validate([
            'current_value' => 'required|numeric|min:0',
        ]);

        $projectInvestment->update([
            'current_value' => $request->current_value,
        ]);

        return redirect()->route('project-investments.show', $projectInvestment)
            ->with('success', 'Project value updated successfully!');
    }

    /**
     * Display analytics for project investments.
     */
    public function analytics()
    {
        $userId = auth()->id();

        // Get aggregate statistics
        $stats = ProjectInvestment::where('user_id', $userId)
            ->selectRaw('
                COUNT(*) as total_projects,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as active_projects,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as completed_projects,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as sold_projects,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as abandoned_projects
            ', ['active', 'completed', 'sold', 'abandoned'])
            ->first();

        // All monetary figures are converted into the tenant's default currency
        // so the portfolio can be compared in a single, consistent currency.
        $currency = $this->portfolioCurrency();

        // Get all projects with transactions for calculations
        $projects = ProjectInvestment::where('user_id', $userId)
            ->with('transactions')
            ->get();

        $totalInvested = $projects->sum(fn (ProjectInvestment $project) => $project->totalInvestedIn($currency));
        $totalCurrentValue = $projects->sum(fn (ProjectInvestment $project) => $project->currentValueIn($currency));
        $totalGainLoss = $totalCurrentValue - $totalInvested;

        $breakdownBy = function (string $attribute) use ($projects, $currency) {
            return $projects->filter(fn (ProjectInvestment $project) => $project->{$attribute} !== null)
                ->groupBy($attribute)
                ->map(fn ($group) => [
                    'count' => $group->count(),
                    'invested' => round($group->sum(fn (ProjectInvestment $project) => $project->totalInvestedIn($currency)), 2),
                    'current_value' => round($group->sum(fn (ProjectInvestment $project) => $project->currentValueIn($currency)), 2),
                ]);
        };

        $byStage = $breakdownBy('stage');
        $byBusinessModel = $breakdownBy('business_model');
        $byProjectType = $breakdownBy('project_type');

        // Per-project rows, converted into the portfolio currency and sorted by investment.
        $projectsList = $projects->map(fn (ProjectInvestment $project) => [
            'id' => $project->id,
            'name' => $project->name,
            'stage' => $project->stage,
            'status' => $project->status,
            'total_invested' => round($project->totalInvestedIn($currency), 2),
            'current_value' => $project->current_value !== null ? round($project->currentValueIn($currency), 2) : null,
        ])->sortByDesc('total_invested')->values();

        $analytics = [
            'total_projects' => (int) $stats->total_projects,
            'active_projects' => (int) $stats->active_projects,
            'completed_projects' => (int) $stats->completed_projects,
            'sold_projects' => (int) $stats->sold_projects,
            'abandoned_projects' => (int) $stats->abandoned_projects,
            'total_invested' => round($totalInvested, 2),
            'total_current_value' => round($totalCurrentValue, 2),
            'total_gain_loss' => round($totalGainLoss, 2),
            'by_stage' => $byStage,
            'by_business_model' => $byBusinessModel,
            'by_project_type' => $byProjectType,
            'projects' => $projectsList,
            'currency' => $currency,
        ];

        return Inertia::render('ProjectInvestments/Analytics', compact('analytics'));
    }
}
