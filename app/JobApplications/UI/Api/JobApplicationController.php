<?php

namespace App\JobApplications\UI\Api;

use App\Http\Controllers\Controller;
use App\JobApplications\Commands\SubmitApplicationCommand;
use App\JobApplications\Commands\UpdateApplicationCommand;
use App\JobApplications\Commands\ScheduleInterviewCommand;
use App\JobApplications\Commands\RecordOutcomeCommand;
use App\JobApplications\Queries\GetJobApplicationsQuery;
use App\JobApplications\Queries\GetJobApplicationByIdQuery;
use App\JobApplications\Queries\GetActiveApplicationsQuery;
use App\JobApplications\Queries\GetInterviewScheduleQuery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JobApplicationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $applications = app(GetJobApplicationsQuery::class)->execute([
            'user_id' => Auth::id(),
            'filters' => $request->query(),
        ]);

        return response()->json($applications);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'company_name' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'application_date' => 'required|date',
            'job_description' => 'nullable|string',
            'application_url' => 'nullable|url',
            'salary_range' => 'nullable|string',
            'contact_person' => 'nullable|string',
            'contact_email' => 'nullable|email',
            'status' => 'required|string|in:applied,interviewing,offered,rejected,withdrawn',
            'notes' => 'nullable|string',
        ]);

        $command = new SubmitApplicationCommand(
            Auth::id(),
            $request->company_name,
            $request->position,
            $request->application_date,
            $request->job_description,
            $request->application_url,
            $request->salary_range,
            $request->contact_person,
            $request->contact_email,
            $request->status,
            $request->notes
        );

        $applicationId = $this->dispatch($command);

        return response()->json([
            'id' => $applicationId,
            'message' => 'Job application submitted successfully'
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        $application = app(GetJobApplicationByIdQuery::class)->execute([
            'id' => $id,
            'user_id' => Auth::id(),
        ]);

        if (!$application) {
            return response()->json(['message' => 'Job application not found'], 404);
        }

        return response()->json($application);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'company_name' => 'sometimes|required|string|max:255',
            'position' => 'sometimes|required|string|max:255',
            'application_date' => 'sometimes|required|date',
            'job_description' => 'nullable|string',
            'application_url' => 'nullable|url',
            'salary_range' => 'nullable|string',
            'contact_person' => 'nullable|string',
            'contact_email' => 'nullable|email',
            'status' => 'sometimes|required|string|in:applied,interviewing,offered,rejected,withdrawn',
            'notes' => 'nullable|string',
        ]);

        $command = new UpdateApplicationCommand(
            $id,
            Auth::id(),
            $request->all()
        );

        $this->dispatch($command);

        return response()->json([
            'message' => 'Job application updated successfully'
        ]);
    }

    public function scheduleInterview(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'interview_date' => 'required|date',
            'interview_time' => 'required|string',
            'interview_type' => 'required|string|in:phone,video,in-person',
            'with_person' => 'required|string',
            'location' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $command = new ScheduleInterviewCommand(
            $id,
            Auth::id(),
            $request->interview_date,
            $request->interview_time,
            $request->interview_type,
            $request->with_person,
            $request->location,
            $request->notes
        );

        $this->dispatch($command);

        return response()->json([
            'message' => 'Interview scheduled successfully'
        ]);
    }

    public function recordOutcome(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'outcome' => 'required|string|in:offered,rejected,withdrawn',
            'outcome_date' => 'required|date',
            'salary_offered' => 'nullable|string',
            'feedback' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $command = new RecordOutcomeCommand(
            $id,
            Auth::id(),
            $request->outcome,
            $request->outcome_date,
            $request->salary_offered,
            $request->feedback,
            $request->notes
        );

        $this->dispatch($command);

        return response()->json([
            'message' => 'Application outcome recorded successfully'
        ]);
    }

    public function activeApplications(): JsonResponse
    {
        $applications = app(GetActiveApplicationsQuery::class)->execute([
            'user_id' => Auth::id(),
        ]);

        return response()->json($applications);
    }

    public function interviewSchedule(Request $request): JsonResponse
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $interviews = app(GetInterviewScheduleQuery::class)->execute([
            'user_id' => Auth::id(),
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);

        return response()->json($interviews);
    }
}
