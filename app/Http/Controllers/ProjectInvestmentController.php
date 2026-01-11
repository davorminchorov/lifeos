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

        // Calculate summary statistics using database queries for efficiency
        $userId = auth()->id();
        $summary = [
            'total_projects' => ProjectInvestment::where('user_id', $userId)->count(),
            'active_projects' => ProjectInvestment::where('user_id', $userId)->where('status', 'active')->count(),
            'total_invested' => (float) ProjectInvestment::where('user_id', $userId)->sum('investment_amount'),
            'total_current_value' => (float) (ProjectInvestment::where('user_id', $userId)->sum('current_value')
                ?: ProjectInvestment::where('user_id', $userId)->sum('investment_amount')),
            'total_gain_loss' => (float) ProjectInvestment::where('user_id', $userId)
                ->select(DB::raw('SUM(COALESCE(current_value, investment_amount) - investment_amount) as gain_loss'))
                ->value('gain_loss'),
        ];

        return view('project-investments.index', compact('projectInvestments', 'summary'));
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
        // Ensure user owns this project
        if ($projectInvestment->user_id !== auth()->id()) {
            abort(403);
        }

        return view('project-investments.show', compact('projectInvestment'));
    }

    /**
     * Show the form for editing the specified project investment.
     */
    public function edit(ProjectInvestment $projectInvestment)
    {
        // Ensure user owns this project
        if ($projectInvestment->user_id !== auth()->id()) {
            abort(403);
        }

        return view('project-investments.edit', compact('projectInvestment'));
    }

    /**
     * Update the specified project investment.
     */
    public function update(UpdateProjectInvestmentRequest $request, ProjectInvestment $projectInvestment)
    {
        // Ensure user owns this project
        if ($projectInvestment->user_id !== auth()->id()) {
            abort(403);
        }

        $projectInvestment->update($request->validated());

        return redirect()->route('project-investments.show', $projectInvestment)
            ->with('success', 'Project investment updated successfully!');
    }

    /**
     * Remove the specified project investment.
     */
    public function destroy(ProjectInvestment $projectInvestment)
    {
        // Ensure user owns this project
        if ($projectInvestment->user_id !== auth()->id()) {
            abort(403);
        }

        $projectInvestment->delete();

        return redirect()->route('project-investments.index')
            ->with('success', 'Project investment deleted successfully!');
    }

    /**
     * Update the current value of a project investment.
     */
    public function updateValue(Request $request, ProjectInvestment $projectInvestment)
    {
        // Ensure user owns this project
        if ($projectInvestment->user_id !== auth()->id()) {
            abort(403);
        }

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
        $projects = ProjectInvestment::where('user_id', $userId)->get();

        $analytics = [
            'total_projects' => $projects->count(),
            'active_projects' => $projects->where('status', 'active')->count(),
            'completed_projects' => $projects->where('status', 'completed')->count(),
            'sold_projects' => $projects->where('status', 'sold')->count(),
            'abandoned_projects' => $projects->where('status', 'abandoned')->count(),
            'total_invested' => $projects->sum('investment_amount'),
            'total_current_value' => $projects->sum('current_value') ?: $projects->sum('investment_amount'),
            'total_gain_loss' => $projects->sum(function ($project) {
                return $project->gain_loss;
            }),
            'by_stage' => $projects->groupBy('stage')->map(function ($group) {
                return [
                    'count' => $group->count(),
                    'invested' => $group->sum('investment_amount'),
                    'current_value' => $group->sum('current_value') ?: $group->sum('investment_amount'),
                ];
            }),
            'by_business_model' => $projects->groupBy('business_model')->map(function ($group) {
                return [
                    'count' => $group->count(),
                    'invested' => $group->sum('investment_amount'),
                    'current_value' => $group->sum('current_value') ?: $group->sum('investment_amount'),
                ];
            }),
            'by_project_type' => $projects->groupBy('project_type')->map(function ($group) {
                return [
                    'count' => $group->count(),
                    'invested' => $group->sum('investment_amount'),
                    'current_value' => $group->sum('current_value') ?: $group->sum('investment_amount'),
                ];
            }),
            'projects' => $projects->sortByDesc('investment_amount')->values(),
        ];

        return view('project-investments.analytics', compact('analytics'));
    }
}
