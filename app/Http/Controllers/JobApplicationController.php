<?php

namespace App\Http\Controllers;

use App\Enums\ApplicationSource;
use App\Enums\ApplicationStatus;
use App\Http\Requests\StoreJobApplicationRequest;
use App\Http\Requests\UpdateJobApplicationRequest;
use App\Models\JobApplication;
use Illuminate\Http\Request;

class JobApplicationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = JobApplication::query()
            ->where('user_id', auth()->id())
            ->with(['statusHistories', 'interviews', 'offer']);

        // Filter by status
        if ($request->filled('status')) {
            $status = ApplicationStatus::tryFrom($request->status);
            if ($status) {
                $query->byStatus($status);
            }
        }

        // Filter by source
        if ($request->filled('source')) {
            $source = ApplicationSource::tryFrom($request->source);
            if ($source) {
                $query->bySource($source);
            }
        }

        // Filter by priority
        if ($request->filled('priority')) {
            $query->byPriority((int) $request->priority);
        }

        // Filter by remote
        if ($request->filled('remote')) {
            $query->remote();
        }

        // Filter by archived
        if ($request->filled('archived') && $request->archived === 'true') {
            $query->archived();
        } else {
            $query->active();
        }

        // Search by company name or job title
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->where('applied_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('applied_at', '<=', $request->date_to);
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $applications = $query->paginate($request->get('per_page', 15));

        return view('job-applications.index', compact('applications'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $statuses = ApplicationStatus::cases();
        $sources = ApplicationSource::cases();
        $currencies = ['MKD', 'USD', 'EUR', 'GBP', 'CAD', 'AUD', 'JPY', 'CHF', 'RSD', 'BGN'];

        return view('job-applications.create', compact('statuses', 'sources', 'currencies'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreJobApplicationRequest $request)
    {
        $application = JobApplication::create([
            'user_id' => auth()->id(),
            ...$request->validated(),
        ]);

        return redirect()->route('job-applications.show', $application)
            ->with('success', 'Job application created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(JobApplication $job_application)
    {
        // Ensure user owns the application
        if ($job_application->user_id !== auth()->id()) {
            abort(403);
        }

        $job_application->load([
            'statusHistories' => fn ($query) => $query->orderBy('changed_at', 'desc'),
            'interviews' => fn ($query) => $query->orderBy('scheduled_at', 'desc'),
            'offer',
        ]);

        $application = $job_application;

        return view('job-applications.show', compact('application'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(JobApplication $job_application)
    {
        // Ensure user owns the application
        if ($job_application->user_id !== auth()->id()) {
            abort(403);
        }

        $statuses = ApplicationStatus::cases();
        $sources = ApplicationSource::cases();
        $currencies = ['MKD', 'USD', 'EUR', 'GBP', 'CAD', 'AUD', 'JPY', 'CHF', 'RSD', 'BGN'];
        $application = $job_application;

        return view('job-applications.edit', compact('application', 'statuses', 'sources', 'currencies'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateJobApplicationRequest $request, JobApplication $job_application)
    {
        // Ensure user owns the application
        if ($job_application->user_id !== auth()->id()) {
            abort(403);
        }

        $job_application->update($request->validated());

        return redirect()->route('job-applications.show', $job_application)
            ->with('success', 'Job application updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(JobApplication $job_application)
    {
        // Ensure user owns the application
        if ($job_application->user_id !== auth()->id()) {
            abort(403);
        }

        $job_application->delete();

        return redirect()->route('job-applications.index')
            ->with('success', 'Job application deleted successfully!');
    }

    /**
     * Archive the application.
     */
    public function archive(JobApplication $application)
    {
        // Ensure user owns the application
        if ($application->user_id !== auth()->id()) {
            abort(403);
        }

        $application->update(['archived_at' => now()]);

        return back()->with('success', 'Job application archived successfully!');
    }

    /**
     * Unarchive the application.
     */
    public function unarchive(JobApplication $application)
    {
        // Ensure user owns the application
        if ($application->user_id !== auth()->id()) {
            abort(403);
        }

        $application->update(['archived_at' => null]);

        return back()->with('success', 'Job application unarchived successfully!');
    }
}
