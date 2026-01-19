<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\DashboardController as MainDashboardController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected MainDashboardController $dashboardController;

    public function __construct(MainDashboardController $dashboardController)
    {
        $this->dashboardController = $dashboardController;
    }

    /**
     * Get chart data for the dashboard.
     * This delegates to the main DashboardController to avoid code duplication.
     */
    public function chartData(Request $request): JsonResponse
    {
        return $this->dashboardController->getChartData($request);
    }
}
