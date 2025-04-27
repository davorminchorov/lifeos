<?php

namespace App\Dashboard\UI\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class JobApplicationsSummaryController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $userId = Auth::id();

        // Get counts of applications by status
        $statusCounts = DB::table('job_applications')
            ->where('user_id', $userId)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status')
            ->toArray();

        // Get upcoming interviews
        $upcomingInterviews = DB::table('job_application_interviews as i')
            ->join('job_applications as a', 'i.application_id', '=', 'a.id')
            ->where('a.user_id', $userId)
            ->where('i.interview_date', '>=', now()->toDateString())
            ->orderBy('i.interview_date', 'asc')
            ->orderBy('i.interview_time', 'asc')
            ->limit(5)
            ->select([
                'i.id',
                'i.application_id',
                'i.interview_date',
                'i.interview_time',
                'i.interview_type',
                'i.with_person',
                'a.company_name',
                'a.position'
            ])
            ->get();

        // Get recent applications
        $recentApplications = DB::table('job_applications')
            ->where('user_id', $userId)
            ->orderBy('application_date', 'desc')
            ->limit(5)
            ->get();

        // Calculate application statistics
        $totalApplications = array_sum($statusCounts);
        $successRate = $totalApplications > 0 && isset($statusCounts['offered'])
            ? round(($statusCounts['offered'] / $totalApplications) * 100, 1)
            : 0;

        return response()->json([
            'total_applications' => $totalApplications,
            'active_applications' => ($statusCounts['applied'] ?? 0) + ($statusCounts['interviewing'] ?? 0),
            'upcoming_interviews' => $upcomingInterviews,
            'recent_applications' => $recentApplications,
            'status_distribution' => $statusCounts,
            'success_rate' => $successRate,
        ]);
    }
}
