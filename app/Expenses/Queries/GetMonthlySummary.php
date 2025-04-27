<?php

namespace App\Expenses\Queries;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class GetMonthlySummary
{
    public function handle(int $numberOfMonths = 6): array
    {
        $startDate = Carbon::now()->subMonths($numberOfMonths - 1)->startOfMonth();
        $months = [];

        // Generate an array of the last $numberOfMonths months
        for ($i = 0; $i < $numberOfMonths; $i++) {
            $month = $startDate->copy()->addMonths($i);
            $months[] = $month->format('Y-m');
        }

        // Get monthly totals
        $monthlySummary = DB::table('monthly_expenses')
            ->whereIn('month', $months)
            ->get()
            ->keyBy('month')
            ->toArray();

        // Format data for the frontend
        $result = [];
        foreach ($months as $month) {
            $monthData = $monthlySummary[$month] ?? null;
            $result[] = [
                'month' => $month,
                'month_label' => Carbon::createFromFormat('Y-m', $month)->format('M Y'),
                'total_amount' => $monthData->total_amount ?? 0,
                'expense_count' => $monthData->expense_count ?? 0,
            ];
        }

        return $result;
    }
}
