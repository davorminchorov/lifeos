<?php

namespace App\Http\Controllers;

use App\Enums\ApplicationStatus;
use App\Models\JobApplication;
use Illuminate\Http\Request;

class JobApplicationKanbanController extends Controller
{
    /**
     * Display the Kanban board view.
     */
    public function index(Request $request)
    {
        $query = JobApplication::query()
            ->where('user_id', auth()->id())
            ->active();

        // Apply filters
        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        $applications = $query->with(['interviews', 'offer'])->get();

        // Group applications by status
        $columns = [];
        foreach (ApplicationStatus::cases() as $status) {
            // Skip ACCEPTED, REJECTED, WITHDRAWN, ARCHIVED from kanban board
            if (in_array($status, [
                ApplicationStatus::ACCEPTED,
                ApplicationStatus::REJECTED,
                ApplicationStatus::WITHDRAWN,
                ApplicationStatus::ARCHIVED,
            ])) {
                continue;
            }

            $columns[$status->value] = [
                'status' => $status,
                'label' => $status->label(),
                'color' => $status->color(),
                'applications' => $applications->where('status', $status)->values(),
            ];
        }

        return view('job-applications.kanban', compact('columns', 'applications'));
    }

    /**
     * Update application status (for drag-and-drop).
     */
    public function updateStatus(Request $request, JobApplication $application)
    {
        // Ensure user owns the application
        if ($application->user_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'status' => ['required', 'string'],
        ]);

        $newStatus = ApplicationStatus::tryFrom($request->status);
        if (! $newStatus) {
            return response()->json(['error' => 'Invalid status'], 422);
        }

        $application->update(['status' => $newStatus]);

        return response()->json([
            'success' => true,
            'message' => 'Application moved successfully!',
        ]);
    }
}
