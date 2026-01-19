<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectInvestmentRequest;
use App\Http\Requests\UpdateProjectInvestmentRequest;
use App\Models\ProjectInvestment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        // Calculate summary statistics from transactions
        $summary = DB::table('project_investments')
            ->leftJoin('project_investment_transactions', 'project_investments.id', '=', 'project_investment_transactions.project_investment_id')
            ->where('project_investments.user_id', auth()->id())
            ->selectRaw('
                COUNT(DISTINCT project_investments.id) as total_projects,
                SUM(CASE WHEN project_investments.status = ? THEN 1 ELSE 0 END) as active_projects,
                COALESCE(SUM(project_investment_transactions.amount), 0) as total_invested
            ', ['active'])
            ->first();

        $totalCurrentValue = ProjectInvestment::where('user_id', auth()->id())
            ->with('transactions')
            ->get()
            ->sum(function ($project) {
                return $project->current_value ?? $project->total_invested;
            });

        $totalGainLoss = $totalCurrentValue - $summary->total_invested;

        $summaryData = [
            'total_projects' => (int) $summary->total_projects,
            'active_projects' => (int) $summary->active_projects,
            'total_invested' => (float) $summary->total_invested,
            'total_current_value' => (float) $totalCurrentValue,
            'total_gain_loss' => (float) $totalGainLoss,
        ];

        return view('project-investments.index', ['projectInvestments' => $projectInvestments, 'summary' => $summaryData]);
    }

    /**
     * Show the form for creating a new project investment.
     */
    public function create()
    {
        return view('project-investments.create');
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

        return view('project-investments.show', compact('projectInvestment'));
    }

    /**
     * Show the form for editing the specified project investment.
     */
    public function edit(ProjectInvestment $projectInvestment)
    {
        $this->authorizeOwnership($projectInvestment);

        return view('project-investments.edit', compact('projectInvestment'));
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

        // Calculate total invested from transactions
        $totalInvested = DB::table('project_investment_transactions')
            ->join('project_investments', 'project_investment_transactions.project_investment_id', '=', 'project_investments.id')
            ->where('project_investments.user_id', $userId)
            ->sum('project_investment_transactions.amount');

        // Get all projects with transactions for calculations
        $projects = ProjectInvestment::where('user_id', $userId)
            ->with('transactions')
            ->get();

        $totalCurrentValue = $projects->sum(function ($project) {
            return $project->current_value ?? $project->total_invested;
        });

        $totalGainLoss = $totalCurrentValue - $totalInvested;

        // Get breakdown by stage
        $byStage = $projects->filter(fn ($p) => $p->stage !== null)
            ->groupBy('stage')
            ->map(function ($group) {
                return [
                    'count' => $group->count(),
                    'invested' => $group->sum('total_invested'),
                    'current_value' => $group->sum(fn ($p) => $p->current_value ?? $p->total_invested),
                ];
            });

        // Get breakdown by business model
        $byBusinessModel = $projects->filter(fn ($p) => $p->business_model !== null)
            ->groupBy('business_model')
            ->map(function ($group) {
                return [
                    'count' => $group->count(),
                    'invested' => $group->sum('total_invested'),
                    'current_value' => $group->sum(fn ($p) => $p->current_value ?? $p->total_invested),
                ];
            });

        // Get breakdown by project type
        $byProjectType = $projects->filter(fn ($p) => $p->project_type !== null)
            ->groupBy('project_type')
            ->map(function ($group) {
                return [
                    'count' => $group->count(),
                    'invested' => $group->sum('total_invested'),
                    'current_value' => $group->sum(fn ($p) => $p->current_value ?? $p->total_invested),
                ];
            });

        // Sort projects by total invested
        $projects = $projects->sortByDesc('total_invested')->values();

        $analytics = [
            'total_projects' => (int) $stats->total_projects,
            'active_projects' => (int) $stats->active_projects,
            'completed_projects' => (int) $stats->completed_projects,
            'sold_projects' => (int) $stats->sold_projects,
            'abandoned_projects' => (int) $stats->abandoned_projects,
            'total_invested' => (float) $totalInvested,
            'total_current_value' => (float) $totalCurrentValue,
            'total_gain_loss' => (float) $totalGainLoss,
            'by_stage' => $byStage,
            'by_business_model' => $byBusinessModel,
            'by_project_type' => $byProjectType,
            'projects' => $projects,
        ];

        return view('project-investments.analytics', compact('analytics'));
    }
}
