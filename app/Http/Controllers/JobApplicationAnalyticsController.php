<?php

namespace App\Http\Controllers;

use App\Enums\ApplicationSource;
use App\Enums\ApplicationStatus;
use App\Models\JobApplication;
use Illuminate\Http\Request;

class JobApplicationAnalyticsController extends Controller
{
    /**
     * Display the analytics dashboard.
     */
    public function index(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->subMonths(6)->toDateString());
        $dateTo = $request->get('date_to', now()->toDateString());

        $applications = JobApplication::query()
            ->where('user_id', auth()->id())
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->with(['statusHistories', 'interviews', 'offer'])
            ->get();

        // Application funnel - count by status
        $funnel = [];
        foreach (ApplicationStatus::cases() as $status) {
            $funnel[$status->value] = [
                'status' => $status,
                'label' => $status->label(),
                'color' => $status->color(),
                'count' => $applications->where('status', $status)->count(),
            ];
        }

        // Source effectiveness
        $sourceStats = [];
        foreach (ApplicationSource::cases() as $source) {
            $sourceApplications = $applications->where('source', $source);
            $totalCount = $sourceApplications->count();
            $interviewCount = $sourceApplications->filter(function ($app) {
                return $app->interviews->count() > 0;
            })->count();
            $offerCount = $sourceApplications->filter(function ($app) {
                return $app->offer !== null;
            })->count();

            $sourceStats[$source->value] = [
                'source' => $source,
                'label' => ucwords(str_replace('_', ' ', $source->value)),
                'applications' => $totalCount,
                'interviews' => $interviewCount,
                'offers' => $offerCount,
                'interview_rate' => $totalCount > 0 ? round(($interviewCount / $totalCount) * 100, 1) : 0,
                'offer_rate' => $totalCount > 0 ? round(($offerCount / $totalCount) * 100, 1) : 0,
            ];
        }

        // Time-in-stage metrics
        $stageMetrics = [];
        foreach (ApplicationStatus::cases() as $status) {
            $statusApplications = $applications->where('status', $status);
            $avgDays = 0;

            if ($statusApplications->count() > 0) {
                $totalDays = $statusApplications->sum(function ($app) {
                    return $app->days_in_current_status;
                });
                $avgDays = round($totalDays / $statusApplications->count(), 1);
            }

            $stageMetrics[$status->value] = [
                'status' => $status,
                'label' => $status->label(),
                'avg_days' => $avgDays,
            ];
        }

        // Quick stats
        $stats = [
            'total_applications' => $applications->count(),
            'active_applications' => $applications->whereNull('archived_at')->count(),
            'total_interviews' => $applications->sum(fn ($app) => $app->interviews->count()),
            'upcoming_interviews' => $applications->sum(function ($app) {
                return $app->interviews->where('scheduled_at', '>=', now())->where('completed', false)->count();
            }),
            'offers_received' => $applications->filter(fn ($app) => $app->offer !== null)->count(),
            'offers_pending' => $applications->filter(function ($app) {
                return $app->offer && $app->offer->status->value === 'pending';
            })->count(),
            'offers_accepted' => $applications->where('status', ApplicationStatus::ACCEPTED)->count(),
            'rejected' => $applications->where('status', ApplicationStatus::REJECTED)->count(),
            'withdrawn' => $applications->where('status', ApplicationStatus::WITHDRAWN)->count(),
        ];

        // Calculate conversion rates
        $stats['interview_rate'] = $stats['total_applications'] > 0
            ? round(($applications->filter(fn ($app) => $app->interviews->count() > 0)->count() / $stats['total_applications']) * 100, 1)
            : 0;
        $stats['offer_rate'] = $stats['total_applications'] > 0
            ? round(($stats['offers_received'] / $stats['total_applications']) * 100, 1)
            : 0;
        $stats['acceptance_rate'] = $stats['offers_received'] > 0
            ? round(($stats['offers_accepted'] / $stats['offers_received']) * 100, 1)
            : 0;

        // Recent activity (last 10 status changes)
        $recentActivity = JobApplication::query()
            ->where('user_id', auth()->id())
            ->with(['statusHistories' => fn ($query) => $query->orderBy('changed_at', 'desc')->limit(10)])
            ->get()
            ->flatMap(fn ($app) => $app->statusHistories->map(function ($history) use ($app) {
                return [
                    'application' => $app,
                    'history' => $history,
                ];
            }))
            ->sortByDesc('history.changed_at')
            ->take(10);

        // Average time to offer
        $applicationsWithOffers = $applications->filter(fn ($app) => $app->offer !== null && $app->applied_at);
        $avgTimeToOffer = 0;
        if ($applicationsWithOffers->count() > 0) {
            $totalDays = $applicationsWithOffers->sum(function ($app) {
                return $app->applied_at->diffInDays($app->offer->created_at);
            });
            $avgTimeToOffer = round($totalDays / $applicationsWithOffers->count(), 1);
        }
        $stats['avg_time_to_offer'] = $avgTimeToOffer;

        return view('job-applications.analytics', compact(
            'funnel',
            'sourceStats',
            'stageMetrics',
            'stats',
            'recentActivity',
            'dateFrom',
            'dateTo'
        ));
    }

    /**
     * Export analytics data to CSV.
     */
    public function export(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->subMonths(6)->toDateString());
        $dateTo = $request->get('date_to', now()->toDateString());

        $applications = JobApplication::query()
            ->where('user_id', auth()->id())
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->with(['statusHistories', 'interviews', 'offer'])
            ->get();

        $filename = 'job-applications-analytics-'.now()->format('Y-m-d').'.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($applications) {
            $file = fopen('php://output', 'w');

            // Header row
            fputcsv($file, [
                'Company',
                'Job Title',
                'Status',
                'Source',
                'Applied Date',
                'Priority',
                'Days in Status',
                'Interviews',
                'Has Offer',
                'Location',
                'Remote',
            ]);

            // Data rows
            foreach ($applications as $app) {
                fputcsv($file, [
                    $app->company_name,
                    $app->job_title,
                    $app->status->label(),
                    ucwords(str_replace('_', ' ', $app->source->value)),
                    $app->applied_at?->format('Y-m-d') ?? 'N/A',
                    $app->priority,
                    $app->days_in_current_status,
                    $app->interviews->count(),
                    $app->offer ? 'Yes' : 'No',
                    $app->location ?? 'N/A',
                    $app->remote ? 'Yes' : 'No',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
