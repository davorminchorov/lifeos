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
    private const ALLOWED_SORT_COLUMNS = ['start_date', 'name', 'investment_amount', 'current_value', 'created_at', 'status', 'stage'];

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

        $projectInvestments = $query->paginate($request->get('per_page', 15));

        // Calculate summary statistics using a single aggregated database query
        $summary = ProjectInvestment::where('user_id', auth()->id())
            ->selectRaw('
                COUNT(*) as total_projects,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as active_projects,
                COALESCE(SUM(investment_amount), 0) as total_invested,
                COALESCE(SUM(COALESCE(current_value, investment_amount)), 0) as total_current_value,
                COALESCE(SUM(COALESCE(current_value, investment_amount) - investment_amount), 0) as total_gain_loss
            ', ['active'])
            ->first();

        $summaryData = [
            'total_projects' => (int) $summary->total_projects,
            'active_projects' => (int) $summary->active_projects,
            'total_invested' => (float) $summary->total_invested,
            'total_current_value' => (float) $summary->total_current_value,
            'total_gain_loss' => (float) $summary->total_gain_loss,
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
        $projectInvestment = ProjectInvestment::create([
            'user_id' => auth()->id(),
            ...$request->validated(),
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

        // Get aggregate statistics using database queries
        $stats = ProjectInvestment::where('user_id', $userId)
            ->selectRaw('
                COUNT(*) as total_projects,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as active_projects,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as completed_projects,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as sold_projects,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as abandoned_projects,
                COALESCE(SUM(investment_amount), 0) as total_invested,
                COALESCE(SUM(COALESCE(current_value, investment_amount)), 0) as total_current_value,
                COALESCE(SUM(COALESCE(current_value, investment_amount) - investment_amount), 0) as total_gain_loss
            ', ['active', 'completed', 'sold', 'abandoned'])
            ->first();

        // Get breakdown by stage using database aggregation
        $byStage = ProjectInvestment::where('user_id', $userId)
            ->whereNotNull('stage')
            ->groupBy('stage')
            ->selectRaw('
                stage,
                COUNT(*) as count,
                COALESCE(SUM(investment_amount), 0) as invested,
                COALESCE(SUM(COALESCE(current_value, investment_amount)), 0) as current_value
            ')
            ->get()
            ->keyBy('stage')
            ->map(fn ($item) => [
                'count' => (int) $item->count,
                'invested' => (float) $item->invested,
                'current_value' => (float) $item->current_value,
            ]);

        // Get breakdown by business model using database aggregation
        $byBusinessModel = ProjectInvestment::where('user_id', $userId)
            ->whereNotNull('business_model')
            ->groupBy('business_model')
            ->selectRaw('
                business_model,
                COUNT(*) as count,
                COALESCE(SUM(investment_amount), 0) as invested,
                COALESCE(SUM(COALESCE(current_value, investment_amount)), 0) as current_value
            ')
            ->get()
            ->keyBy('business_model')
            ->map(fn ($item) => [
                'count' => (int) $item->count,
                'invested' => (float) $item->invested,
                'current_value' => (float) $item->current_value,
            ]);

        // Get breakdown by project type using database aggregation
        $byProjectType = ProjectInvestment::where('user_id', $userId)
            ->whereNotNull('project_type')
            ->groupBy('project_type')
            ->selectRaw('
                project_type,
                COUNT(*) as count,
                COALESCE(SUM(investment_amount), 0) as invested,
                COALESCE(SUM(COALESCE(current_value, investment_amount)), 0) as current_value
            ')
            ->get()
            ->keyBy('project_type')
            ->map(fn ($item) => [
                'count' => (int) $item->count,
                'invested' => (float) $item->invested,
                'current_value' => (float) $item->current_value,
            ]);

        // Get projects sorted by investment amount for the list
        $projects = ProjectInvestment::where('user_id', $userId)
            ->orderByDesc('investment_amount')
            ->get();

        $analytics = [
            'total_projects' => (int) $stats->total_projects,
            'active_projects' => (int) $stats->active_projects,
            'completed_projects' => (int) $stats->completed_projects,
            'sold_projects' => (int) $stats->sold_projects,
            'abandoned_projects' => (int) $stats->abandoned_projects,
            'total_invested' => (float) $stats->total_invested,
            'total_current_value' => (float) $stats->total_current_value,
            'total_gain_loss' => (float) $stats->total_gain_loss,
            'by_stage' => $byStage,
            'by_business_model' => $byBusinessModel,
            'by_project_type' => $byProjectType,
            'projects' => $projects,
        ];

        return view('project-investments.analytics', compact('analytics'));
    }
}
