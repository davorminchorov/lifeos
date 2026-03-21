<?php

namespace App\Http\Controllers;

use App\Services\DashboardCacheService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(protected DashboardCacheService $dashboard) {}

    /**
     * Display the dashboard with aggregated data from all modules.
     */
    public function index()
    {
        $stats = $this->dashboard->getStats();
        $alerts = $this->dashboard->getAlerts();
        $insights = $this->dashboard->getInsights();
        $recent_expenses = $this->dashboard->getRecentExpenses();
        $upcoming_bills = $this->dashboard->getUpcomingBills();

        return view('dashboard', compact(
            'stats',
            'alerts',
            'insights',
            'recent_expenses',
            'upcoming_bills'
        ));
    }

    /**
     * Get chart data for Advanced Analytics Dashboard.
     */
    public function getChartData(Request $request)
    {
        $period = $request->get('period', '6months');

        return response()->json($this->dashboard->getChartData($period));
    }
}
