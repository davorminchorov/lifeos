<?php

namespace App\Expenses\UI\API;

use App\Expenses\Queries\GetMonthlySummary;
use App\Expenses\Queries\GetCategoryDistribution;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportsController
{
    public function __construct(
        private readonly GetMonthlySummary $getMonthlySummary,
        private readonly GetCategoryDistribution $getCategoryDistribution,
    ) {
    }

    public function monthlySummary(Request $request): JsonResponse
    {
        $months = (int) $request->query('months', 6);
        $data = $this->getMonthlySummary->handle($months);

        return response()->json(['data' => $data]);
    }

    public function categoryDistribution(Request $request): JsonResponse
    {
        $period = $request->query('period', 'all');
        $data = $this->getCategoryDistribution->handle($period);

        return response()->json(['data' => $data]);
    }
}
