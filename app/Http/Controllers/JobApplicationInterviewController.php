<?php

namespace App\Http\Controllers;

use App\Enums\InterviewOutcome;
use App\Enums\InterviewType;
use App\Http\Requests\StoreInterviewRequest;
use App\Models\JobApplication;
use App\Models\JobApplicationInterview;
use Illuminate\Http\Request;

class JobApplicationInterviewController extends Controller
{
    /**
     * Display a listing of the interviews for an application.
     */
    public function index(JobApplication $application)
    {
        // Ensure user owns the application
        if ($application->user_id !== auth()->id()) {
            abort(403);
        }

        $interviews = $application->interviews()
            ->orderBy('scheduled_at', 'desc')
            ->get();

        return view('job-applications.interviews.index', compact('application', 'interviews'));
    }

    /**
     * Show the form for creating a new interview.
     */
    public function create(JobApplication $application)
    {
        // Ensure user owns the application
        if ($application->user_id !== auth()->id()) {
            abort(403);
        }

        $types = InterviewType::cases();
        $outcomes = InterviewOutcome::cases();

        return view('job-applications.interviews.create', compact('application', 'types', 'outcomes'));
    }

    /**
     * Store a newly created interview.
     */
    public function store(StoreInterviewRequest $request, JobApplication $application)
    {
        // Ensure user owns the application
        if ($application->user_id !== auth()->id()) {
            abort(403);
        }

        $interview = $application->interviews()->create([
            'user_id' => auth()->id(),
            ...$request->validated(),
        ]);

        return redirect()->route('job-applications.show', $application)
            ->with('success', 'Interview scheduled successfully!');
    }

    /**
     * Display the specified interview.
     */
    public function show(JobApplication $application, JobApplicationInterview $interview)
    {
        // Ensure user owns the interview
        if ($interview->user_id !== auth()->id() || $interview->job_application_id !== $application->id) {
            abort(403);
        }

        return view('job-applications.interviews.show', compact('application', 'interview'));
    }

    /**
     * Show the form for editing the interview.
     */
    public function edit(JobApplication $application, JobApplicationInterview $interview)
    {
        // Ensure user owns the interview
        if ($interview->user_id !== auth()->id() || $interview->job_application_id !== $application->id) {
            abort(403);
        }

        $types = InterviewType::cases();
        $outcomes = InterviewOutcome::cases();

        return view('job-applications.interviews.edit', compact('application', 'interview', 'types', 'outcomes'));
    }

    /**
     * Update the specified interview.
     */
    public function update(Request $request, JobApplication $application, JobApplicationInterview $interview)
    {
        // Ensure user owns the interview
        if ($interview->user_id !== auth()->id() || $interview->job_application_id !== $application->id) {
            abort(403);
        }

        $interview->update($request->all());

        return redirect()->route('job-applications.show', $application)
            ->with('success', 'Interview updated successfully!');
    }

    /**
     * Remove the specified interview.
     */
    public function destroy(JobApplication $application, JobApplicationInterview $interview)
    {
        // Ensure user owns the interview
        if ($interview->user_id !== auth()->id() || $interview->job_application_id !== $application->id) {
            abort(403);
        }

        $interview->delete();

        return redirect()->route('job-applications.show', $application)
            ->with('success', 'Interview deleted successfully!');
    }

    /**
     * Mark interview as completed.
     */
    public function complete(JobApplication $application, JobApplicationInterview $interview)
    {
        // Ensure user owns the interview
        if ($interview->user_id !== auth()->id() || $interview->job_application_id !== $application->id) {
            abort(403);
        }

        $interview->update(['completed' => true]);

        return back()->with('success', 'Interview marked as completed!');
    }
}
